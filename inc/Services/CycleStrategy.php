<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

if ( ! defined( 'ABSPATH' ) ) exit;

interface CycleStrategy
{
    
    public function returnEntries() : array;
}