<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Contracts;

if ( ! defined( 'ABSPATH' ) ) exit;

interface DTOValidationInterface
{
    
    public static function rules(): array;
}
