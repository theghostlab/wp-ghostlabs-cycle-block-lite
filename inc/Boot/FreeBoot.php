<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Boot;

use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;
use GHOSTLABS\CYCLE_BLOCK\Contracts\GateInterface;
use GHOSTLABS\CYCLE_BLOCK\Contracts\TierBootInterface;
use GHOSTLABS\CYCLE_BLOCK\Gate\OpenGate;
use GHOSTLABS\CYCLE_BLOCK\Services\Loader;
use GHOSTLABS\CYCLE_BLOCK\Services\RestRoutes\UpgradeRoutes;
use GHOSTLABS\CYCLE_BLOCK\Services\SettingsService;
use GHOSTLABS\CYCLE_BLOCK\Services\Upgrade\UpgradeEndpoints;
use GHOSTLABS\CYCLE_BLOCK\Services\Upgrade\UpgradeSupport;
use GHOSTLABS\CYCLE_BLOCK\Services\Upgrade\UpsellSource;
use GHOSTLABS\CYCLE_BLOCK\Services\UpgradePluginMeta;

if ( ! defined( 'ABSPATH' ) ) exit;

final class FreeBoot implements TierBootInterface
{
    public function gate(): GateInterface
    {
        return new OpenGate();
    }

    public function boot(Loader $loader): void
    {
        $this->defineSettingsScreen();
        $this->defineUpgradeRoutes();
        $this->defineEditorUpgradeVar();
        $this->definePluginRowUpgradeLink();
        $this->relaxTransportSecurityInDevelopment();
    }

    private function definePluginRowUpgradeLink(): void
    {
        $meta = new UpgradePluginMeta();

        add_filter('theghostlab_cycle_plugin_action_links', [$meta, 'links']);

        add_filter('theghostlab_cycle_plugin_row_meta', [$meta, 'rowMeta']);
    }

    private function defineEditorUpgradeVar(): void
    {
        add_filter('theghostlab_cycle_editor_vars', static function (array $vars): array {

            $vars['upgrade'] = [
                'urls' => UpgradeEndpoints::buyProUrls(
                    [UpsellSource::EditorSchedule, UpsellSource::EditorEvents],
                    admin_url('options-general.php?page=' . PluginConfig::$settingsMenuSlug),
                    (string) wp_parse_url(get_site_url(), PHP_URL_HOST)
                ),

                'inPlace' => ['available' => UpgradeSupport::available() && UpgradeSupport::canInstall()],
            ];

            return $vars;
        });
    }

    private function relaxTransportSecurityInDevelopment(): void
    {
    }

    private function defineUpgradeRoutes(): void
    {
        add_action('rest_api_init', static function (): void {
            (new UpgradeRoutes())->routes();
        });
    }

    public function schemaStatements(string $tablePrefix, string $charsetCollate): array
    {
        return [];
    }

    public function translationsDir(): ?string
    {
        return null;
    }

    public function legacyIndexes(): array
    {
        return [];
    }

    public function allowedSchedules(): array
    {
        return ['1-m', '7-d', '1-d'];
    }

    private function defineSettingsScreen(): void
    {
        $settings = new SettingsService();

        add_action('admin_menu', [$settings, 'page']);
        add_action('admin_enqueue_scripts', [$settings, 'scripts']);
    }
}
