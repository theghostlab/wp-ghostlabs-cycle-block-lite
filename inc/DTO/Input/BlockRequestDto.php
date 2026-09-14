<?php

namespace GHOSTLABS\CYCLE_BLOCK\DTO\Input;

if ( ! defined( 'ABSPATH' ) ) exit;

class BlockRequestDto
{
    public ?int $postId = null;
    public ?string $blockId = null;
    public ?string $entryId = null;
}
