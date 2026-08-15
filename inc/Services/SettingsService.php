<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Settings → Cycle Block, free edition.
 *
 * The shared settings shell, with one tab: the upgrade path. This edition
 * stores no options, so there is nothing else to put here.
 *
 * It inlines only what that tab needs — the root element id and the REST
 * namespace. The shell also has built-in components this edition does not
 * register; leaving them out of the tab list is what keeps them dormant. See
 * settings.jsx for why omission alone is sufficient.
 */
final class SettingsService
{
    /** The one admin screen this service owns. */
    private const SCREEN_ID = 'settings_page_theghostlab-cycle-settings';

    public function page(): void
    {
        add_submenu_page(
            'options-general.php',
            __('Cycle Block Settings', 'ghostlabs-cycle-block-lite'),
            __('Cycle Block', 'ghostlabs-cycle-block-lite'),
            'manage_options',
            PluginConfig::$settingsMenuSlug,
            [$this, 'page_render'],
        );
    }

    public function page_render(): void
    {
        echo '<div id="theghostlab-cycle-settings-root"></div>';
    }

    public function scripts(): void
    {
        $screen = get_current_screen();

        // get_current_screen() is null until WordPress has set one up.
        // admin_enqueue_scripts normally runs after that, but not on every admin
        // entry point, and reading ->id off null is fatal in PHP 8.
        if ($screen === null || $screen->id !== self::SCREEN_ID) {
            return;
        }

        wp_enqueue_script('wp-element');

        wp_enqueue_script(
            'theghostlab-cycle-app',
            GHOSTLABS_CYCLE_BLOCK_PLUGIN_URL . 'assets/js/settings/settings-bundle.js',
            ['wp-element', 'wp-api-fetch', 'wp-data', 'wp-i18n'],
            PluginConfig::assetVersion('assets/js/settings/settings-bundle.js'),
            true
        );

        /**
         * The app reads the global wp.i18n — esbuild bundles for real, so a
         * bundled @wordpress/i18n would keep its own locale registry this call
         * could never reach. The wp-i18n dependency above guarantees the global
         * exists before the bundle runs.
         */
        wp_set_script_translations(
            'theghostlab-cycle-app',
            'ghostlabs-cycle-block-lite',
            GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH . 'languages'
        );

        wp_add_inline_script(
            'theghostlab-cycle-app',
            sprintf(
                'window.GHOSTLABS_CYCLE_BLOCK_CONFIG = %s;',
                wp_json_encode([
                    'rootElementId' => 'theghostlab-cycle-settings-root',
                    'restNamespace' => PluginConfig::restNameSpace(),
                ])
            ),
            'before'
        );

        wp_enqueue_style(
            'theghostlab-cycle-app-css',
            GHOSTLABS_CYCLE_BLOCK_PLUGIN_URL . 'assets/css/settings.css',
            [],
            PluginConfig::assetVersion('assets/css/settings.css')
        );
    }
}
