// The GLOBAL wp.i18n, never an import — esbuild bundles for real, and a bundled
// @wordpress/i18n keeps its own empty locale registry that
// wp_set_script_translations() can never reach.
const { __ } = window.wp.i18n;

const CLAIM_ARG = 'cycle_upgrade_claim';

/**
 * What this screen shows where the in-place upgrade cannot run.
 *
 * ⚠️ **A separate component, mounted INSTEAD of `UpgradePanel` — not a branch
 * inside it.** The defect this closes was not that the panel said the wrong
 * thing; it was that the panel existed at all on a multisite network. It offered
 * a licence key field, promised "this site switches to Pro in place", accepted a
 * key, posted it, and only then refused — routinely to somebody who had just
 * paid. Swapping the component out means there is no field to fill, no form to
 * submit and no state machine mounted behind it: the guarantee is structural
 * rather than a condition somebody could later invert.
 *
 * ⚠️ **The sentence is PHP's, not this file's.** `Services\Upgrade\UpgradeSupport`
 * decides which of three refusals a caller gets — a super admin is told the route
 * to take, a subsite administrator is told who to ask, a restricted single site is
 * told what is turned off — and the REST gate answers the *same* string. A second
 * copy here is how the screen and the endpoint would start disagreeing again.
 *
 * ⚠️ **No link.** The account page has no URL in this build, and the house rule
 * is that a guessed destination is worse than none: it resolves, it looks right,
 * and it loses the buyer. See `upgrade-url.js`.
 */
const UpgradeUnavailablePanel = ( { inPlace } ) => {
	/*
	 * Someone arriving with a claim token has just come back from checkout, so
	 * the first thing on screen must not be "already own Pro?" — they know. The
	 * purchase is acknowledged and the refusal follows it, in that order.
	 *
	 * Read at render, not at mount: this component holds no state and never
	 * re-renders on its own.
	 */
	const returningFromCheckout = Boolean(
		new URLSearchParams( window.location.search ).get( CLAIM_ARG )
	);

	const heading = returningFromCheckout
		? __( 'Your purchase is complete', 'ghostlabs-cycle-block-lite' )
		: __( 'Already own Cycle Block Pro?', 'ghostlabs-cycle-block-lite' );

	return (
		<div className="rounded-[10px] border border-gray-200 bg-white px-7 py-[18px]">
			<h2 className="text-sm font-semibold text-gray-900">{ heading }</h2>
			{ /*
			  * ⚠️ Not `role="status"`. This is present on first paint rather than
			  * announced in response to anything, and a live region that fires on
			  * load talks over the page being read. It is ordinary prose, in the
			  * reading order, immediately under the heading it belongs to.
			  */ }
			<p className="mt-0.5 text-sm text-gray-500">{ inPlace.message }</p>
		</div>
	);
};

export default UpgradeUnavailablePanel;
