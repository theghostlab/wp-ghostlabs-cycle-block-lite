<?php

namespace GhostLabs\WPKit\Contracts;

if ( ! defined( 'ABSPATH' ) ) exit;

interface OptionsServiceInterface
{
    
    public function get(string $key): mixed;
}
