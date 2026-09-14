<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services;

if ( ! defined( 'ABSPATH' ) ) exit;

final class TierMatrix
{
    
    public const SCHEDULES = ['1-y', '3-m', '1-m', '14-d', '7-d', '1-d', '1-h', '1-i', '0-s'];

    public const TRACKER_NAMES = ['Google Analytics 4', 'Matomo', 'Plausible', 'Umami'];

    public const FREE_SCHEDULES = ['1-d', '7-d', '1-m'];

    public static function scheduleLabels(): array
    {
        return [
            '1-y'  => __('Annually', 'ghostlabs-cycle-block-lite'),
            '3-m'  => __('Quarterly', 'ghostlabs-cycle-block-lite'),
            '1-m'  => __('Monthly', 'ghostlabs-cycle-block-lite'),
            '14-d' => __('Bi-Weekly', 'ghostlabs-cycle-block-lite'),
            '7-d'  => __('Weekly', 'ghostlabs-cycle-block-lite'),
            '1-d'  => __('Daily', 'ghostlabs-cycle-block-lite'),
            '1-h'  => __('Hourly', 'ghostlabs-cycle-block-lite'),
            '1-i'  => __('~ Every Minute', 'ghostlabs-cycle-block-lite'),
            '0-s'  => __('~ Instantly', 'ghostlabs-cycle-block-lite'),
        ];
    }

    public static function freeScheduleNames(): array
    {
        $labels = self::scheduleLabels();

        return array_map(
            static fn(string $schedule): string => $labels[$schedule] ?? $schedule,
            self::FREE_SCHEDULES
        );
    }

    public static function capabilities(): array
    {
        return [
            [
                'key'   => 'intervals',
                'label' => __('Cycle intervals', 'ghostlabs-cycle-block-lite'),

                'free'  => implode(', ', self::freeScheduleNames()),
                /* translators: %d: the total number of cycle schedules, e.g. 9. */
                'pro'   => sprintf(__('All %d', 'ghostlabs-cycle-block-lite'), count(self::SCHEDULES)),
            ],
            [
                'key'   => 'order',
                'label' => __('Shuffle, sequence and manual order', 'ghostlabs-cycle-block-lite'),
                'free'  => true,
                'pro'   => true,
            ],
            [
                'key'   => 'repeat-preview',
                'label' => __('Repeat and editor preview', 'ghostlabs-cycle-block-lite'),
                'free'  => true,
                'pro'   => true,
            ],
            [
                'key'   => 'custom-events',

                'label' => __(
                    'Custom Events — a view and an optional click event for every event you define',
                    'ghostlabs-cycle-block-lite'
                ),
                'free'  => false,
                'pro'   => true,
            ],
            [

                'key'   => 'analytics-destinations',
                'label' => __('Analytics your events reach', 'ghostlabs-cycle-block-lite'),
                'free'  => false,
                'pro'   => implode(', ', self::TRACKER_NAMES),
            ],
            [

                'key'   => 'analytics-seam',
                'label' => __('Your tag manager, or a tracker of your own', 'ghostlabs-cycle-block-lite'),
                'free'  => false,
                'pro'   => true,
            ],
            [
                'key'   => 'custom-events-logs',
                'label' => __('Custom Events Logs, readable in settings', 'ghostlabs-cycle-block-lite'),
                'free'  => false,
                'pro'   => true,
            ],
            [
                'key'   => 'event-parameters',
                'label' => __('Your own event parameters', 'ghostlabs-cycle-block-lite'),
                'free'  => false,

                'pro'   => __('Everywhere except Matomo', 'ghostlabs-cycle-block-lite'),
            ],
            [

                'key'   => 'support',
                'label' => __('Dedicated support', 'ghostlabs-cycle-block-lite'),
                'free'  => false,
                'pro'   => true,
            ],
        ];
    }

    public static function proOnlyLabels(): array
    {
        return array_values(array_map(
            static fn(array $capability): string => $capability['label'],
            array_filter(
                self::capabilities(),
                static fn(array $capability): bool => $capability['free'] === false
            )
        ));
    }

    public static function proOnlyScheduleNames(): array
    {
        $labels = self::scheduleLabels();

        return array_map(
            static fn(string $schedule): string => $labels[$schedule] ?? $schedule,
            self::proOnlySchedules()
        );
    }

    public static function proOnlySchedules(): array
    {
        return array_values(array_diff(self::SCHEDULES, self::FREE_SCHEDULES));
    }
}
