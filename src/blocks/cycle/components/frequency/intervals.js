import {__} from '@wordpress/i18n';

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

export const intervalOptions = intervals => intervals.map(({value, label}) => ({
    label: label(),
    value
}));
