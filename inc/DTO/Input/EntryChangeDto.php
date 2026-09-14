<?php

namespace GHOSTLABS\CYCLE_BLOCK\DTO\Input;

if ( ! defined( 'ABSPATH' ) ) exit;

class EntryChangeDto
{
    public ?int $postId = null;
    public ?string $blockId = null;
    public ?string $currentId = null;
    public ?string $startingPosition = null;
}
