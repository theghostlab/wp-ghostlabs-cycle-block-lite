<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

interface FrontEndServiceInterface
{
    /**
     * Decide which entry is on screen now, advancing the schedule if it is due.
     *
     * @return string|null The chosen entry id, or null if the block has no usable schedule.
     */
    public function play();

    /**
     * @return string|null
     */
    public function run();
}