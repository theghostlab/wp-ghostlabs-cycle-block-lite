<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Boot;

use GHOSTLABS\CYCLE_BLOCK\Contracts\GateInterface;
use GHOSTLABS\CYCLE_BLOCK\Contracts\TierBootInterface;
use GHOSTLABS\CYCLE_BLOCK\Gate\ClosedGate;
use GHOSTLABS\CYCLE_BLOCK\Services\Loader;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Stands in when the edition has no `Boot\{Edition}Boot` class.
 *
 * That only happens if a build was assembled wrong — the entry header defined an
 * edition whose tier folder was not overlaid. Rather than fatal on a missing
 * class, the plugin registers nothing and denies the gate, so the site keeps
 * serving pages and the cycle blocks fall back to their own saved content
 * through the render callback's defensive path.
 *
 * Deliberately *not* an open gate: that would be the one failure mode where a
 * broken build runs an edition's full feature set with nothing verifying it was
 * entitled to.
 */
final class MissingTierBoot implements TierBootInterface
{
    public function gate(): GateInterface
    {
        return new ClosedGate();
    }

    public function boot(Loader $loader): void
    {
        // Nothing. There is no edition to wire.
    }

    /**
     * No tables either — a build that could not identify its own edition has no
     * business creating schema for one.
     *
     * @return list<string>
     */
    public function schemaStatements(string $tablePrefix, string $charsetCollate): array
    {
        return [];
    }

    /**
     * None. A build that could not identify its own edition must not touch schema.
     *
     * @return array<string, string>
     */
    public function legacyIndexes(): array
    {
        return [];
    }
}
