<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services\RestRoutes;

use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;
use GHOSTLABS\CYCLE_BLOCK\DTO\Input\ClaimRequestDto;
use GHOSTLABS\CYCLE_BLOCK\DTO\Input\UpgradeRequestDto;
use GHOSTLABS\CYCLE_BLOCK\DTO\Validation\Input\ClaimRequestValidation;
use GHOSTLABS\CYCLE_BLOCK\DTO\Validation\Input\UpgradeRequestValidation;
use GHOSTLABS\CYCLE_BLOCK\Services\Upgrade\ClaimExchanger;
use GHOSTLABS\CYCLE_BLOCK\Services\Upgrade\UpgradeOrchestrator;
use GHOSTLABS\CYCLE_BLOCK\Services\Upgrade\UpgradeSupport;
use GHOSTLABS\CYCLE_BLOCK\Services\Utils;
use GhostLabs\WPKit\Contracts\RestRouteInterface;
use GhostLabs\WPKit\Service\DTOMapper;
use WP_Error;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The one-shot upgrade endpoint, `POST {free-ns}/upgrade`.
 *
 * It implements wp-kit's RestRouteInterface, not the local one, exactly like
 * Pro's licence routes: Kernel's discovery collects the LOCAL interface and
 * constructs matches with no arguments, and this class both takes a
 * collaborator and must not depend on the discovery walk. FreeBoot registers
 * it with a direct add_action instead.
 *
 * The route lives under the FREE namespace and vanishes when the swap
 * succeeds — its whole job is to remove the edition serving it. The client
 * treats a mid-flight 404 as "already swapped".
 *
 * Failures are DATA (UpgradeResult), answered 200: the settings app branches
 * on noticeCode. HTTP errors are reserved for the request being malformed
 * (400) or the caller lacking capability (core's 403).
 */
class UpgradeRoutes implements RestRouteInterface
{
    private Utils $utils;

    private UpgradeOrchestrator $orchestrator;

    private ClaimExchanger $claims;

    public function __construct(?UpgradeOrchestrator $orchestrator = null, ?ClaimExchanger $claims = null)
    {
        $this->utils        = new Utils();
        $this->orchestrator = $orchestrator ?? new UpgradeOrchestrator();
        $this->claims       = $claims ?? new ClaimExchanger();
    }

    public function routes(): void
    {
        /**
         * The strictest gate in the plugin, on both routes: these endpoints
         * install and activate code, so they demand the capabilities core
         * itself demands for those acts, on top of the settings screen's own.
         *
         * ⚠️ **It answers a WP_Error, never a bare `false`.** Core renders
         * `false` as a generic 403 with nothing in it, and on multisite that was
         * the only thing a subsite Administrator could reach: core denies them
         * `install_plugins`, so the orchestrator's explanation — the one place
         * this plugin said anything about networks — ran on the far side of a
         * gate they never passed. They read the offer, pasted a key, pressed the
         * button, and the screen went quiet.
         *
         * The sentence comes from `UpgradeSupport`, which is also what the
         * settings screen renders, so the API and the UI cannot disagree about
         * why this site is being refused.
         */
        $permission = static function () {
            if (UpgradeSupport::canInstall()) {
                return true;
            }

            return new WP_Error('rest_forbidden', UpgradeSupport::message(), ['status' => 403]);
        };

        register_rest_route(PluginConfig::restNameSpace(), '/upgrade', [
            [
                'methods'  => 'POST',
                'callback' => [$this, 'upgrade'],
                'permission_callback' => $permission,
            ],
        ]);

        register_rest_route(PluginConfig::restNameSpace(), '/claim-upgrade', [
            [
                'methods'  => 'POST',
                'callback' => [$this, 'claimUpgrade'],
                'permission_callback' => $permission,
            ],
        ]);
    }

    /**
     * @return \WP_REST_Response|WP_Error
     */
    public function upgrade(WP_REST_Request $request)
    {
        $payload = $this->utils->setPayload($request->get_json_params());

        /** @var UpgradeRequestDto $dto */
        $dto = DTOMapper::map($payload, UpgradeRequestDto::class);

        $errors = DTOMapper::validate($dto, UpgradeRequestValidation::rules());

        if (!empty($errors)) {
            return $this->validationError($errors);
        }

        return rest_ensure_response($this->orchestrator->run((string) $dto->licenseKey)->toArray());
    }

    /**
     * The checkout-return path: redeem the claim server-side, then continue as
     * the manual path — the exchanged key goes through the same orchestrator,
     * so every guarantee pinned on /upgrade holds here by construction.
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function claimUpgrade(WP_REST_Request $request)
    {
        $payload = $this->utils->setPayload($request->get_json_params());

        /** @var ClaimRequestDto $dto */
        $dto = DTOMapper::map($payload, ClaimRequestDto::class);

        $errors = DTOMapper::validate($dto, ClaimRequestValidation::rules());

        if (!empty($errors)) {
            return $this->validationError($errors);
        }

        $claim = $this->claims->exchange((string) $dto->claimToken);

        if (!$claim['ok'] || $claim['licenseKey'] === null) {
            return rest_ensure_response([
                'ok'            => false,
                'stage'         => 'claim',
                'noticeCode'    => $claim['noticeCode'],
                'message'       => $claim['message'],
                'activationUrl' => null,
            ]);
        }

        return rest_ensure_response($this->orchestrator->run($claim['licenseKey'])->toArray());
    }

    /**
     * @param array<string, mixed> $errors
     */
    private function validationError(array $errors): WP_Error
    {
        return new WP_Error(
            'validation_error',
            json_encode(['errors' => $errors]) ?: 'validation failed',
            ['status' => 400]
        );
    }
}
