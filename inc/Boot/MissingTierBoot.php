<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Boot;

use GHOSTLABS\CYCLE_BLOCK\Contracts\GateInterface;
use GHOSTLABS\CYCLE_BLOCK\Contracts\ScheduleSetInterface;
use GHOSTLABS\CYCLE_BLOCK\Contracts\TierBootInterface;
use GHOSTLABS\CYCLE_BLOCK\Gate\ClosedGate;
use GHOSTLABS\CYCLE_BLOCK\Services\Loader;
use GHOSTLABS\CYCLE_BLOCK\Services\NoSchedules;

if ( ! defined( 'ABSPATH' ) ) exit;

final class MissingTierBoot implements TierBootInterface
{
    public function gate(): GateInterface
    {
        return new ClosedGate();
    }

    public function boot(Loader $loader): void
    {
        
    }

    public function schemaStatements(string $tablePrefix, string $charsetCollate): array
    {
        return [];
    }

    public function schedules(): ScheduleSetInterface
    {
        return new NoSchedules();
    }

    public function restSegment(): string
    {
        return 'unassembled';
    }

    public function translationsDir(): ?string
    {
        return null;
    }

    public function legacyIndexes(): array
    {
        return [];
    }
}
