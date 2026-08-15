<?php

namespace GHOSTLABS\CYCLE_BLOCK\DTO\Validation\Input;

use GhostLabs\WPKit\Contracts\DTOValidationInterface;

/**
 * Ids are validated for presence and sanity, not strict ULID shape — blockIds
 * live in post content, and content written by older builds must keep working.
 */
class UpdateBlockValidation implements DTOValidationInterface
{
    /**
     * @return array<string, array<string, mixed>>
     */
    static public function rules(): array
    {
        return [
            'postId'  => ['required' => true, 'min' => 1],
            'blockId' => ['required' => true, 'max' => 64],
            'entryId' => ['required' => true, 'max' => 64],
        ];
    }
}
