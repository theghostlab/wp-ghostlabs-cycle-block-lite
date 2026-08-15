<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

interface CycleStrategy
{
    /**
     * The entry ids to hand out, in the order they should be shown.
     *
     * @return list<string>
     */
    public function returnEntries() : array;
}