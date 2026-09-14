import {__, sprintf} from '@wordpress/i18n';

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

const SECONDS = {
    y: 31536000,
    m: 2592000,
    d: 86400,
    h: 3600,
    i: 60,
    s: 1
};

const seconds = value => {
    const match = /^(\d+)-([a-z])$/.exec(value ?? '');

    return match && SECONDS[match[2]] !== undefined
        ? Number(match[1]) * SECONDS[match[2]]
        : null;
};

export const proSchedule = interval => ({
    ...interval,
    name: interval.label,
    /* translators: %s: the name of a cycle schedule, e.g. "Annually". */
    label: () => sprintf(__('%s (Pro-Only)', 'ghostlabs-cycle-block-lite'), interval.label()),
    pro: true
});

export const intervalOptions = (intervals, disabled = []) => intervals.map(({value, label}) => ({
    label: label(),
    value,
    ...(disabled.includes(value) ? {disabled: true} : {})
}));

export const isProSchedule = (value, intervals) =>
    Boolean(intervals.find(interval => interval.value === value)?.pro);

export const scheduleName = (value, intervals) => {
    const interval = intervals.find(entry => entry.value === value);

    return interval ? (interval.name ?? interval.label)() : value;
};

export const nearestHonoured = (value, intervals) => {
    const target = seconds(value);

    if (target === null) {
        return value;
    }

    let best = null;

    for (const interval of intervals) {
        if (interval.pro) {
            continue;
        }

        const candidate = seconds(interval.value);

        if (candidate === null) {
            continue;
        }

        const distance = Math.abs(candidate - target);

        if (
            best === null ||
            distance < best.distance ||
            (distance === best.distance && candidate > best.seconds)
        ) {
            best = {value: interval.value, distance, seconds: candidate};
        }
    }

    return best ? best.value : value;
};
