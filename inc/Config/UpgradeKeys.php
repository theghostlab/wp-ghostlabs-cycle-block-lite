<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Config;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The one storage name the free tier shares with the edition it upgrades into.
 *
 * The Pro edition caches its verdict under this transient. During the in-place
 * upgrade the free tier DELETES it — a stale verdict cached before the customer
 * bought would otherwise gate Pro off until it expired — and never writes it:
 * the cached shape belongs to the proprietary licence layer, which this edition
 * must not know.
 *
 * ⚠️ **This string is permanent.** It names data on installed sites, built from
 * the same identity statics Pro's `LicenseKeys` uses; the two must stay
 * byte-identical or the delete silently misses.
 */
final class UpgradeKeys
{
    /**
     * Where the Pro edition caches its licence verdict.
     */
    public static function transient(): string
    {
        return sprintf( '%s-%s', PluginConfig::$domain, PluginConfig::$pluginSlug );
    }
}
