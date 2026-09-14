<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

if ( ! defined( 'ABSPATH' ) ) exit;

interface FrontEndServiceInterface
{
    
    public function play();

    public function run();
}