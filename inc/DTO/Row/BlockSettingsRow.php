<?php

namespace GHOSTLABS\CYCLE_BLOCK\DTO\Row;

/**
 * One row of the settings table: the per-block schedule configuration written on
 * save_post (BlockSettings) and read back for interval generation
 * (EntryService::getBlockSetting → IntervalFrequency::intervals).
 *
 * update_interval stays a string — the column is varchar(12) and MySQL returns
 * strings; sprintf('%d') consumes it either way.
 */
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
