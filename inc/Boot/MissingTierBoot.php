<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Boot;

use GHOSTLABS\CYCLE_BLOCK\Contracts\GateInterface;
use GHOSTLABS\CYCLE_BLOCK\Contracts\TierBootInterface;
use GHOSTLABS\CYCLE_BLOCK\Gate\ClosedGate;
use GHOSTLABS\CYCLE_BLOCK\Services\Loader;

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

    public function allowedSchedules(): array
    {
        return [];
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
