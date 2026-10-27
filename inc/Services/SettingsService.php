<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use Closure;
use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;
use GHOSTLABS\CYCLE_BLOCK\Services\TierMatrix;
use GHOSTLABS\CYCLE_BLOCK\Services\Upgrade\AccountEndpoints;
use GHOSTLABS\CYCLE_BLOCK\Services\Upgrade\UpgradeEndpoints;
use GHOSTLABS\CYCLE_BLOCK\Services\Upgrade\UpsellSource;

if ( ! defined( 'ABSPATH' ) ) exit;

final class SettingsService
{
    
    private const SCREEN_ID = 'settings_page_theghostlab-cycle-settings';

    private readonly Closure $canUpload;

    public function __construct(?Closure $canUpload = null)
    {
        $this->canUpload = $canUpload ?? static fn(): bool => current_user_can('upload_plugins');
    }

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

        if ($screen === null || $screen->id !== self::SCREEN_ID) {
            return;
        }

        wp_enqueue_script('wp-element');

        wp_enqueue_script(
            'theghostlab-cycle-app',
            GHOSTLABS_CYCLE_BLOCK_PLUGIN_URL . 'assets/js/settings/settings-bundle.js',

            ['wp-element', 'wp-i18n'],
            PluginConfig::assetVersion('assets/js/settings/settings-bundle.js'),
            true
        );

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

                    'upgrade'       => [
                        'urls'       => self::buyUrls(),
                        'accountUrl' => AccountEndpoints::accountUrl(),
                        'uploadUrl'  => ($this->canUpload)()
                            ? network_admin_url('plugin-install.php?tab=upload')
                            : null,
                    ],

                    'tiers'         => TierMatrix::capabilities(),
                ], JSON_HEX_TAG | JSON_HEX_AMP)
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

    private static function buyUrls(): array
    {
        return UpgradeEndpoints::buyProUrls([UpsellSource::Settings, UpsellSource::SettingsTable]);
    }
}
