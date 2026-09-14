<?php

namespace GHOSTLABS\CYCLE_BLOCK\DTO\Row;

if ( ! defined( 'ABSPATH' ) ) exit;

class FrequencyRow extends Row
{
    public ?string $block_id = null;
    public ?string $entry_id = null;
    public ?int $post_id = null;
    public ?string $last_displayed = null;
}
