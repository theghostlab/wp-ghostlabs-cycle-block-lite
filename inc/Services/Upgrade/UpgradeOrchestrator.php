<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services\Upgrade;

use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;
use GHOSTLABS\CYCLE_BLOCK\Config\UpgradeKeys;
use GHOSTLABS\CYCLE_BLOCK\Contracts\ProInstallerInterface;
use GHOSTLABS\CYCLE_BLOCK\Services\OptionsService;
use GHOSTLABS\CYCLE_BLOCK\Services\DebugLog;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The in-place free→pro upgrade, in order:
 *
 *   preflight → verify → save key → drop stale verdict → locate package
 *   → install → pending-notice handoff → install log → deactivate free
 *   → answer the activation URL
 *
 * Two rules shape the order. Nothing is WRITTEN before the key verifies —
 * `OptionsService::get()` mints the option row merely on read, so this class
 * must not even read it first. And nothing is TORN DOWN before the install
 * succeeds — deactivating free is the last state change, so every earlier
 * failure leaves a working free install behind.
 *
 * Pro is never activated from here. Its entry file would re-define constants
 * this process already defined — the activation hook would bind to the WRONG
 * entry path, never fire, and `DBSetup::maybeUpgrade()` would then
 * short-circuit on the version option forever, leaving Pro without its own
 * table. The browser follows the nonce'd core activation URL instead, so Pro
 * activates in a fresh request where free is already inactive and its own
 * activation hook runs `DBSetup::install()`.
 */
class UpgradeOrchestrator
{
    private LicenseVerifier $verifier;

    private ProPackageLocator $locator;

    private ProInstallerInterface $installer;

    private OptionsService $options;

    public function __construct(
        ?LicenseVerifier $verifier = null,
        ?ProPackageLocator $locator = null,
        ?ProInstallerInterface $installer = null,
        ?OptionsService $options = null,
    ) {
        $this->verifier  = $verifier ?? new LicenseVerifier();
        $this->locator   = $locator ?? new ProPackageLocator();
        $this->installer = $installer ?? new ProInstaller();
        $this->options   = $options ?? new OptionsService();
    }

    /** How long the concurrency lock is held before it self-heals. */
    private const LOCK_TTL = 5 * MINUTE_IN_SECONDS;

    public function run(string $licenseKey): UpgradeResult
    {
        $result = $this->attempt($licenseKey);

        // A security-relevant event whichever way it went: a success installs and
        // activates code; a failure may be a licence-key or claim-token guess run
        // through this site. Emitted for an audit-log plugin to observe what this
        // plugin does not persist itself — verify-data-safety.sh forbids it a log
        // table of its own, and nothing in free-only used to leave any trace.
        do_action('theghostlab_cycle_upgrade_event', [
            'ok'         => $result->ok,
            'stage'      => $result->stage,
            'noticeCode' => $result->noticeCode,
        ]);

        return $result;
    }

    private function attempt(string $licenseKey): UpgradeResult
    {
        // ⚠️ The sentence is NOT written here. `UpgradeSupport` is what the
        // settings screen reads to decide whether to offer a licence key field
        // at all, and what the REST gate answers a denied caller — so an offer
        // this cannot honour and the refusal that follows it can never disagree.
        // They did, for as long as this literal lived in this file.
        if (! UpgradeSupport::available()) {
            return UpgradeResult::fail('preflight', 'multisite', UpgradeSupport::message());
        }

        // One upgrade at a time. Without this, two concurrent runs both read "Pro
        // not yet installed", both call Plugin_Upgrader::install(), and the loser
        // reports a bogus failure while the winner has already swapped the editions
        // — leaving the admin a failure notice on a site with no active edition.
        if (!$this->acquireLock()) {
            return UpgradeResult::fail('preflight', 'in_progress', __('An upgrade is already running on this site. Wait for it to finish, then reload this page.', 'ghostlabs-cycle-block-lite'));
        }

        try {
            return $this->runLocked($licenseKey);
        } finally {
            $this->releaseLock();
        }
    }

    private function acquireLock(): bool
    {
        if (get_transient(PluginConfig::upgradeLockTransient())) {
            return false;
        }

        set_transient(PluginConfig::upgradeLockTransient(), 1, self::LOCK_TTL);

        return true;
    }

    private function releaseLock(): void
    {
        delete_transient(PluginConfig::upgradeLockTransient());
    }

    private function runLocked(string $licenseKey): UpgradeResult
    {
        $blocked = $this->installer->preflight();

        if ($blocked !== null) {
            return UpgradeResult::fail('preflight', $blocked['noticeCode'], $blocked['message']);
        }

        $verdict = $this->verifier->verify($licenseKey);

        if (!$verdict['ok']) {
            return UpgradeResult::fail('verify', $verdict['noticeCode'], $verdict['message']);
        }

        // The key is genuine: store it where Pro will look — the same nested
        // option row both editions share — and drop any stale verdict cached
        // before the purchase, so Pro re-verifies fresh on its first request.
        $this->options->save('licenseKey', $licenseKey);

        delete_transient(UpgradeKeys::transient());

        $package = $this->locator->locate($licenseKey);

        if (!$package['ok']) {
            return UpgradeResult::fail('download', $package['noticeCode'], $package['message']);
        }

        $install = $this->installer->install((string) $package['downloadUrl']);

        if (!$install['ok'] || $install['basename'] === null) {
            return UpgradeResult::fail('install', $install['noticeCode'] !== '' ? $install['noticeCode'] : 'install_failed', $install['message']);
        }

        set_transient(PluginConfig::upgradePendingTransient(), true, 15 * MINUTE_IN_SECONDS);

        // Fire-and-forget: the install log must not hold the response open, and
        // a lost ping costs nothing a customer can see.
        wp_remote_post(UpgradeEndpoints::installUri(), UpgradeFetch::args($licenseKey) + ['blocking' => false]);

        $this->deactivateSelf();

        return UpgradeResult::ok($this->activationUrl($install['basename']));
    }

    /**
     * Non-silent, so free's own deactivation hook (rewrite-rule flush) runs.
     * Deactivating the plugin this code belongs to is safe mid-request — the
     * classes are already loaded — and everything after this point is pure
     * composition with no plugin state left to touch.
     *
     * Wrapped so a third-party `deactivate_plugin` hook that throws cannot turn a
     * completed install into a 500 that never delivers the activation URL. The
     * install has already succeeded and the key is saved; Pro's own `admin_init`
     * deactivates free regardless once it activates, so a failed flush here costs
     * nothing but a stale rewrite cache. Returns whether it completed cleanly.
     */
    private function deactivateSelf(): bool
    {
        if (!function_exists('deactivate_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        try {
            deactivate_plugins(GHOSTLABS_CYCLE_BLOCK_PLUGIN_SLUG, false);
        } catch (\Throwable $e) {
            DebugLog::write('free deactivation hook threw during upgrade: ' . $e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Core's own plugins.php activation URL, for the browser to follow.
     *
     * The basename comes from the installer, never a constant — the server's
     * ZIP unwraps to `ghostlabs-cycle-block/`, a locally assembled build to
     * `ghostlabs-cycle-block-pro/`, and hardcoding either activates the wrong
     * path on the other layout.
     *
     * Composed with add_query_arg rather than wp_nonce_url: wp_nonce_url
     * entity-escapes ampersands for display, and this URL is consumed by
     * location.assign.
     */
    private function activationUrl(string $basename): string
    {
        $url = self_admin_url('plugins.php?action=activate&plugin=' . rawurlencode($basename));

        return add_query_arg('_wpnonce', wp_create_nonce('activate-plugin_' . $basename), $url);
    }
}
