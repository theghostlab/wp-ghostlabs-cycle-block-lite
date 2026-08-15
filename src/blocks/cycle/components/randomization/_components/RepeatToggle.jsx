import { __ } from '@wordpress/i18n';
import ShuffleToggle from './ShuffleToggle';
import RepeatIcon from './RepeatIcon';

/**
 * Whether an exhausted cycle starts over or freezes on its last variation.
 *
 * Shipped by every edition.
 *
 * Built on the same ShuffleToggle as the shuffle control, so it keeps that
 * component's accessibility contract: a stable accessible name with the state in
 * `aria-pressed` alone, rather than a label that flips between "Repeat" and
 * "Don't repeat" and makes screen readers announce contradictions.
 */
const RepeatToggle = ( { attributes, setAttributes } ) => (
	<ShuffleToggle
		label={ __( 'Repeat', 'ghostlabs-cycle-block-lite' ) }
		icon={ RepeatIcon }
		value={ attributes.repeat }
		callback={ ( value ) =>
			setAttributes( {
				previousSettings: {
					...attributes.previousSettings,
					repeat: attributes.repeat,
				},
				repeat: value,
			} )
		}
	/>
);

export default RepeatToggle;
