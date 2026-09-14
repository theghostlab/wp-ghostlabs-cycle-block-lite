<?php

namespace GHOSTLABS\CYCLE_BLOCK\DTO\Row;

if ( ! defined( 'ABSPATH' ) ) exit;

class QueueRow extends Row
{
    public ?int $post_id = null;
    public ?string $block_id = null;
    public ?string $queue = null;
    public ?string $updated_on = null;
}
