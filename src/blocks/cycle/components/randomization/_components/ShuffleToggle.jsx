import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

const ShuffleToggle = ({callback, icon, iconOn, iconOff, label, value}) => {
    const [isShuffleOn, setShuffleOn] = useState(value);

    const handleToggle = () => {
        const newValue = !isShuffleOn;
        setShuffleOn(newValue);
        callback(newValue);
    };

    return (
        <button
            className={`components-button is-secondary theghostlab-toggle`}
            aria-pressed={isShuffleOn}
            onClick={handleToggle}
        >
            <span className="visually-hidden">{isShuffleOn ? __(`${label} is on`,'theghostlab') : __(`${label} is off`,'theghostlab')}</span>
            {isShuffleOn ? (
                icon && <i className={'theghostlab-toggle-on'} title={__(`${label} is on`,'theghostlab')}>{icon()}</i>
            ) : (
                icon && <i className={'theghostlab-toggle-off'} title={__(`${label} is off`,'theghostlab')}>{icon()}</i>
            )}
        </button>
    );
};


export default ShuffleToggle;