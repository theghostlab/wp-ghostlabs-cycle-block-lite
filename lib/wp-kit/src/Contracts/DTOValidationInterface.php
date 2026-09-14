<?php

namespace GhostLabs\WPKit\Contracts;

if ( ! defined( 'ABSPATH' ) ) exit;

interface DTOValidationInterface
{
    
    static public function rules(): array;
}
