<?php

namespace GHOSTLABS\CYCLE_BLOCK\DTO\Input;

/**
 * The updateOnEntryChange admin-ajax payload: the editor changed a block's
 * starting position. Null defaults are load-bearing — see BlockRequestDto.
 */
class EntryChangeDto
{
    public ?int $postId = null;
    public ?string $blockId = null;
    public ?string $currentId = null;
    public ?string $startingPosition = null;
}
