<?php

namespace GHOSTLABS\CYCLE_BLOCK\DTO\Row;

use GhostLabs\WPKit\Service\DTOMapper;

if ( ! defined( 'ABSPATH' ) ) exit;

abstract class Row
{
    
    public function toRow(): array
    {
        return array_filter(DTOMapper::toArray($this), static fn($value) => $value !== null);
    }
}
