<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Contracts;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * May this edition's gated hooks run?
 *
 * `Kernel::run()` asks exactly this before handing the queued hooks to
 * `Loader::run()`. An edition that has something to verify answers with its
 * verdict; one that has nothing to verify answers yes unconditionally.
 *
 * It exists so Kernel never has to name a class that only one edition ships.
 * Classes an edition does not ship are *absent*, not stubbed, so a direct call
 * would be a fatal — and guarding every call site with `class_exists()` is what
 * this interface replaces.
 */
interface GateInterface
{
    public function allows(): bool;
}
