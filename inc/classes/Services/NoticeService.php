<?php

namespace THEGHOSTLAB\CYCLE\Services;

use THEGHOSTLAB\CYCLE\Models\PluginInfo;

class NoticeService {

	private DBService $dbService;

	public function __construct(DBService $dbService) {
		$this->dbService = $dbService;
	}

	public function upgradeNotice() {
		$hasCycleBlock = $this->dbService->getQueueTableCount();

		if( !$hasCycleBlock ) return;

        if($this->showUpgradeNotice()) return;

		?>
		<div id="ghostlabs-cycle-notice" class="notice notice-info is-dismissible">
			<p>Like <b>City Blocks</b> by GhostLabs? <a href="https://theghostlab.io/cycle-block#plans" target="_blank">Need more functionality?</a></p>
		</div>
		<?php
	}

	private function showUpgradeNotice() {
		return get_transient( PluginInfo::$upgradeTransient );
	}
}