import { __, sprintf } from '@wordpress/i18n';
import { ExternalLink, Notice } from '@wordpress/components';
import { buyProUrl } from '../upgrade-url';
import { upgradeLabel } from '../upgrade-label';
import tierIntervals from './frequency-options';
import '../upsell.css';

const honouredNames = () =>
	tierIntervals
		.filter( ( interval ) => ! interval.pro )
		.map( ( interval ) => ( interval.name ?? interval.label )() )
		.reverse()
		.join( ', ' );

const ScheduleNotice = ( { picked, running, isStored, onDismiss } ) => {
	const href = buyProUrl( 'editor-schedule' );

	/* translators: %s: the name of a cycle schedule, e.g. "Hourly". */
	const headline = sprintf(
		__( '%s is a Pro schedule.', 'ghostlabs-cycle-block-lite' ),
		picked
	);

	const range = sprintf(
		/* translators: %s: a list of cycle schedule names, e.g. "Monthly, Weekly, Daily". */
		__( 'Free cycles %s.', 'ghostlabs-cycle-block-lite' ),
		honouredNames()
	);

	const outcome = isStored
		? sprintf(
				/* translators: %s: the name of a cycle schedule, e.g. "Weekly". */
				__(
					'This block keeps that setting, and cycles %s here.',
					'ghostlabs-cycle-block-lite'
				),
				running
		  )
		: sprintf(
				/* translators: %s: the name of a cycle schedule, e.g. "Weekly". */
				__(
					'Your cycle is still %s — nothing changed.',
					'ghostlabs-cycle-block-lite'
				),
				running
		  );

	return (
		<Notice
			className="theghostlab-cycle-upsell-notice"
			status="info"
			politeness="polite"
			spokenMessage={ `${ headline } ${ range } ${ outcome }` }
			onRemove={ onDismiss }
		>
			<p>
				<strong>{ headline }</strong>
			</p>
			<p>{ `${ range } ${ outcome }` }</p>
			{ }
			{ href && (
				<p>
					<ExternalLink href={ href }>
						{ upgradeLabel() }
					</ExternalLink>
				</p>
			) }
		</Notice>
	);
};

export default ScheduleNotice;
