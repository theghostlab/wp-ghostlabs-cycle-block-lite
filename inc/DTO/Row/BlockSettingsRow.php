<?php

namespace GHOSTLABS\CYCLE_BLOCK\DTO\Row;

if ( ! defined( 'ABSPATH' ) ) exit;

class BlockSettingsRow extends Row
{
    public ?int $id = null;
    public ?int $post_id = null;
    public ?string $block_id = null;
    public ?string $current_id = null;
    public ?string $update_on = null;
    public ?string $update_interval = null;
    public ?string $update_frequency = null;
    public ?string $randomize_setting = null;
}
