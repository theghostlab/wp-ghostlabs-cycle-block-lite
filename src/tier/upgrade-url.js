/**
 * The checkout link for one upsell surface, built by PHP and inlined through
 * shared's `theghostlab_cycle_editor_vars` filter (see
 * `FreeBoot::defineEditorUpgradeVar`).
 *
 * ⚠️ **No literal fallback, deliberately.** The editor's upsell used to hard-code
 * the marketing URL, and a hard-coded URL cannot carry the checkout-return
 * contract — `?upgrade_return=…&domain=…` — that lets the licence server send the
 * buyer back to a screen which claims the licence and installs Pro in place. The
 * link looked correct and quietly dropped the one thing that makes the in-place
 * upgrade work from here. A fallback would reintroduce exactly that: a URL that
 * renders, resolves, and loses the purchase.
 *
 * Callers render nothing rather than guess. A guessed destination is worse than
 * none: it is the case where the buyer pays and the site never finds out.
 *
 * ## ⚠️ One URL per surface, and the key IS the `from` value
 *
 * The editor holds two upsell surfaces — the schedule notice, shown when a
 * schedule is refused, and the Custom Events panel, which someone opened out of
 * curiosity. Those are different buying moments, and the ranking this plugin's
 * surfaces were built on assumes the first converts harder *without evidence*.
 * They cannot be told apart in the conversion data unless they leave with
 * different URLs, so PHP builds one per surface and this reads them by name.
 *
 * The key is the `from` value itself (`'editor-schedule'`), not a friendlier
 * alias, so the vocabulary exists once — as data, in PHP's `UpsellSource` — and
 * there is no second name here to drift from it.
 *
 * ⚠️ **The client must never assemble one of these.** Appending `&from=…` in JS
 * would put the point-of-sale URL back in the browser's hands, which is the rule
 * `FreeBoot` exists to keep. An unrecognised surface therefore resolves to
 * `undefined` and the caller renders no link — visible immediately in E2E, where
 * a silently mis-attributed link would not be.
 *
 * Read off `window` and not as a bare global: the var is assigned as a window
 * property, and a script-scoped `const` elsewhere would satisfy a bare read
 * while leaving `window.…` undefined.
 *
 * @param {string} surface The surface's `from` value, e.g. `'editor-events'`.
 * @return {string|null}
 */
export const buyProUrl = ( surface ) =>
	window.theghostlab_cycle_vars?.upgrade?.urls?.[ surface ] ?? null;

export default buyProUrl;
