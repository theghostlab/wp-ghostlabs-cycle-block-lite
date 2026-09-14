/**
 * Whether the in-place upgrade can actually run on this site, for this user.
 *
 * Decided in PHP (`Services\Upgrade\UpgradeSupport`) and inlined through
 * shared's `theghostlab_cycle_editor_vars` filter, for the same reason the
 * checkout URL is: the question has no client-side answer. `is_multisite()` has
 * no JS equivalent, and neither does `current_user_can( 'install_plugins' )`.
 *
 * ⚠️ **This gates a CLAIM, not a link.** The editor's two upsell surfaces used
 * to label their link "Upgrade — installs in place" unconditionally. On a
 * multisite network that is false, and it is false in the most expensive
 * possible place: it is the sentence that persuades someone to click through to
 * checkout. They paid for a one-click swap that this build refuses to perform.
 *
 * ⚠️ **Absent means available.** The only way the var goes missing is a stale
 * bundle meeting a newer PHP or the reverse, and single-site installs — where
 * the claim is true — are the overwhelming majority. A missing key must not
 * strip the in-place promise from the sites that actually have it.
 *
 * Read off `window` rather than as a bare global: the var is assigned as a
 * window property, and a script-scoped `const` would satisfy a bare read while
 * leaving `window.…` undefined — the trap that silently disabled Pro's
 * withdrawn-licence check for as long as it existed.
 *
 * @return {boolean} Whether "installs in place" is true here.
 */
export const inPlaceAvailable = () =>
	window.theghostlab_cycle_vars?.upgrade?.inPlace?.available !== false;

export default inPlaceAvailable;
