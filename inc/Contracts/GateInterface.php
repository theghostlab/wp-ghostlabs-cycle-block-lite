<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Contracts;

if ( ! defined( 'ABSPATH' ) ) exit;

interface GateInterface
{
    public function allows(): bool;
}
