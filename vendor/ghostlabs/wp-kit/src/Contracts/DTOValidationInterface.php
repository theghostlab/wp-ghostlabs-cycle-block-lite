<?php

namespace GhostLabs\WPKit\Contracts;

interface DTOValidationInterface
{
    /**
     * @return array<string, array<string, mixed>>
     */
    static public function rules(): array;
}
