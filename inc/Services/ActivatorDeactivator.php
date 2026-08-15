<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use Exception;
use GHOSTLABS\CYCLE_BLOCK\Traits\Singleton;

final class ActivatorDeactivator
{
    use Singleton;

    /**
     * @throws Exception
     */
    static public function activate() : void {

        $dbSetup = new DBSetup();

        $dbSetup->install();

        flush_rewrite_rules();
    }

    static public function deactivate() : void {
        flush_rewrite_rules();
    }
}