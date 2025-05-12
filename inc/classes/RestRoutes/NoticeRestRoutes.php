<?php

namespace THEGHOSTLAB\CYCLE\RestRoutes;

use THEGHOSTLAB\CYCLE\Models\PluginInfo;
use THEGHOSTLAB\CYCLE\Services\Utils;
use WP_REST_Request;

class NoticeRestRoutes implements RestRouteInterface {

	private int $showUpgradeNoticeEvery = 6 * HOUR_IN_SECONDS;
	private Utils $utils;

	public function __construct() {
		$this->utils = new Utils();
	}

	public function dismissUpgradeNotice(WP_REST_Request $request)  {
		$data = $request->get_body_params();

		$payload = $this->utils->setPayload($data);

		if( $payload['dismissNotice'] ) {
			$this->showUpgradeNotice();
		}

		return rest_ensure_response([
			'status' => true,
		]);
	}

	public function routes(): void {

		register_rest_route(PluginInfo::restNameSpace(), '/dismiss-upgrade-notice/', [
			[
				'methods' => 'POST',
				'callback' => [$this,'dismissUpgradeNotice'],
				'permission_callback' => fn () => current_user_can('manage_options')
			],
		]);

	}

	private function showUpgradeNotice() {
		$transient = PluginInfo::$upgradeTransient;

		if ( false === ( $value = get_transient( $transient ) ) ) {
			set_transient($transient, true, $this->showUpgradeNoticeEvery);
		}

		return $value;
	}
}