<?php

namespace GHOSTLABS\CYCLE_BLOCK\DTO\Row;

/**
 * One row of the frequency table: which entry a block last showed, and when.
 *
 * Callers set only the fields they mean to write; IntervalFrequency::addFrequency()
 * fills the identity fields from the block's attributes and toRow() drops the rest,
 * so a partial update never nulls a column it did not name.
 */
class FrequencyRow extends Row
{
    public ?string $block_id = null;
    public ?string $entry_id = null;
    public ?int $post_id = null;
    public ?string $last_displayed = null;
}
