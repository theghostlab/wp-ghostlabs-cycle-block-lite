<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services\Upgrade;

use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The HTTP arguments every upgrade-service call shares.
 *
 * license-manager authenticates on the Bearer token and routes on the Domain,
 * Plugin-Slug and Plugin-Tier headers — it answers for the wrong product if any
 * of the last three is missing.
 *
 * `Plugin-Tier` is the literal `pro`, never `PluginConfig::$_tier`: this caller
 * is asking about the PRO entitlement and package it wants to install, not
 * about itself. The server half of that contract is pinned by license-manager's
 * FreeUpgradeCallerTest.
 */
final class UpgradeFetch
{
    /** @return array<string, mixed> */
    public static function args(string $licenseKey): array
    {
        $host = wp_parse_url(get_site_url(), PHP_URL_HOST);

        return [
            'timeout' => 45,
            'headers' => [
                'Authorization' => 'Bearer ' . $licenseKey,
                'Domain'        => is_string($host) ? $host : '',
                'Plugin-Slug'   => PluginConfig::$_slug,
                'Plugin-Tier'   => 'pro',
            ],
        ];
    }
}
