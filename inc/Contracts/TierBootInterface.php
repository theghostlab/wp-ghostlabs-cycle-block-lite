<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Contracts;

use GHOSTLABS\CYCLE_BLOCK\Services\Loader;

if ( ! defined( 'ABSPATH' ) ) exit;

interface TierBootInterface
{
    
    public function gate(): GateInterface;

    public function boot(Loader $loader): void;

    public function schemaStatements(string $tablePrefix, string $charsetCollate): array;

    public function allowedSchedules(): array;

    public function translationsDir(): ?string;

    public function legacyIndexes(): array;
}
