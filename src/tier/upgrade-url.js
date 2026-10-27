/**
 * The checkout link for one upsell surface, built by PHP and inlined through
 * shared's `theghostlab_cycle_editor_vars` filter (see
 * `FreeBoot::defineEditorUpgradeVar`).
 *
 * ⚠️ **No literal fallback, deliberately.** A hard-coded URL carries no `from`,
 * so it renders, resolves, and files the sale under no surface at all — the
 * failure attribution exists to prevent, and one nothing on screen would show.
 * Callers render nothing rather than guess.
 *
 * ✅ *(was 🔴)* Until 2026-10-08 the URL also carried the checkout-return
 * contract (`upgrade_return`, `domain`), so the licence server could send the
 * buyer back to finish an in-place upgrade. WordPress.org refused that upgrade,
 * and PHP builds the link with `from=` alone now.
 *
 * ## ⚠️ One URL per surface, and the key IS the `from` value
 *
 * The editor has one upsell surface, the Custom Events panel. It held a second
 * until 2026-10-26 — the schedule notice, retired with the Upsell select — and
 * two surfaces cannot be told apart in the conversion data unless they leave
 * with different URLs, so PHP builds one per surface and this reads them by
 * name.
 *
 * The key is the `from` value itself (`'editor-events'`), not a friendlier
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
