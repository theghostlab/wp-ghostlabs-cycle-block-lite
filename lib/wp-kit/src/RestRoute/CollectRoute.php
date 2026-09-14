<?php

namespace GhostLabs\WPKit\RestRoute;

use Attribute;

if ( ! defined( 'ABSPATH' ) ) exit;

#[Attribute]
class CollectRoute
{
    public function __construct(public string $group = 'ghostlabs-routes')
    {
    }
}
