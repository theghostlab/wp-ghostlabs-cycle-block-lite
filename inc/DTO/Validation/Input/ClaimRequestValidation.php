<?php

namespace GHOSTLABS\CYCLE_BLOCK\DTO\Validation\Input;

use GhostLabs\WPKit\Contracts\DTOValidationInterface;

if ( ! defined( 'ABSPATH' ) ) exit;

class ClaimRequestValidation implements DTOValidationInterface
{
    /**
     * @return array<string, array<string, mixed>>
     */
    static public function rules(): array
    {
        return [
            'claimToken' => ['required' => true, 'max' => 128],
        ];
    }
}
