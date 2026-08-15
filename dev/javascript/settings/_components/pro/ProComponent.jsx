import {
	Panel,
	PanelContainer,
	PanelHeader,
	PanelTitle,
} from '../ui';

// The GLOBAL wp.i18n, never an import — esbuild bundles for real, and a bundled
// @wordpress/i18n keeps its own empty locale registry that
// wp_set_script_translations() can never reach.
const { __ } = window.wp.i18n;

const FEATURES = [
	{
		title: __( 'Custom Events', 'ghostlabs-cycle-block-lite' ),
		body: __(
			'A view and a click event per variation, sent to Google Analytics with your own parameters, so you can see which variation actually performs.',
			'ghostlabs-cycle-block-lite'
		),
	},
	{
		title: __( 'Custom Events Logs', 'ghostlabs-cycle-block-lite' ),
		body: __(
			'An internal log of every event, grouped by post and variation, readable right here in the settings — no analytics account required to check that tracking is working.',
			'ghostlabs-cycle-block-lite'
		),
	},
	{
		title: __( 'Custom event parameters', 'ghostlabs-cycle-block-lite' ),
		body: __(
			'Attach your own keys and values to every event, so the data arriving in your analytics matches how you already name campaigns and placements.',
			'ghostlabs-cycle-block-lite'
		),
	},
	{
		title: __( 'Priority support', 'ghostlabs-cycle-block-lite' ),
		body: __(
			'Direct support from the people who build the plugin, rather than the community forum.',
			'ghostlabs-cycle-block-lite'
		),
	},
];

/**
 * The free edition's only settings tab.
 *
 * There is nothing to configure — the free tier stores no options — so this is
 * the upgrade path, which per the portfolio strategy is the only revenue
 * mechanism a free install has.
 *
 * Deliberately plain: a heading, a list, and one real link. No countdown, no
 * dismissible banner, no admin notice anywhere else in wp-admin. On a listing
 * with zero ratings the first few reviews decide everything, and "nags you"
 * is the cheapest one-star there is.
 */
const ProComponent = () => (
	<PanelContainer>
		<PanelHeader>
			{ /*
			  * PanelTitle, not `<Panel title=…>`. Panel is a plain
			  * div and forwards unknown props as HTML attributes, so a `title`
			  * there renders a hover tooltip and no heading at all — the tab
			  * would have had no heading in its accessibility tree.
			  */ }
			<PanelTitle>
				{ __( 'Cycle Block Pro', 'ghostlabs-cycle-block-lite' ) }
			</PanelTitle>
		</PanelHeader>
		<Panel>
		<p className="mb-4">
			{ __(
				'Everything in the free edition — every interval, shuffle, sequence, repeat and editor preview — plus per-variation analytics.',
				'ghostlabs-cycle-block-lite'
			) }
		</p>

		<dl className="mb-6">
			{ FEATURES.map( ( { title, body } ) => (
				<div key={ title } className="mb-4">
					<dt className="font-semibold">{ title }</dt>
					<dd className="m-0">{ body }</dd>
				</div>
			) ) }
		</dl>

		<a
			className="button button-primary"
			href="https://theghostlab.io/cycle-block-pro"
			target="_blank"
			rel="noopener noreferrer"
		>
			{ /* The "(opens in a new tab)" is part of the accessible name on
			     purpose: target=_blank without it is a WCAG 3.2.5 failure, and
			     the logs tab's external links already follow this pattern. */ }
			{ __( 'See Cycle Block Pro (opens in a new tab)', 'ghostlabs-cycle-block-lite' ) }
		</a>
		</Panel>
	</PanelContainer>
);

export default ProComponent;
