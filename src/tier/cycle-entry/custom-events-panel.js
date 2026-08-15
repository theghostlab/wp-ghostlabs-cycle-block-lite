import { __ } from '@wordpress/i18n';
import { PanelBody, ExternalLink } from '@wordpress/components';

/**
 * The free tier's answer to `@tier/cycle-entry/custom-events-panel`.
 *
 * Shows what Custom Events does and where to get it, rather than hiding the
 * feature entirely — an author who can see what sits behind the wall converts
 * better than one who never learns it exists.
 *
 * Deliberately not a disabled copy of the real UI. There is no form here to
 * disable: a disabled control that looks operable invites clicks that do
 * nothing, and `aria-disabled` widgets still take focus and still announce
 * themselves as controls. This is plain, focusable, readable text and one real
 * link — which is also why it needs no `@wordpress/components` form primitives
 * and adds nothing to the free bundle beyond the panel itself.
 *
 * Closed by default: it is an advertisement in the sidebar of someone's editor,
 * and it should not push the controls they came for below the fold.
 */
const CustomEventsPanel = () => (
	<PanelBody
		title={ __( 'Custom Events', 'ghostlabs-cycle-block-lite' ) }
		initialOpen={ false }
	>
		<p>
			{ __(
				'Track how each variation performs. Custom Events sends a view and click event per variation to Google Analytics, with your own parameters, and keeps an internal log you can read in the plugin settings.',
				'ghostlabs-cycle-block-lite'
			) }
		</p>
		<p>
			<ExternalLink href="https://theghostlab.io/cycle-block-pro">
				{ __( 'Available in Cycle Block Pro', 'ghostlabs-cycle-block-lite' ) }
			</ExternalLink>
		</p>
	</PanelBody>
);

export default CustomEventsPanel;
