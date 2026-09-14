<?php

namespace GHOSTLABS\CYCLE_BLOCK\Config;

use GHOSTLABS\CYCLE_BLOCK\Services\SourceRoots;

if ( ! defined( 'ABSPATH' ) ) exit;

class PluginConfig
{
    public static string $settingsMenuSlug = 'theghostlab-cycle-settings';
    public static string $pluginSlug = 'ghostlabs-cycle-block';
    public static string $domain = 'ghostlabs_cycle_block';
    
    public static string $version = GHOSTLABS_CYCLE_BLOCK_VERSION;
    public static string $blockName = "theghostlab/cycle";
    public static string $restVersion = 'v1';
    public static string $_domain = "ghostlabs";
    public static string $_slug = 'cycle-block';
    
    public static string $_tier = GHOSTLABS_CYCLE_BLOCK_EDITION;

    static public function instantInit(int $postId, string $blockId) : string {
        return sprintf('_theghostlab_cycle_instant_init_%d_%s', $postId, $blockId);
    }

    static public function testPreviewTransient(int $postId, string $blockId): string{
        return sprintf('_theghostlab_cycle_test_preview_%d_%s', $postId, $blockId);
    }

    static public function rateLimitTransient(string $key): string {
        return sprintf('_theghostlab_cycle_rl_%s', md5($key));
    }

    static public function upgradePendingTransient(): string {
        return '_theghostlab_cycle_upgrade_pending';
    }

    static public function upgradeLockTransient(): string {
        return '_theghostlab_cycle_upgrade_lock';
    }

    static public function assetVersion(string $relativePath): string {

        $asset = SourceRoots::asset($relativePath);

        $absolute = $asset['path']
            ?? GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH . ltrim($relativePath, '/');

        $mtime = file_exists($absolute) ? filemtime($absolute) : false;

        return $mtime === false
            ? static::$version
            : sprintf('%s.%d', static::$version, $mtime);
    }

    public static function restNameSpace(): string {
        return sprintf("%s/%s/%s/%s", self::$_domain, self::$_slug, self::$_tier, self::$restVersion);
    }
}