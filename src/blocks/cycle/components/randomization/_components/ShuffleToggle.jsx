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
