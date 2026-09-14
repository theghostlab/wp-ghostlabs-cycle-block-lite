<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;

if ( ! defined( 'ABSPATH' ) ) exit;

class EntryHistory
{
    static private function transient(int $postId, string $blockId): string
    {
        return sprintf(
            '_%s_%s_history_%d_%s',
            PluginConfig::$domain,
            PluginConfig::$pluginSlug,
            $postId,
            $blockId
        );
    }

    static public function clearCache(int $postId, string $blockId): void
    {
        $transient = self::transient($postId, $blockId);

        delete_transient($transient);
    }

    static public function setCache(int $postId, string $blockId, string $entryId): void
    {
        $cache = self::getCache($postId, $blockId);

        $cache[] = $entryId;

        $cache = array_unique($cache);

        set_transient( self::transient($postId, $blockId), $cache, HOUR_IN_SECONDS );
    }

    static public function getCache(int $postId, string $blockId)
    {
        $transient = self::transient($postId, $blockId);

        if (false === ($cache = get_transient( $transient ))) {
            $cache = [];
            set_transient($transient, $cache, HOUR_IN_SECONDS);
        }

        return $cache;
    }
}
