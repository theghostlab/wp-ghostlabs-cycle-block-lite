import { __ } from '@wordpress/i18n';
import { inPlaceAvailable } from './upgrade-availability';

/**
 * The label on the editor's two checkout links.
 *
 * ⚠️ **Resolved at call time, never hoisted to a module constant.** `__()` has to
 * run at render or it bakes in whichever locale registered first — the trap
 * `intervals.js`, `TierMatrix` and `UpgradePanel` all carry.
 *
 * ⚠️ **"Installs in place" is a promise, and it is the last thing read before a
 * purchase.** Where the swap cannot run — any multisite network, or any editor
 * whose user cannot install plugins — the link still goes to checkout, because
 * nothing about a network stops somebody buying Cycle Block Pro. What changes is
 * that it stops promising a mechanism this build will then refuse.
 *
 * @return {string} The link text.
 */
export const upgradeLabel = () =>
	inPlaceAvailable()
		? __( 'Upgrade — installs in place', 'ghostlabs-cycle-block-lite' )
		: __( 'Upgrade to Cycle Block Pro', 'ghostlabs-cycle-block-lite' );

export default upgradeLabel;
