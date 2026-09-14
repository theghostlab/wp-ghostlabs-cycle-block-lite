import { __ } from '@wordpress/i18n';
import { PanelBody, ExternalLink, Notice } from '@wordpress/components';
import { buyProUrl } from '../upgrade-url';
import { upgradeLabel } from '../upgrade-label';
import '../upsell.css';

const CustomEventsPanel = () => {
	const href = buyProUrl( 'editor-events' );

	return (
		<PanelBody
			title={ __( 'Custom Events', 'ghostlabs-cycle-block-lite' ) }
			initialOpen={ true }
		>
			<p>
				{ __(
					'See which variation actually performs. Pro sends a view and a click event per variation to every analytics tool on your page at once — Google Analytics, Matomo, Plausible, Umami — and keeps a rolling 90-day log you can read in settings.',
					'ghostlabs-cycle-block-lite'
				) }
			</p>
			{ }
			<Notice
				className="theghostlab-cycle-upsell-notice"
				status="info"
				spokenMessage=""
				isDismissible={ false }
			>
				<p>
					<strong>
						{ __(
							'Upgrading changes nothing else.',
							'ghostlabs-cycle-block-lite'
						) }
					</strong>{ ' ' }
					{ __(
						'Every block, schedule and setting stays exactly as it is.',
						'ghostlabs-cycle-block-lite'
					) }
				</p>
				{ }
				{ href && (
					<p>
						{ }
						<ExternalLink href={ href }>
							{ upgradeLabel() }
						</ExternalLink>
					</p>
				) }
			</Notice>
		</PanelBody>
	);
};

export default CustomEventsPanel;
