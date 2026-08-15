<?php

namespace GhostLabs\WPKit\Contracts;

interface SaveSettingsHandlerInterface
{
    /**
     * @return array<string, mixed>|null
     */
    public function save(string $data): ?array;
}
