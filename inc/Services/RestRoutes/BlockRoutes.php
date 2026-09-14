<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services\RestRoutes;

use GhostLabs\WPKit\Contracts\DTOValidationInterface;
use GhostLabs\WPKit\Service\DTOMapper;
use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;
use GHOSTLABS\CYCLE_BLOCK\DTO\Input\BlockRequestDto;
use GHOSTLABS\CYCLE_BLOCK\DTO\Input\EntryChangeDto;
use GHOSTLABS\CYCLE_BLOCK\DTO\Validation\Input\ClearTestPreviewValidation;
use GHOSTLABS\CYCLE_BLOCK\DTO\Validation\Input\EntryChangeValidation;
use GHOSTLABS\CYCLE_BLOCK\DTO\Validation\Input\UpdateBlockValidation;
use GHOSTLABS\CYCLE_BLOCK\Services\Cycle;
use GHOSTLABS\CYCLE_BLOCK\Services\DBService;
use GHOSTLABS\CYCLE_BLOCK\Services\DBTables;
use GHOSTLABS\CYCLE_BLOCK\Services\EntryHistory;
use GHOSTLABS\CYCLE_BLOCK\Services\Utils;
use GHOSTLABS\CYCLE_BLOCK\Services\Variations;
use WP_Error;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) exit;

class BlockRoutes implements RestRouteInterface
{
    private Utils $utils;

    public function __construct()
    {
        $this->utils = new Utils();
    }

    public function routes()
    {
        $permission = static function () {
            return current_user_can('edit_posts');
        };

        register_rest_route(PluginConfig::restNameSpace(), '/block/update', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'updateBlock'],
                'permission_callback' => $permission,
            ],
        ]);

        register_rest_route(PluginConfig::restNameSpace(), '/block/clear-test-preview', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'clearTestPreview'],
                'permission_callback' => $permission,
            ],
        ]);

        register_rest_route(PluginConfig::restNameSpace(), '/block/entry-change', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'entryChange'],
                'permission_callback' => $permission,
            ],
        ]);
    }

    public function updateBlock(WP_REST_Request $request)
    {
        $dto = $this->validated(BlockRequestDto::class, new UpdateBlockValidation(), $request);

        if ($dto instanceof WP_Error) {
            return $dto;
        }

        if ($denied = $this->authorizePost((int) $dto->postId)) {
            return $denied;
        }

        $fetchLastFrequency = (new DBService())->getTableByKeys(DBTables::frequencyTable(), [
            'blockId' => $dto->blockId,
            'postId'  => $dto->postId,
        ], 'LIMIT 1');

        $check = $fetchLastFrequency[0]['block_id'] ?? null;

        if (!$check) {
            EntryHistory::setCache((int) $dto->postId, (string) $dto->blockId, (string) $dto->entryId);
        }

        return rest_ensure_response(['status' => true]);
    }

    public function clearTestPreview(WP_REST_Request $request)
    {
        $dto = $this->validated(BlockRequestDto::class, new ClearTestPreviewValidation(), $request);

        if ($dto instanceof WP_Error) {
            return $dto;
        }

        if ($denied = $this->authorizePost((int) $dto->postId)) {
            return $denied;
        }

        $transient = PluginConfig::testPreviewTransient((int) $dto->postId, (string) $dto->blockId);

        return rest_ensure_response(['status' => delete_transient($transient)]);
    }

    public function entryChange(WP_REST_Request $request)
    {
        $dto = $this->validated(EntryChangeDto::class, new EntryChangeValidation(), $request);

        if ($dto instanceof WP_Error) {
            return $dto;
        }

        if ($denied = $this->authorizePost((int) $dto->postId)) {
            return $denied;
        }

        Variations::setStartingPosition((int) $dto->postId, (string) $dto->blockId, (string) $dto->startingPosition);

        if (strcmp((string) $dto->startingPosition, (string) $dto->currentId) !== 0) {
            Cycle::reset((int) $dto->postId, (string) $dto->blockId);

            return rest_ensure_response(['status' => $dto->currentId]);
        }

        return rest_ensure_response(['status' => null]);
    }

    private function authorizePost(int $postId): ?WP_Error
    {
        if (current_user_can('edit_post', $postId)) {
            return null;
        }

        return new WP_Error(
            'rest_forbidden',
            __('You are not allowed to edit this post.', 'ghostlabs-cycle-block-lite'),
            ['status' => 403]
        );
    }

    private function validated(string $dtoClass, DTOValidationInterface $validation, WP_REST_Request $request)
    {
        $payload = $this->utils->setPayload($request->get_json_params());

        $dto = DTOMapper::map($payload, $dtoClass);

        $errors = DTOMapper::validate($dto, $validation::rules());

        if (!empty($errors)) {
            return new WP_Error(
                'validation_error',
                json_encode(['errors' => $errors]) ?: 'validation failed',
                ['status' => 400]
            );
        }

        return $dto;
    }
}
