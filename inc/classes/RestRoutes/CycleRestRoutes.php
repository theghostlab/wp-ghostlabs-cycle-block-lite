<?php

namespace THEGHOSTLAB\CYCLE\RestRoutes;

use ReflectionException;
use THEGHOSTLAB\CYCLE\DTO\Input\ClearTestPreviewDto;
use THEGHOSTLAB\CYCLE\DTO\Input\EntryDto;
use THEGHOSTLAB\CYCLE\DTO\Input\UpdateBlockDto;
use THEGHOSTLAB\CYCLE\DTO\Validation\Input\ClearTestPreviewValidation;
use THEGHOSTLAB\CYCLE\DTO\Validation\Input\EntryValidation;
use THEGHOSTLAB\CYCLE\DTO\Validation\Input\UpdateBlockValidation;
use THEGHOSTLAB\CYCLE\Models\PluginInfo;
use THEGHOSTLAB\CYCLE\Services\Cycle;
use THEGHOSTLAB\CYCLE\Services\DBService;
use THEGHOSTLAB\CYCLE\Services\DBTables;
use THEGHOSTLAB\CYCLE\Services\DTOMapper;
use THEGHOSTLAB\CYCLE\Services\EntryHistory;
use THEGHOSTLAB\CYCLE\Services\Utils;
use THEGHOSTLAB\CYCLE\Services\Variations;
use WP_Error;
use WP_REST_Request;

class CycleRestRoutes implements RestRouteInterface {

	private Utils $utils;

	public function __construct() {
		$this->utils = new Utils();
	}

	public function routes(): void {

		register_rest_route(PluginInfo::restNameSpace(), '/update-block', [
			[
				'methods' => 'POST',
				'callback' => [$this,'updateBlock'],
				'permission_callback' => fn () => current_user_can('manage_options')
			],
		]);

		register_rest_route(PluginInfo::restNameSpace(), '/clear-test-preview', [
			[
				'methods' => 'POST',
				'callback' => [$this,'clearTestPreview'],
				'permission_callback' => fn () => current_user_can('manage_options')
			],
		]);

		register_rest_route(PluginInfo::restNameSpace(), '/update-on-entry-change', [
			[
				'methods' => 'POST',
				'callback' => [$this,'updateOnEntryChange'],
				'permission_callback' => fn () => current_user_can('manage_options')
			],
		]);
	}

	public function updateBlock(WP_REST_Request $request) {
		$data = $request->get_body_params();
		$payload = $this->utils->setPayload($data);

		try {
			/** @var UpdateBlockDto $dto */
			$dto = DTOMapper::map($payload, UpdateBlockDto::class);

			$errors = DTOMapper::validate($dto, UpdateBlockValidation::rules());

			if (!empty($errors)) {
				throw new ReflectionException(json_encode($errors), 412);
			}

			$DBService = new DBService();

			$fetchLastFrequency = $DBService->getTableByKeys(DBTables::frequencyTable(), ['blockId' =>  $dto->blockId], 'LIMIT 1');

			$check = $fetchLastFrequency[0]['block_id'] ?? null;

			if( !$check ) {
				EntryHistory::setCache($dto->blockId, $dto->entryId);
			}

			return rest_ensure_response([
				'status' => true,
			]);

		} catch ( ReflectionException $e) {
			return rest_ensure_response(new WP_Error('validation_error', esc_html($e->getMessage())));
		}
	}

	public function clearTestPreview(WP_REST_Request $request) {
		$data = $request->get_body_params();
		$payload = $this->utils->setPayload($data);

		try {

			/** @var ClearTestPreviewDto $dto */
			$dto = DTOMapper::map($payload, ClearTestPreviewDto::class);

			$errors = DTOMapper::validate($dto, ClearTestPreviewValidation::rules());

			if (!empty($errors)) {
				throw new ReflectionException(json_encode($errors), 412);
			}

			$transient = PluginInfo::testPreviewTransient($dto->postId, $dto->clientId);

			$status = delete_transient($transient);

			return rest_ensure_response([
				'status' => $status,
			]);

		} catch ( ReflectionException $e) {
			return rest_ensure_response(new WP_Error('validation_error', esc_html($e->getMessage())));
		}
	}

	public function updateOnEntryChange(WP_REST_Request $request) {

		$body = $request->get_body_params();

		if( empty($body) ) return rest_ensure_response(new WP_Error('missing_body', 'Missing body.'));

		$data = $this->utils->setPayload($body);

		try {
			/** @var EntryDTO $dto */
			$dto = DTOMapper::map($data, EntryDTO::class);

			$errors = DTOMapper::validate($dto, EntryValidation::rules());

			if (!empty($errors)) {
				throw new ReflectionException(json_encode($errors), 412);
			}

			Variations::setStartingPosition($dto->postId, $dto->blockId, $dto->startingPosition);

			if ( strcmp($dto->startingPosition, $dto->currentId) !== 0) {

				Cycle::reset($dto->blockId);

				wp_send_json(['status' => $dto->currentId]);

				return rest_ensure_response([
					'status' => $dto->currentId,
				]);
			}

			return rest_ensure_response([
				'status' => null,
			]);

		} catch ( ReflectionException $e) {
			return rest_ensure_response(new WP_Error('validation_error', esc_html($e->getMessage())));
		}
	}
}