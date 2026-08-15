<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Gate;

use GHOSTLABS\CYCLE_BLOCK\Contracts\GateInterface;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Allows everything.
 *
 * The gate for an edition with nothing to verify: no check to make and nothing
 * to fail. This is a null object rather than a missing one on purpose:
 * `Kernel::run()` always has a gate to ask, so it never needs to know which
 * edition it is running as.
 */
final class OpenGate implements GateInterface
{
    public function allows(): bool
    {
        return true;
    }
}
