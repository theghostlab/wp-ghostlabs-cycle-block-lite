<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services\Upgrade;

use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;
use GHOSTLABS\CYCLE_BLOCK\Contracts\ProInstallerInterface;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The WP_Upgrader-backed half of the upgrade: thin on purpose.
 *
 * Everything here is core machinery behaving as core behaves — which is why
 * the unit suite talks to the interface and this class is proven by the E2E
 * suite instead. Keep decisions out of this file; they belong in the
 * orchestrator where the unit suite can see them.
 */
final class ProInstaller implements ProInstallerInterface
{
    private const ENTRY_FILE = 'ghostlabs-cycle-block.php';

    /**
     * Directories a Pro install can legitimately occupy: the server's ZIP
     * wraps everything in `ghostlabs-cycle-block/` (license-manager's
     * TrunkService names the wrapper), while a locally assembled build sits in
     * `ghostlabs-cycle-block-pro/` (build.sh's TIER_SLUGS). Free's own
     * directory is `-lite` and never collides with either.
     *
     * ⚠️ Derived from `PluginConfig::$pluginSlug`, never written as literals.
     * The build's text-domain rewrite retargets the literal string
     * 'ghostlabs-cycle-block' across the whole assembled free tree, sparing
     * only `$pluginSlug`'s own declaration — a literal here was rewritten to
     * 'ghostlabs-cycle-block-lite', which made this class match the FREE
     * install as "Pro is already present", skip the download, and hand the
     * activation URL the free plugin's own basename. `ProInstallerTest` pins
     * both the values and the derivation.
     *
     * @return list<string>
     */
    public static function proDirectories(): array
    {
        return [PluginConfig::$pluginSlug, PluginConfig::$pluginSlug . '-pro'];
    }

    public function preflight(): ?array
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';

        if (get_filesystem_method() !== 'direct' || WP_Filesystem() !== true) {
            return [
                'noticeCode' => 'filesystem',
                'message'    => __('This site cannot write to its plugins directory directly. Download the Pro plugin from your account page and install it under Plugins → Add New → Upload Plugin.', 'ghostlabs-cycle-block-lite'),
            ];
        }

        return null;
    }

    public function install(string $zipUrl): array
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $existing = $this->existingProBasename();

        if ($existing !== null) {
            return ['ok' => true, 'basename' => $existing, 'noticeCode' => '', 'message' => ''];
        }

        $skin     = new \Automatic_Upgrader_Skin();
        $upgrader = new \Plugin_Upgrader($skin);

        $installed = $upgrader->install($zipUrl);
        $basename  = $upgrader->plugin_info();

        if ($installed !== true || !is_string($basename) || $basename === '') {
            return [
                'ok'         => false,
                'basename'   => null,
                'noticeCode' => 'install_failed',
                'message'    => __('The Pro package could not be installed. Download it from your account page and install it under Plugins → Add New → Upload Plugin.', 'ghostlabs-cycle-block-lite'),
            ];
        }

        return ['ok' => true, 'basename' => $basename, 'noticeCode' => '', 'message' => ''];
    }

    /**
     * A Pro build already on disk, from an earlier run that died between
     * install and activation. Detected by directory + entry file rather than
     * by activating anything: reading an inactive plugin's edition constant
     * would mean executing it.
     */
    private function existingProBasename(): ?string
    {
        foreach (array_keys(get_plugins()) as $basename) {
            // Never this plugin itself. The directory check should already
            // exclude it, but matching one's own install here is precisely the
            // failure that deactivates free and then "activates" it again.
            if ($basename === GHOSTLABS_CYCLE_BLOCK_PLUGIN_SLUG) {
                continue;
            }

            if (!str_ends_with($basename, '/' . self::ENTRY_FILE)) {
                continue;
            }

            [$directory] = explode('/', $basename, 2);

            if (in_array($directory, self::proDirectories(), true)) {
                return $basename;
            }
        }

        return null;
    }
}
