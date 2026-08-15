/**
 * A pressed-state toggle button. The accessible name stays stable ("Shuffle",
 * "Repeat") and the state lives in aria-pressed alone — a name that flips with
 * the state ("Shuffle is on") makes AT read contradictions like "Shuffle is
 * off, pressed". Controlled by the parent: the value prop is the state, so an
 * attribute change from outside can never desync a local copy.
 */
const ShuffleToggle = ({callback, icon, label, value}) => (
    <button
        type="button"
        className={`components-button is-secondary theghostlab-toggle`}
        aria-pressed={value}
        onClick={() => callback(!value)}
    >
        <span className="visually-hidden">{label}</span>
        {icon && (
            <i
                className={value ? 'theghostlab-toggle-on' : 'theghostlab-toggle-off'}
                aria-hidden="true"
            >{icon()}</i>
        )}
    </button>
);

export default ShuffleToggle;
