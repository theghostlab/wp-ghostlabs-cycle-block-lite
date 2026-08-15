<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Gate;

use GHOSTLABS\CYCLE_BLOCK\Contracts\GateInterface;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Denies everything. The answer to "I cannot tell what this edition may do".
 *
 * Only reached when the build is wrong — no `Boot\{Edition}Boot` class for the
 * edition constant the entry file defined. Failing closed is the safe direction:
 * an open gate there would run an edition's full feature set with nothing
 * verifying it was entitled to.
 */
final class ClosedGate implements GateInterface
{
    public function allows(): bool
    {
        return false;
    }
}
