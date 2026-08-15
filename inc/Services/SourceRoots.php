<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Where this build's PHP source and block actions actually live.
 *
 * The runtime mirror of the two-root PSR-4 map in each tier's composer.json.
 * Composer resolves classes by name; anything that resolves by *scanning a
 * directory* — REST route discovery, BlockFactory's block-actions lookup —
 * needs the same two roots, or it silently finds half the plugin.
 *
 * Three contexts, and the same rule is correct in all of them:
 *
 *   assembled build   build.sh merged shared/ and {tier}-only/ into one plugin
 *                     directory, and no `shared/` sits beside it -> one root.
 *   monorepo in place {tier}-only/ is the plugin and ../shared/ completes it
 *                     -> two roots.
 *   wp-env (E2E)      mounts {tier}-only/ as the plugin and maps the monorepo's
 *                     shared/ to wp-content/plugins/shared -> two roots.
 *
 * So the test is simply whether a sibling `shared/` exists. It is deliberately
 * not "am I in the monorepo?" — `build-scripts/` would answer that, but wp-env
 * does not mount it, and the E2E suite is the one place this is easiest to get
 * wrong and hardest to notice.
 */
final class SourceRoots
{
    /**
     * Plugin roots, tier-local first so a tier file wins over a shared one of
     * the same name — the same precedence the build's overlay gives it.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        $plugin = rtrim(GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH, '/\\');
        $roots  = [$plugin];
        $shared = dirname($plugin) . '/shared';

        if (is_dir($shared)) {
            $roots[] = $shared;
        }

        return $roots;
    }

    /**
     * Resolve a plugin-relative asset to the path *and* the public URL it is
     * actually reachable at.
     *
     * Shared assets are the one case where the two-root rule has to cover URLs
     * as well as filesystem paths. `assets/css/theghostlab-cycle.css` is
     * hand-written, enqueued by the shared render callback, and needed by both
     * tiers — so it lives in shared/. In an assembled build it has been merged
     * into the plugin and PLUGIN_URL finds it; running in place, and in wp-env
     * (which mounts {tier}-only/ as the plugin and maps shared/ beside it), it
     * sits one directory over and PLUGIN_URL points straight past it.
     *
     * That failure is a 404 on a stylesheet — silent in PHP, invisible to the
     * PHP suites, and only observable as a missing request in E2E output.
     *
     * @return array{path: string, url: string}|null Null when the asset is missing entirely.
     */
    public static function asset(string $relative): ?array
    {
        $relative = ltrim($relative, '/');
        $plugin   = rtrim(GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH, '/\\');
        $url      = rtrim(GHOSTLABS_CYCLE_BLOCK_PLUGIN_URL, '/');

        if (is_file($plugin . '/' . $relative)) {
            return [
                'path' => $plugin . '/' . $relative,
                'url'  => $url . '/' . $relative,
            ];
        }

        $shared = dirname($plugin) . '/shared/' . $relative;

        if (is_file($shared)) {
            return [
                'path' => $shared,
                // dirname() on the URL drops the tier segment the same way it
                // drops the tier directory from the path, so the two stay in step.
                'url'  => dirname($url) . '/shared/' . $relative,
            ];
        }

        return null;
    }

    /**
     * Every existing `$sub` directory across the roots.
     *
     * @return list<string>
     */
    public static function dirs(string $sub): array
    {
        $sub   = trim($sub, '/');
        $found = [];

        foreach (self::all() as $root) {
            $dir = $root . '/' . $sub;

            if (is_dir($dir)) {
                $found[] = $dir;
            }
        }

        return $found;
    }
}
