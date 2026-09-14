<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;
use GHOSTLABS\CYCLE_BLOCK\Services\Upgrade\UpgradeEndpoints;
use GHOSTLABS\CYCLE_BLOCK\Services\Upgrade\UpsellSource;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The free edition's one affordance on the Plugins screen.
 *
 * Registered on the shared row-links filter each edition extends with its own
 * links. This edition has no licence to report on, so it has exactly one thing
 * to say here, and it says it once.
 *
 * **Why this surface at all.** `plugin_action_links` is the only in-admin upsell
 * the WordPress review team names in writing — "a link on their entry on the
 * plugin list page". It interrupts nothing: no notice, no banner, and nobody who
 * is not already looking at plugins.php ever sees it.
 *
 * ⚠️ **One ask, emphasised once.** The upgrade link is a standard row-actions
 * link in bold, and is the *only* emphasised element the plugin puts in this row
 * — which is what the emphasis is worth. The coloured admin-menu-row pattern was
 * rejected separately, on the grounds that there is no measured lift for it
 * anywhere and under a core parent it lands in a hover flyout among ten core
 * items.
 *
 * ⚠️ **Two louder treatments were tried and dropped on 2026-08-28: GhostLabs
 * purple, then a coloured pill.** Neither was a guidelines violation — guideline
 * 5 permits the upsell outright and guideline 11 constrains scope and frequency,
 * not appearance — so what settled it was guideline 11's actual complaint, that
 * plugins should "feel like part of WordPress". A link that takes core's colour
 * and adds weight is emphatic without being a foreign object in a text strip,
 * and it inherits core's hover, focus and colour-scheme behaviour for free.
 *
 * The purple carried a second argument that is worth recording as **wrong**: that
 * blue is WordPress asking for something and purple is GhostLabs selling, so the
 * two must not share a colour. The collision cannot occur. The admin blue on this
 * screen belongs to `PluginMeta::settingsLink()`'s "needs attention" state,
 * switched on by `theghostlab_cycle_settings_link_needs_attention` — a filter
 * this edition registers no callback for. On this edition's row the Settings link
 * is never emphasised, so the upgrade link is the only weighted item in the strip.
 *
 * ⚠️ **And exactly one ask.** `rowMeta()` below carries Docs and Support and no
 * second "Upgrade". The two halves are not on the same line — they are separate
 * columns of the same row, `plugin-title column-primary` and
 * `column-description desc`, some 400px apart — so the rule is not about
 * crowding. It is that a person reading one row should be asked once. Do not
 * dismiss it on the geometry.
 */
final class UpgradePluginMeta
{
    /**
     * The one Cycle Block article the marketing site publishes.
     *
     * ⚠️ **Written twice — the other edition declares the same path.** Each tier
     * keeps its own endpoint helper on purpose, so this cannot be one constant;
     * `DocsLinkParityTest` parses both and fails if they disagree, which they
     * did until 2026-09-09. Change one, change the other.
     *
     * ⚠️ Worded without naming the other edition deliberately: this file ships
     * in the publicly distributed tier and `verify-tier-isolation.sh` reads
     * comments. Naming it here is a leak, not a nicety — and the fix for a hit
     * like that is to delete the explanation, never to widen the allow-list.
     */
    private const DOCS_PATH = '/docs/cycle-block';

    /**
     * ⚠️ Keyed by the WordPress.org DIRECTORY slug, which is **not**
     * `PluginConfig::$pluginSlug`. That is `ghostlabs-cycle-block` in both
     * editions; .org assigned `ghostlabs-cycle-block-lite` in 2025 and it cannot
     * be renamed. Deriving this from $pluginSlug sends support requests to
     * somebody else's plugin.
     *
     * The paid edition must never link here — the .org forums are staffed by
     * volunteers and do not support commercial plugins.
     */
    private const SUPPORT_URL = 'https://wordpress.org/support/plugin/ghostlabs-cycle-block-lite/';

    /**
     * Docs and Support — the right-hand column of the row.
     *
     * ⚠️ Reached through shared's guarded `theghostlab_cycle_plugin_row_meta`,
     * never by hooking `plugin_row_meta` directly: that filter is global and
     * fires for every plugin on the screen. See `PluginMeta::rowMeta()`.
     *
     * **Two links, and deliberately no third.** No second "Upgrade" — the ask
     * already sits in the action-links column of this same row, and doubling it
     * is where "spammy" starts. No "Rate us" — the review ask belongs after
     * demonstrated successful use, and a permanent row link pre-empts it with
     * something weaker.
     *
     * ❌ No `target="_blank"`. Core's own row links do not force a new tab, and
     * the one exception this plugin ships — the upgrade link — pays for it with
     * the WCAG 3.2.5 warning in `.screen-reader-text`.
     *
     * @param array<int|string, string> $meta
     * @return array<int|string, string>
     */
    public function rowMeta(array $meta): array
    {
        $meta[] = $this->rowLink(
            UpgradeEndpoints::marketingUrl(self::DOCS_PATH),
            __('Docs', 'ghostlabs-cycle-block-lite'),
            __('Cycle Block documentation', 'ghostlabs-cycle-block-lite')
        );

        $meta[] = $this->rowLink(
            self::SUPPORT_URL,
            __('Support', 'ghostlabs-cycle-block-lite'),
            __('Cycle Block support forum', 'ghostlabs-cycle-block-lite')
        );

        return $meta;
    }

    /**
     * ⚠️ Each row link is named for this plugin, not just labelled.
     *
     * A screen-reader user tabbing a list of thirty plugins otherwise hears
     * thirty identical "Docs". Core names its own row links the same way —
     * "Visit plugin site for %s".
     */
    private function rowLink(string $url, string $label, string $accessibleName): string
    {
        return sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            esc_url($url),
            esc_attr($accessibleName),
            esc_html($label)
        );
    }

    /**
     * Append the upgrade link.
     *
     * ⚠️ Appends — never rebuilds. Shared unshifts the Settings link onto this
     * array before applying the filter, so a callback that returned an array of
     * its own would drop the one link every edition is guaranteed to have.
     *
     * @param array<int|string, string> $links
     * @return array<int|string, string>
     */
    public function links(array $links): array
    {
        $links[] = $this->upgradeLink();

        return $links;
    }

    /**
     * ⚠️ The URL is built by PHP, carrying the checkout-return contract —
     * `?upgrade_return=…&domain=…` — exactly as the settings screen's CTA and
     * the editor panel's link do. A bare marketing URL here would convert
     * someone from the most-visited screen in wp-admin and then lose the
     * purchase, because the licence server would have nowhere to send them back
     * to and the buyer would be pasting a key by hand.
     *
     * The return is the settings screen: that is where the claim and install UI
     * lives.
     *
     * `target="_blank"` keeps plugins.php open behind the checkout, which means
     * WCAG 3.2.5 applies — the warning belongs in the accessible name. The
     * visible label stays two words, so it rides in `.screen-reader-text`, a
     * core admin class that is already on this screen.
     */
    private function upgradeLink(): string
    {
        // ⚠️ And it names the surface. This is the most-visited screen in
        // wp-admin, so how it performs against the editor and the settings
        // screen is the most useful comparison this plugin's attribution can
        // make — and until `from` existed all three produced an identical URL.
        //
        // Docs, in `rowMeta()` above, deliberately carries none: it shares this
        // marketing base but is not an ask, and attributing it would file
        // documentation readers as would-be buyers.
        $url = UpgradeEndpoints::buyProUrl(
            admin_url('options-general.php?page=' . PluginConfig::$settingsMenuSlug),
            (string) wp_parse_url(get_site_url(), PHP_URL_HOST),
            UpsellSource::PluginsRow
        );

        // A standard row-actions link, weighted. ⚠️ **No colour of its own**, and
        // that is the whole of the styling decision: the link keeps core's admin
        // link colour, so it follows the user's admin colour scheme, keeps core's
        // hover and focus treatment, and cannot drift from the links either side
        // of it. Weight is the one thing added, because weight is the one thing
        // that does not need a hover state to work.
        //
        // ⚠️ 700 rather than the 600 core gives `strong`/`b`, matching
        // `PluginMeta::settingsLink()`'s emphasis in the same row. The two are
        // never emphasised at once — see the class docblock — but they should
        // look the same when they are.
        return sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer" style="font-weight: 700;">%s<span class="screen-reader-text"> %s</span></a>',
            esc_url($url),
            esc_html__('Upgrade to Pro', 'ghostlabs-cycle-block-lite'),
            esc_html__('(opens in a new tab)', 'ghostlabs-cycle-block-lite')
        );
    }
}
