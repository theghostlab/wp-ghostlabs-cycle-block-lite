<?php

namespace THEGHOSTLAB\CYCLE\Services;

use Exception;
use THEGHOSTLAB\CYCLE\Models\PluginInfo;
use THEGHOSTLAB\CYCLE\Traits\Singleton;

class ActivatorDeactivator
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

	    delete_transient(PluginInfo::transientKey());
	    delete_transient(PluginInfo::noticeTransient());

	    delete_option(PluginInfo::$domain);

        flush_rewrite_rules();
    }
}