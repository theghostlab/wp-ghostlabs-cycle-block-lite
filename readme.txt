=== Cycle Block – Rotate & Schedule Content Variations ===
Contributors: ghostlabs
Donate link: https://theghostlab.io
Tags: content rotation, scheduled content, testimonials, gutenberg, rotating content
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Rotate content variations on a schedule — weekly, monthly, or every visit. Rendered server-side, so there's no flicker or layout shift.

== Description ==

Write several versions of a section once, and let the page show a different one over time.

A testimonial that changes every week. A hero message that rotates each month. A call to action
that differs on every visit. You write the variations in the block editor, choose how often they
change, and the page takes it from there.

**How it works**

Add a **Cycle** block, then add a variation inside it for each version you want. Anything can go
in a variation — headings, images, buttons, columns, other blocks. Pick an interval, and that is
the whole setup.

* **Daily, weekly or monthly** rotation.
* **Shuffle**, so a full pass shows every variation before any repeats.
* **Sequence**, if you want them in the order you wrote them.
* **Repeat**, or stop on the last variation once the cycle has run through.
* **Preview from the editor**, stepping through every variation without waiting for the schedule
  and without disturbing what visitors see.
* **Unlimited variations** per block, and as many blocks as you like per page.

**Rendered on the server, which matters more than it sounds**

The variation is chosen before the page is sent to the browser. Only one is ever in the HTML.

* **No flicker and no layout shift.** Nothing swaps after the page has painted, so the block
  never causes the jump that hurts Core Web Vitals.
* **No hidden duplicate content.** Search engines see one variation, not all of them stacked in
  the markup with the rest hidden by CSS.
* **No JavaScript required** to choose or display a variation.
* **Nothing is written into your post content**, so your saved posts do not change as the
  schedule advances.

**Built to behave**

* Every variation is real block content, so it inherits your theme's colours, typography and
  spacing rather than fighting them.
* The schedule is tracked per block *and* per post, so duplicating a page gives the copy its own
  schedule instead of sharing one.
* If anything goes wrong — a missing schedule, a plugin conflict — the block falls back to
  showing its content rather than breaking the page.
* Built to WCAG 2.2 AA and fully translatable.

= Source code =

The plugin's compiled JavaScript is built from readable sources, and both are published
together at:

https://github.com/theghostlab/wp-ghostlabs-cycle-block-lite

That repository contains this plugin exactly as distributed, plus the uncompiled `src/` and
`dev/` behind its bundles and the configuration used to build them. Everything builds from
public packages — `composer install && pnpm install && pnpm run build` reproduces the shipped
`build/` and `assets/js/` byte for byte.

== Installation ==

1. Install through the **Plugins** screen, or upload the plugin folder to `/wp-content/plugins/`.
2. Activate it through the **Plugins** screen.
3. In any post, page or template, add the **Cycle** block, then add a variation inside it for
   each version of the content you want.

== Frequently Asked Questions ==

= How does it decide which variation to show? =

Each block keeps a queue. When enough time has passed for the interval you chose, the next
variation in the queue is handed out and the queue moves on. With shuffle on, the queue is
reshuffled at the start of each pass, so every variation appears once before any repeats.

= What happens if nobody visits for a while? =

The block moves on by one variation, not by however many intervals were missed. A page nobody
visited for a month does not skip a month's worth of content the next time someone arrives.

= I use a caching plugin. Will variations still rotate? =

This is the one thing worth checking. A full-page cache stores the finished HTML, chosen
variation included, so the rotation effectively runs at your cache's expiry rather than at the
interval you set.

If your cache lifetime is shorter than your interval — a daily cache with a weekly rotation, for
example — everything behaves as expected. If it is longer, either shorten the cache lifetime for
those pages or lengthen the interval to match.

= Does it work with any block? =

Yes. A variation is an ordinary block container, so it can hold headings, paragraphs, images,
buttons, columns, groups, and blocks from other plugins.

= What happens to my page if the plugin is deactivated? =

Every variation shows, one after another, as ordinary content. Nothing is lost and nothing
breaks — your content was never stored anywhere but in the post itself.

= I duplicated a page. Do both copies share a schedule? =

No. The schedule is keyed to the block *and* the post, so a duplicate starts its own.

= Does this do A/B testing? =

Not in the statistical sense — it rotates content on a schedule rather than splitting traffic
and measuring significance. If you want to know which variation performs, the Pro edition adds
per-variation analytics events; this edition rotates, it does not measure.

= Why does it need PHP 8.1? =

The plugin is built on modern PHP. Most hosts have offered 8.1 or newer for some time, and your
host's control panel will show which version your site runs. Earlier releases of this plugin ran
on PHP 7.4, which has been unsupported since 2022.

== Changelog ==

= 1.1.0 =
* Rebuilt on the codebase GhostLabs develops Cycle Block from, so this edition now receives the
  same fixes as the Pro edition instead of lagging behind it.
* Accessibility pass to WCAG 2.2 AA across the editor interface.
* The whole plugin is now translatable, and translations from WordPress.org are picked up
  automatically.
* The front-end stylesheet is loaded only on pages that actually use the block, instead of on
  every page of the site.
* Fixed: a block whose queue had run out could log a PHP warning on the front end.
* Fixed: rendering over the REST API no longer advances the schedule — a request that is not a
  page view should not consume a variation.
* **Requires PHP 8.1** (previously 7.4).

== Upgrade Notice ==

= 1.1.0 =
Requires PHP 8.1 or newer; sites on an older version will not be offered this update. Every
feature you had is still here. Your content is unaffected: variations live in your posts, not in
the plugin.
