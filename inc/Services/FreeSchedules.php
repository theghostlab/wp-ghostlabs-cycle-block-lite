<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use GHOSTLABS\CYCLE_BLOCK\Contracts\ScheduleSetInterface;

if ( ! defined( 'ABSPATH' ) ) exit;

final class FreeSchedules implements ScheduleSetInterface
{
    
    public const SCHEDULES = ['1-m', '7-d', '1-d'];

    private const DURATIONS = [
        '1-m' => 'P1M',
        '7-d' => 'P7D',
        '1-d' => 'P1D',
    ];

    public function duration(string $schedule): ?string
    {
        return self::DURATIONS[$schedule] ?? null;
    }
}
