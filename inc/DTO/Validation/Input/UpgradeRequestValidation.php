<?php

namespace GHOSTLABS\CYCLE_BLOCK\DTO\Validation\Input;

use GhostLabs\WPKit\Contracts\DTOValidationInterface;

if ( ! defined( 'ABSPATH' ) ) exit;

class UpgradeRequestValidation implements DTOValidationInterface
{
    /**
     * @return array<string, array<string, mixed>>
     */
    static public function rules(): array
    {
        return [
            'licenseKey' => ['required' => true, 'max' => 128],
        ];
    }
}
