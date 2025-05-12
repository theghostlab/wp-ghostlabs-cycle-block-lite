<?php

namespace THEGHOSTLAB\CYCLE\Models;

class PluginInfo
{
    public static string $pluginSlug = 'ghostlabs-cycle-block';
    public static string $domain = 'ghostlabs_cycle_block';
    public static string $version = '1.0';
    public static string $blockName = "theghostlab/cycle";
	public static string $promoLink = 'https://theghostlab.io';
	public static string $upgradeTransient = 'ghostlabs-cycle-lite-upgrade-notice';
	public static string $nonceAction = 'upturn-fistful-hemp';
	private static string $restVersion = 'v1';
	private static string $_domain = "ghostlabs";
	private static string $_slug = 'city-blocks';
	private static string $_tier = 'lite';

	static public function noticeTransient(): string {
		return sprintf( "%s-notice", self::$pluginSlug );
	}

	static public function transientKey(): string {
		return static::$pluginSlug;
	}

	static public function instantInit(int $postId, string $blockId) : string {
        return sprintf('_theghostlab_cycle_instant_init_%d_%s', $postId, $blockId);
    }

    static public function previousSettingsTransient(int $postId, string $blockId): string{
        return sprintf('_theghostlab_cycle_previous_settings_%d_%s', $postId, $blockId);
    }

    static public function testPreviewTransient(int $postId, string $blockId): string{
        return sprintf('_theghostlab_cycle_test_preview_%d_%s', $postId, $blockId);
    }

	public static function restNameSpace(): string {
		return sprintf("%s/%s/%s/%s", self::$_domain, self::$_slug, self::$_tier, self::$restVersion);
	}
}