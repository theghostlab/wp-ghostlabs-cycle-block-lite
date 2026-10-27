<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use GHOSTLABS\CYCLE_BLOCK\Contracts\ScheduleSetInterface;

if ( ! defined( 'ABSPATH' ) ) exit;

final class NoSchedules implements ScheduleSetInterface
{
    public function duration(string $schedule): ?string
    {
        return null;
    }
}
