<?php

namespace THEGHOSTLAB\CYCLE\Services;

use THEGHOSTLAB\CYCLE\Models\PluginInfo;

class EntryHistory
{
    static private function transient(string $blockId): string
    {
        return sprintf(
            '_%s_%s_history_%s',
            PluginInfo::$domain,
            PluginInfo::$pluginSlug,
            $blockId
        );
    }

    static public function clearCache(string $blockId)
    {
        $transient = self::transient($blockId);

        delete_transient($transient);
    }

    static public function setCache(string $blockId, string $entryId)
    {
        $cache = self::getCache($blockId);

        $cache[] = $entryId;

        $cache = array_unique($cache);

        set_transient( self::transient($blockId), $cache, HOUR_IN_SECONDS );
    }

    static public function getCache(string $blockId)
    {
        $transient = self::transient($blockId);

        if (false === ($cache = get_transient( $transient ))) {
            $cache = [];
            set_transient($transient, $cache, HOUR_IN_SECONDS);
        }

        return $cache;
    }
}