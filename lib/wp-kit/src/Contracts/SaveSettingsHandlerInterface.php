<?php

namespace GhostLabs\WPKit\Contracts;

if ( ! defined( 'ABSPATH' ) ) exit;

interface SaveSettingsHandlerInterface
{
    
    public function save(string $data): ?array;
}
