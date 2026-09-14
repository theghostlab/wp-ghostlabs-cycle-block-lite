<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services\Upgrade;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Every URL the free tier's upgrade path talks to, in one place.
 *
 * The host is the DEV literal on purpose — the same contract as Pro's
 * `Services\Constants`: deploy workflows sed `https://enter.theghostlab.io` to the
 * staging or production host across every PHP file. **Keep the literal intact
 * in source**, and never compose the host through a helper the sed would miss.
 */
final class UpgradeEndpoints
{
    private static string $serviceUrl = "https://enter.theghostlab.io/_service";

    /**
     * The public (non-/_service) half of the licence server, where the
     * checkout-return claim is redeemed — public because the caller's whole
     * problem is that it holds no key to authenticate with yet.
     */
    private static string $publicUrl = "https://enter.theghostlab.io";

    /**
     * Where "Buy Cycle Block Pro" sends the customer. One constant so the
     * point-of-sale front door can move without touching the flow around it.
     */
    /**
     * The marketing site's origin — a **second** rewrite axis, separate from the
     * licence backend above.
     *
     * ⚠️ It used to be a finished production URL (`…/cycle-block-pro`), and that
     * was two bugs in one field. It was a 404 — the marketing route is
     * `/cycle-block`, with no redirect — and because it was not the dev host,
     * `finalize_for_release`'s rewrite never touched it, so **every** build sent
     * the buyer to production, dev builds included. The release assertion could
     * not catch it either: that guard greps for the surviving *backend* dev
     * host, and this literal was not that one.
     *
     * ⚠️ Do not name the dev backend host in prose anywhere in shipped source.
     * The rewrite matches it only with its scheme, while the assertion greps for
     * the bare host — so a mention in a comment survives the first and trips the
     * second, failing the release build on a sentence.
     *
     * So it follows the same contract the backend URLs do: a dev literal in
     * source, rewritten at release — here by `build.sh --marketing-base`,
     * because the marketing site and the licence backend are different origins
     * that move independently.
     *
     * ⚠️ Whole and greppable. The rewrite is textual, so this must stay one
     * literal string; composing it from a scheme and a host is the "helper the
     * sed would miss" this class's docblock already warns about. The *path* may
     * be concatenated — the base is what gets rewritten.
     */
    private const MARKETING_BASE = 'https://theghostlab.io';

    /** The plugin's page on the marketing site. */
    private const MARKETING_PATH = '/cycle-block';

    /**
     * Any other page on the marketing site, off the same rewritten base.
     *
     * Exposed so nothing else in this edition has to write a marketing URL of
     * its own — the plugin row's Docs link is the second consumer, and a second
     * literal is precisely how the buy link became a 404 that no release check
     * could see.
     */
    static public function marketingUrl(string $path): string {
        return self::MARKETING_BASE . $path;
    }

    static public function licenseCheck(): string {
        return sprintf("%s/%s", self::$serviceUrl, 'check-license');
    }

    static public function updateUri(): string {
        return sprintf("%s/%s", self::$serviceUrl, 'plugin-updater');
    }

    static public function installUri(): string {
        return sprintf("%s/%s", self::$serviceUrl, 'plugin-installed');
    }

    static public function claimLicenseUri(): string {
        return sprintf("%s/%s", self::$publicUrl, 'claim-license');
    }

    /**
     * With a return URL and domain, the link carries the checkout-return
     * contract — addresses only, never secrets. The licence server's
     * UpgradeReturnPolicy re-validates both before any redirect exists, so
     * this side stays a courier, not a gatekeeper.
     *
     * `$source` names which of this edition's upsell surfaces was clicked, and
     * is a **third, independent axis**: a link can carry the return contract
     * without a source, or a source without the return contract. Every shipped
     * caller passes one — `UpsellAttributionTest` fails on any that does not —
     * but the parameter stays optional so `buyProUrl()` remains a legible
     * expression of "the plugin's page on the marketing site".
     *
     * ⚠️ The source is appended **after** the contract, never in place of it.
     * The failure worth guarding here is a parameter displacing another rather
     * than one going missing: a `from` that overwrote `domain` would still look
     * like a perfectly working link, and would lose the purchase.
     *
     * ⚠️ It is a hint and never an authorisation — see `UpsellSource`. The far
     * side must treat it as untrusted display data.
     */
    static public function buyProUrl(
        ?string $returnUrl = null,
        ?string $domain = null,
        ?UpsellSource $source = null
    ): string {
        $buyProUrl = self::MARKETING_BASE . self::MARKETING_PATH;

        $query = [];

        if ($returnUrl !== null && $domain !== null) {
            $query[] = 'upgrade_return=' . rawurlencode($returnUrl);
            $query[] = 'domain=' . rawurlencode($domain);
        }

        if ($source !== null) {
            // Not rawurlencode'd through a variable the eye can skip: the values
            // are asserted URL-safe in UpsellSourceTest, so what ships in the
            // markup is the same string the licence server files it under and
            // the same string a human reads in an analytics report.
            $query[] = 'from=' . $source->value;
        }

        return $query === []
            ? $buyProUrl
            : $buyProUrl . '?' . implode('&', $query);
    }

    /**
     * Several surfaces' links at once, keyed by the surface each belongs to.
     *
     * Both screens that ask more than once — the editor sidebar and the settings
     * page — publish their links to JS as a map keyed by `from`. Building it here
     * is what makes the key and the URL's own `from` **one fact rather than two**:
     * a caller that assembled the map itself could key `'settings'` at a URL
     * saying `settings-table`, and nothing would look wrong anywhere except in a
     * report, months later.
     *
     * ⚠️ It also keeps each surface named *in the caller's own argument list*.
     * A caller that looped over sources and passed a variable would pass every
     * behavioural test and still read as unattributed to
     * `UpsellAttributionTest`'s source scan — which is the gate that catches the
     * surface nobody has written yet.
     *
     * Order is the caller's: the map preserves the order the surfaces were
     * declared in, so the inlined JSON reads the way the screen does.
     *
     * @param list<UpsellSource> $sources
     * @return array<string, string>
     */
    static public function buyProUrls(
        array $sources,
        ?string $returnUrl = null,
        ?string $domain = null
    ): array {
        $urls = [];

        foreach ($sources as $source) {
            $urls[$source->value] = self::buyProUrl($returnUrl, $domain, $source);
        }

        return $urls;
    }
}
