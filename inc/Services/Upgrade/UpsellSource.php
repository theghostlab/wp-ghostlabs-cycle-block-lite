<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services\Upgrade;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Which upsell surface a buyer left from, as the `from` parameter on the buy link.
 *
 * Four surfaces ask for the sale — the block inspector's schedule notice, the
 * inspector's Custom Events panel, the Plugins-screen action link and the
 * settings screen (twice, hero and table foot) — and until this enum existed
 * every one of them produced a byte-identical URL. Nothing distinguished a
 * purchase that started at one from a purchase that started at another.
 *
 * That matters because **the ranking those surfaces were built on is a guess.**
 * The editor was put first on reasoning, not evidence: none of the plugins
 * studied for it is a block plugin, so no research measured the surface this one
 * actually lives on. This is the cheap way to find out whether the guess holds.
 *
 * ⏳ It had to land while the surfaces were being built. Retrofitting attribution
 * after launch throws away the first months of data, which are the months where
 * the answer is most useful and most cheaply acted on.
 *
 * ## ⚠️ A hint, never an authorisation
 *
 * This value is user-editable — it rides in a query string the buyer can retype —
 * and it crosses an origin. The licence server must treat it as **untrusted
 * display data**: it must not influence pricing, entitlement, the return-URL
 * validation, or anything in `UpgradeReturnPolicy`. It answers "where did this
 * come from", and nothing else may be decided by it.
 *
 * 🟢 It raises no consent or guideline question. It is a query parameter on a
 * link the author chose to click: no cookie, no client-side tracking, nothing
 * sent on any other page load. Guideline 7 prohibits offloading assets and
 * tracking; this is neither.
 *
 * ## ⚠️ The values are published, and renaming one is a data migration
 *
 * They land in the licence server's records and become the buckets a conversion
 * report is read out of. Rename a case and that surface's history splits in two,
 * with the older half filed under a name nothing queries any more. Add cases;
 * do not repurpose them. `UpsellSourceTest` pins all five.
 *
 * The enum is `string`-backed so no call site ever spells the value itself. A
 * typo'd `from` is silent — it produces a perfectly real URL and an attribution
 * bucket nobody reads — and `UpsellAttributionTest` fails on any `buyProUrl()`
 * call that does not name a case here.
 */
enum UpsellSource: string
{
    /**
     * The block inspector's schedule notice — shown the moment this edition
     * refuses a Pro schedule the author just picked.
     *
     * 🟡 Deliberately split from `EditorEvents` rather than merged into one
     * `editor`. The extra URL costs nothing and answers a question the merged
     * version cannot: whether authors convert at the moment a feature is
     * *refused*, or the moment they go *reading* about one. Those are different
     * buying moments, and the surface ranking currently assumes the first is
     * stronger without evidence.
     */
    case EditorSchedule = 'editor-schedule';

    /** The block inspector's Custom Events panel — the feature tour, not a refusal. */
    case EditorEvents = 'editor-events';

    /** The Plugins-screen action link, the one in-admin surface .org names in writing. */
    case PluginsRow = 'plugins-row';

    /** Settings → Cycle Block, the hero band's pill. */
    case Settings = 'settings';

    /**
     * Settings → Cycle Block, the CTA at the foot of the comparison table.
     *
     * The same offer as `Settings`, not a second one — the table is tall enough
     * to push the hero's button off-screen. Separate because "converted after
     * reading the comparison" and "converted off the headline" are different
     * readings of the same screen.
     */
    case SettingsTable = 'settings-table';
}
