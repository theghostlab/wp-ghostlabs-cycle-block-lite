<?php

namespace GHOSTLABS\CYCLE_BLOCK\DTO\Validation\Input;

use GhostLabs\WPKit\Contracts\DTOValidationInterface;

class EntryChangeValidation implements DTOValidationInterface
{
    /**
     * @return array<string, array<string, mixed>>
     */
    static public function rules(): array
    {
        return [
            'postId'           => ['required' => true, 'min' => 1],
            'blockId'          => ['required' => true, 'max' => 64],
            'currentId'        => ['required' => true, 'max' => 64],
            'startingPosition' => ['required' => true, 'max' => 64],
        ];
    }
}
