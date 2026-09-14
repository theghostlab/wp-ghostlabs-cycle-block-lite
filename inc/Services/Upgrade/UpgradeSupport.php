<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services\Upgrade;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Can this site be upgraded in place, and if not, what does this caller need to
 * be told?
 *
 * One answer, read by everything that either OFFERS the upgrade or PERFORMS it:
 * the settings screen (which renders the key field), the editor's two upsell
 * surfaces (which promise "installs in place"), the REST routes (which gate on
 * capability) and the orchestrator (which refuses). Before this class the offer
 * and the refusal were decided in different files, and they disagreed — the
 * screen took a licence key on a network and the orchestrator then explained,
 * to someone who had already paid, that it was never going to work.
 *
 * ⚠️ **Availability is a property of the SITE; capability is a property of the
 * USER.** They are separate questions and both must be asked. A super admin on a
 * network can install plugins and still cannot use the in-place swap, and the
 * reason is the ACTIVATION half, not the deactivation half: `deactivate_plugins()`
 * with `$network_wide` left at its `null` default does unwind a network
 * activation correctly (core `wp-admin/includes/plugin.php`), but
 * `UpgradeOrchestrator` finishes by handing the browser a per-blog
 * `plugins.php?action=activate` URL. Run on a network that takes free down
 * everywhere and brings Pro up on exactly one subsite — every other site loses
 * Cycle Block outright. That is why this refuses on `is_multisite()` rather than
 * on a capability.
 *
 * ⚠️ **Three refusals, because three different people hit this.** A subsite
 * Administrator is denied `install_plugins` by core on every multisite
 * (`wp-includes/capabilities.php`), so they never reached the network message at
 * all — they saw the whole panel, submitted, and got a bare 403 with nothing in
 * it. Telling them to "install it from the network admin" would be worse than
 * silence: it names a screen they cannot open.
 */
final class UpgradeSupport
{
    /** In place, here, now. The only state that renders a key field. */
    public const AVAILABLE = 'available';

    /** A network, and this caller could install it themselves. */
    public const NETWORK_ADMIN = 'network_admin';

    /** A network, and installing is somebody else's job. */
    public const NETWORK_DELEGATED = 'network_delegated';

    /** File modification is switched off — DISALLOW_FILE_MODS, or a host filter. */
    public const RESTRICTED = 'restricted';

    /** Nothing is switched off; this caller simply may not install plugins. */
    public const NO_PERMISSION = 'no_permission';

    /**
     * The capabilities core itself demands to install and activate a plugin.
     *
     * ⚠️ Kept identical to `UpgradeRoutes`' permission gate on purpose: if the
     * screen offers what the route refuses, the buyer meets the disagreement.
     */
    public static function canInstall(): bool
    {
        return current_user_can('manage_options')
            && current_user_can('install_plugins')
            && current_user_can('activate_plugins');
    }

    /** Whether the in-place swap can run on this site at all. */
    public static function available(): bool
    {
        return ! is_multisite();
    }

    /**
     * ⚠️ **File modification is asked about FIRST, and before multisite.** Core
     * denies `install_plugins` for `DISALLOW_FILE_MODS` in
     * `wp-includes/capabilities.php` *before* it reaches its multisite branch,
     * so on a locked-down network a capability check alone cannot tell a super
     * admin apart from a subsite Administrator — and the delegated sentence then
     * tells the one person who could act to go and ask a network administrator,
     * which is themselves.
     *
     * Read through `wp_is_file_mod_allowed()` rather than the constant: it is
     * core's own seam, a host can filter it, and a constant cannot be undefined
     * once something has defined it.
     */
    public static function state(): string
    {
        if (! wp_is_file_mod_allowed('can_install_plugins')) {
            return self::RESTRICTED;
        }

        if (is_multisite()) {
            return self::canInstall() ? self::NETWORK_ADMIN : self::NETWORK_DELEGATED;
        }

        return self::canInstall() ? self::AVAILABLE : self::NO_PERMISSION;
    }

    /**
     * ⚠️ Resolved at call time and never memoised. This class is reachable from
     * `admin_init` and from a REST request, and `__()` caches nothing itself —
     * a static here would bake in whichever locale registered first, the same
     * trap `TierMatrix` and `intervals.js` both carry.
     */
    public static function message(): string
    {
        switch (self::state()) {
            case self::NETWORK_ADMIN:
                return __(
                    'The in-place upgrade doesn\'t run on multisite networks. Download Cycle Block Pro from your account page and install it from the network admin — you\'ll enter your license key on each site.',
                    'ghostlabs-cycle-block-lite'
                );

            case self::NETWORK_DELEGATED:
                return __(
                    'Installing plugins on this network is done by a network administrator. Ask them to install Cycle Block Pro — your blocks and schedules stay exactly as they are.',
                    'ghostlabs-cycle-block-lite'
                );

            case self::RESTRICTED:
                return __(
                    'Installing plugins is turned off on this site. Ask whoever manages it to install Cycle Block Pro — your blocks and schedules stay exactly as they are.',
                    'ghostlabs-cycle-block-lite'
                );

            case self::NO_PERMISSION:
                return __(
                    'You don\'t have permission to install plugins on this site. Ask whoever manages it to install Cycle Block Pro — your blocks and schedules stay exactly as they are.',
                    'ghostlabs-cycle-block-lite'
                );
        }

        return '';
    }

    /**
     * What the settings app and the editor read.
     *
     * @return array{available: bool, state: string, message: string}
     */
    public static function config(): array
    {
        return [
            'available' => self::available() && self::canInstall(),
            'state'     => self::state(),
            'message'   => self::message(),
        ];
    }
}
