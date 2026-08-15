<?php

namespace GHOSTLABS\CYCLE_BLOCK\DTO\Row;

/**
 * One row of the queue table. `queue` is the serialized SplQueue of entry ids
 * still to be shown — serialization stays the caller's business so this shape
 * never guesses at what a queue column holds.
 */
class QueueRow extends Row
{
    public ?int $post_id = null;
    public ?string $block_id = null;
    public ?string $queue = null;
    public ?string $updated_on = null;
}
