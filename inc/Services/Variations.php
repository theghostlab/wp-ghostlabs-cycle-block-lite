<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;

if ( ! defined( 'ABSPATH' ) ) exit;

class Variations
{
    static private function transient(int $postId, string $blockId): string
    {
        return sprintf(
            '_%s_%s_starting_position_%d_%s',
            PluginConfig::$domain,
            PluginConfig::$pluginSlug,
            $postId,
            $blockId
        );
    }

    static public function setStartingPosition(int $postId, string $blockId, string $currentId): void
    {
        set_transient( self::transient($postId, $blockId), $currentId, HOUR_IN_SECONDS );
    }

    static public function getStartingPosition(int $postId, string $blockId)
    {
        $transient = self::transient($postId, $blockId);

        if (false === ($position = get_transient( $transient ))) {
            $position = '';
            set_transient($transient, $position, HOUR_IN_SECONDS);
        }

        return $position;
    }

}