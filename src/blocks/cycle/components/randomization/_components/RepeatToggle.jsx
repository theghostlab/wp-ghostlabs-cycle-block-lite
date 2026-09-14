import { __ } from '@wordpress/i18n';
import ShuffleToggle from './ShuffleToggle';
import RepeatIcon from './RepeatIcon';

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
