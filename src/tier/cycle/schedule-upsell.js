import { __, sprintf } from '@wordpress/i18n';
import { ExternalLink, Notice } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as preferencesStore } from '@wordpress/preferences';
import {
	ANNUALLY,
	QUARTERLY,
	BI_WEEKLY,
	HOURLY,
	EVERY_MINUTE,
	INSTANTLY,
} from '@shared/blocks/cycle/components/frequency/intervals';
import { buyProUrl } from '../upgrade-url';
import '../upsell.css';

/**
 * The free tier's answer to `@tier/cycle/schedule-upsell`: an Upsell notice under
 * the Cycle select naming the schedules Cycle Block Pro adds (owner, 2026-10-26).
 *
 * It points out and never refuses. The select beside it offers only this
 * build's three schedules, and nothing in it is disabled or marked: a disabled,
 * suffixed option reads as a locked feature to a WordPress.org reviewer
 * (Guideline 5), and the command center's *Locked features* gate refuses it.
 * Below the field and its help text, never above, so it does not displace the
 * control or read as a gate.
 *
 * ⚠️ **The dismissal persists per user**, in `core/preferences`, which WordPress
 * keeps in user meta for the block editor. `isDismissible` on its own hides the
 * notice for one render and wires the close button to nothing, and a notice that
 * comes back is the top named grievance with upsells.
 *
 * ⚠️ `spokenMessage=""` because this is standing copy, not a response to
 * anything the author did: the default speaks it into the live region every time
 * the block is selected.
 *
 * ⚠️ **Keep `PRO_SCHEDULES` in step with `TierMatrix`.** `ScheduleParityTest`
 * parses it and fails if it names a different set from the paid edition's.
 */
const SCOPE = 'ghostlabs/cycle-block';
const DISMISSED = 'cycleNoticeDismissed';

const PRO_SCHEDULES = [
	ANNUALLY,
	QUARTERLY,
	BI_WEEKLY,
	HOURLY,
	EVERY_MINUTE,
	INSTANTLY,
];

/**
 * "Annually, Quarterly, … and Instantly", joined the way the site's language
 * joins a list. The labels' leading "~" marks an approximate schedule in the
 * select and reads as noise in a sentence.
 */
const scheduleNames = () =>
	new Intl.ListFormat( document.documentElement.lang || 'en', {
		type: 'conjunction',
	} ).format(
		PRO_SCHEDULES.map( ( schedule ) =>
			schedule.label().replace( /^~\s*/, '' )
		)
	);

const ScheduleUpsell = () => {
	const dismissed = useSelect(
		( select ) => select( preferencesStore ).get( SCOPE, DISMISSED ),
		[]
	);
	const { set } = useDispatch( preferencesStore );

	if ( dismissed ) {
		return null;
	}

	const href = buyProUrl( 'editor-cycle-notice' );

	return (
		<Notice
			className="theghostlab-cycle-upsell-notice"
			status="info"
			spokenMessage=""
			onRemove={ () => set( SCOPE, DISMISSED, true ) }
		>
			<p>
				{ sprintf(
					/* translators: %s: a list of cycle schedule names, e.g. "Annually, Quarterly and Hourly". */
					__( 'Cycle Block Pro adds %s.', 'ghostlabs-cycle-block-lite' ),
					scheduleNames()
				) }
			</p>
			{ /* If PHP did not supply the URL, the copy stands on its own and no
			     link renders — see `../upgrade-url` for why there is no fallback. */ }
			{ href && (
				<p>
					<ExternalLink href={ href }>
						{ __( 'See Cycle Block Pro', 'ghostlabs-cycle-block-lite' ) }
					</ExternalLink>
				</p>
			) }
		</Notice>
	);
};

export default ScheduleUpsell;
