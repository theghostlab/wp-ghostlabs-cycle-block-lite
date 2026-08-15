<?php

namespace GHOSTLABS\CYCLE_BLOCK\Config;

use GHOSTLABS\CYCLE_BLOCK\Services\SourceRoots;

class PluginConfig
{
    public static string $settingsMenuSlug = 'theghostlab-cycle-settings';
    public static string $pluginSlug = 'ghostlabs-cycle-block';
    public static string $domain = 'ghostlabs_cycle_block';
    /**
     * Derived from the entry file's constant, never written by hand.
     *
     * WordPress reads the `Version:` header, and an edition with self-hosted
     * updates compares this value against what its update server reports. A
     * hardcoded copy drifts: at 0.8.0 against a 0.9.0 header the plugin reported
     * itself older than it was and offered an update to the build already
     * installed. Bumping a release means editing the header templates only.
     */
    public static string $version = GHOSTLABS_CYCLE_BLOCK_VERSION;
    public static string $blockName = "theghostlab/cycle";
    public static string $restVersion = 'v1';
    public static string $_domain = "ghostlabs";
    public static string $_slug = 'cycle-block';
    /**
     * The edition this build is, and the third segment of the REST namespace.
     *
     * Hardcoded until 2026-08-13, so every edition registered — and publicly
     * advertised through the REST index — the same namespace regardless of what
     * it actually was. Nothing broke, which is why it survived: the editor reads
     * the namespace from the same helper that registers the routes, so both
     * sides agreed on the wrong name and no click or test could see it.
     *
     * Safe to derive because `$_tier` feeds only the namespace and an outbound
     * request header. It deliberately does **not** feed the option domain or
     * `$pluginSlug`, so no existing install's stored data moves. Keep it that
     * way: wiring it into a key builder would silently re-key every install at
     * once, and there is no migration routine to catch it.
     *
     * ⚠️ Treat the namespace as frozen once an edition is published — installed
     * editors post to it.
     *
     * `RestNamespaceTest` pins both halves, in both tiers.
     */
    public static string $_tier = GHOSTLABS_CYCLE_BLOCK_EDITION;

    static public function instantInit(int $postId, string $blockId) : string {
        return sprintf('_theghostlab_cycle_instant_init_%d_%s', $postId, $blockId);
    }

    static public function testPreviewTransient(int $postId, string $blockId): string{
        return sprintf('_theghostlab_cycle_test_preview_%d_%s', $postId, $blockId);
    }

    /**
     * Bucket name for a rate-limit counter.
     *
     * The caller-supplied key (a client IP, usually) is hashed so an unexpected
     * value can never overrun WordPress's transient-name length limit or carry
     * characters the options table would choke on.
     */
    static public function rateLimitTransient(string $key): string {
        return sprintf('_theghostlab_cycle_rl_%s', md5($key));
    }

    /**
     * Cache-busting `?ver=` for a built asset, derived from its mtime.
     *
     * $version alone is not enough: it only moves on release, so a rebuilt bundle
     * keeps the same query string and browsers go on serving the copy they
     * already have. That bit hard when the settings app moved to REST — cached
     * bundles kept calling admin-ajax actions that had been deleted, and
     * WordPress answers an unregistered action with a bare 400.
     *
     * The plugin version stays on the front so an asset URL still says which
     * release it belongs to. Missing files fall back to it: the build only warns
     * about absent generated directories, so this must not be fatal.
     */
    static public function assetVersion(string $relativePath): string {
        // Across both roots, like the URL it cache-busts: a shared asset sits
        // beside the tier folder when running in place, and a miss here silently
        // falls back to $version — which only moves on release, so the browser
        // keeps serving a stale copy through every rebuild.
        $asset = SourceRoots::asset($relativePath);

        $absolute = $asset['path']
            ?? GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH . ltrim($relativePath, '/');

        $mtime = file_exists($absolute) ? filemtime($absolute) : false;

        return $mtime === false
            ? static::$version
            : sprintf('%s.%d', static::$version, $mtime);
    }

    /**
     * @return non-falsy-string register_rest_route() requires a non-empty
     *         namespace, and every component of this sprintf is a non-empty
     *         class constant.
     */
    public static function restNameSpace(): string {
        return sprintf("%s/%s/%s/%s", self::$_domain, self::$_slug, self::$_tier, self::$restVersion);
    }
}