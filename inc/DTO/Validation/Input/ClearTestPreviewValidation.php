<?php

namespace GHOSTLABS\CYCLE_BLOCK\DTO\Validation\Input;

use GhostLabs\WPKit\Contracts\DTOValidationInterface;

if ( ! defined( 'ABSPATH' ) ) exit;

class ClearTestPreviewValidation implements DTOValidationInterface
{
    
    static public function rules(): array
    {
        return [
            'postId'  => ['required' => true, 'min' => 1],
            'blockId' => ['required' => true, 'max' => 64],
        ];
    }
}
