import {__, sprintf} from '@wordpress/i18n';

/**
 * The schedules the cycle understands, one named export each.
 *
 * `value` is the `{interval}-{frequency}` string the block's `update` attribute
 * stores. `label` is a thunk, not a string, because this module is evaluated at
 * import time and `__()` has to run at render time — the same reason the list
 * this replaced was built inside a function. Resolving the domain at import
 * would bake in whatever was registered before `wp.i18n` had the locale.
 *
 * Named exports rather than one catalogue object, deliberately: a tier imports
 * only the schedules it sells, so the ones it does not are unreferenced and
 * drop out of its bundle. A single exported map would keep every label reachable
 * and put "~ Instantly" in the free tier's JS — which is how this file was first
 * written, and the grep over the built bundle is what caught it. Tier separation
 * in this plugin is verified by grepping the built output, so "unreachable" is
 * not good enough; it has to be absent.
 *
 * Labels live here rather than in the tier modules so an edition chooses *which*
 * schedules it offers, never what they are called. Two editions naming the same
 * schedule differently is drift nobody notices until a support thread compares
 * screenshots.
 */
export const ANNUALLY = {
    value: '1-y',
    label: () => __("Annually", 'ghostlabs-cycle-block-lite')
};

export const QUARTERLY = {
    value: '3-m',
    label: () => __("Quarterly", 'ghostlabs-cycle-block-lite')
};

export const MONTHLY = {
    value: '1-m',
    label: () => __("Monthly", 'ghostlabs-cycle-block-lite')
};

export const BI_WEEKLY = {
    value: '14-d',
    label: () => __("Bi-Weekly", 'ghostlabs-cycle-block-lite')
};

export const WEEKLY = {
    value: '7-d',
    label: () => __("Weekly", 'ghostlabs-cycle-block-lite')
};

export const DAILY = {
    value: '1-d',
    label: () => __("Daily", 'ghostlabs-cycle-block-lite')
};

export const HOURLY = {
    value: '1-h',
    label: () => __("Hourly", 'ghostlabs-cycle-block-lite')
};

export const EVERY_MINUTE = {
    value: '1-i',
    label: () => __("~ Every Minute", 'ghostlabs-cycle-block-lite')
};

export const INSTANTLY = {
    value: '0-s',
    label: () => __("~ Instantly", 'ghostlabs-cycle-block-lite')
};

/**
 * Show a schedule this edition does not sell, named and unselectable.
 *
 * An edition lists the schedules it does not sell rather than hiding them:
 * showing what sits behind the wall converts better than leaving the reader to
 * guess it exists. They render disabled, so the control still says what the
 * product does without letting an edition pick a schedule it cannot honour.
 *
 * Composed with `sprintf` rather than a template literal inside `__()` — a
 * template literal is invisible to string extraction, so the suffixed label
 * would never translate.
 *
 * @param {{value: string, label: function(): string}} interval
 * @return {{value: string, label: function(): string, disabled: boolean}}
 */
export const proOnly = interval => ({
    ...interval,
    /* translators: %s: the name of a cycle schedule, e.g. "Annually". */
    label: () => sprintf(__('%s (Pro)', 'ghostlabs-cycle-block-lite'), interval.label()),
    disabled: true
});

/**
 * Turn an ordered list of schedules into `SelectControl` options.
 *
 * Order is the caller's: the tier module decides both membership and how the
 * list reads.
 *
 * `disabled` is only emitted when true. `SelectControl` forwards it straight to
 * the `<option>`, and an explicit `disabled={false}` on every other entry is
 * noise in the DOM for no gain.
 *
 * @param {{value: string, label: function(): string, disabled?: boolean}[]} intervals The schedules to offer.
 * @return {{label: string, value: string, disabled?: boolean}[]} Options for `SelectControl`.
 */
export const intervalOptions = intervals => intervals.map(({value, label, disabled}) => ({
    label: label(),
    value,
    ...(disabled ? {disabled: true} : {})
}));
