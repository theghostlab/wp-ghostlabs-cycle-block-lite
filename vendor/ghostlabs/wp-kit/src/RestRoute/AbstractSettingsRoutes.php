<?php

namespace GhostLabs\WPKit\RestRoute;

use Exception;
use GhostLabs\WPKit\Contracts\PluginIdentityInterface;
use GhostLabs\WPKit\Contracts\RestRouteInterface;
use GhostLabs\WPKit\Service\DTOMapper;
use GhostLabs\WPKit\Service\SaveSettings;
use GhostLabs\WPKit\Service\Utils;
use RuntimeException;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Response;

abstract class AbstractSettingsRoutes implements RestRouteInterface
{
    private Utils $utils;

    /**
     * Typed against the narrow identity contract, not the licence layer's
     * `ConfigInterface`. This class calls exactly one config member —
     * `getRestNamespace()` — so widening the hint would drag `wp-license` into
     * every free-tier settings screen that needs a save endpoint, which is the
     * coupling the package split exists to remove. A `ConfigInterface` instance
     * still satisfies this parameter by inheritance, so paid consumers change
     * nothing.
     */
    public function __construct(
        protected readonly PluginIdentityInterface $config,
    ) {
        $this->utils = new Utils();
    }

    /**
     * Maps payload 'type' values to [DtoClass, ValidationRules].
     *
     * @return array<string, array{0: class-string, 1: array<string, array<string, mixed>>}>
     */
    abstract protected function dtoMap(): array;

    /**
     * Maps DTO property keys to save handler classes.
     *
     * @return array<string, class-string>
     */
    abstract protected function handlerMap(): array;

    public function routes(): void
    {
        register_rest_route($this->config->getRestNamespace(), '/save-settings', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'saveSettings'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
                'args' => $this->routeArgs(),
            ],
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function routeArgs(): array
    {
        return [
            'type' => [
                'required' => true,
                'type' => 'string',
                'enum' => array_keys($this->dtoMap()),
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ];
    }

    /**
     * @param WP_REST_Request<array<string, mixed>> $request
     * @return WP_Error|WP_REST_Response|WP_HTTP_Response
     */
    public function saveSettings(WP_REST_Request $request): WP_Error|WP_REST_Response|WP_HTTP_Response
    {
        /** @var array<string, mixed> $data */
        $data = $request->get_json_params();

        /** @var array<string, mixed> $payload */
        $payload = $this->utils->setPayload($data);

        $dtoMap = $this->dtoMap();

        /** @var string $type */
        $type = $payload['type'] ?? '';
        $dtoConfig = $dtoMap[$type] ?? $dtoMap[array_key_last($dtoMap)];

        try {
            $dto = DTOMapper::map($payload, $dtoConfig[0]);

            $errors = DTOMapper::validate($dto, $dtoConfig[1]);

            if (!empty($errors)) {
                $errors = json_encode($errors) === false ? 'There was an error validating the data.' : json_encode($errors);

                throw new RuntimeException($errors, 412);
            }

            $saveSettings = new SaveSettings($this->handlerMap());
            $results = $saveSettings->setPayload((array)$dto)->run();

            $errors = [];
            $success = [];

            /** @var array<array{
             *     success: null|bool,
             *     error: null|array<string>,
             *     code: ?int
             * }> $result */
            foreach ($results as $result) {
                $stack = $result;
                $stack = array_shift($stack);

                if (isset($stack['error'])) {
                    $errors = array_merge($errors, $result);
                } else {
                    $success = array_merge($success, $result);
                }
            }

            if (!empty($errors)) {
                $code = array_shift($errors)['code'] ?? 412;
                $errors = json_encode($errors) === false ? 'There was an error validating the data.' : json_encode($errors);
                throw new RuntimeException($errors, $code);
            }

            return rest_ensure_response([
                'status' => 200,
                'errors' => $errors,
                'success' => $success,
            ]);
        } catch (RuntimeException|Exception $e) {
            return rest_ensure_response(new WP_Error(
                $e->getCode(),
                $e->getMessage(),
                ['status' => $e->getCode(), 'errors' => json_decode($e->getMessage())]
            ));
        }
    }
}
