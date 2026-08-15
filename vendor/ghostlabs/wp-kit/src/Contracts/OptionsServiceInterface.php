<?php

namespace GhostLabs\WPKit\Contracts;

interface OptionsServiceInterface
{
    /**
     * Retrieve a stored plugin option by key.
     *
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key): mixed;
}
