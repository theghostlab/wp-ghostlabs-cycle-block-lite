<?php

namespace GhostLabs\WPKit\Contracts;

if ( ! defined( 'ABSPATH' ) ) exit;

interface RestRouteInterface
{
    public function routes(): void;
}
