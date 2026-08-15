<?php

namespace GhostLabs\WPKit\Contracts;

interface MutableOptionsServiceInterface extends OptionsServiceInterface
{
    /**
     * Persist a plugin option by key.
     */
    public function save(string $key, mixed $data): bool;

    /**
     * Remove a plugin option by key. Returns null if the option store
     * does not exist, or the result of the underlying update call.
     */
    public function delete(string $key): ?bool;
}
