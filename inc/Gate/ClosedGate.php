<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Gate;

use GHOSTLABS\CYCLE_BLOCK\Contracts\GateInterface;

if ( ! defined( 'ABSPATH' ) ) exit;

final class ClosedGate implements GateInterface
{
    public function allows(): bool
    {
        return false;
    }
}
