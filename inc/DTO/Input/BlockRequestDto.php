<?php

namespace GHOSTLABS\CYCLE_BLOCK\DTO\Input;

/**
 * The admin-ajax payload shape shared by updateBlock and clearTestPreview.
 *
 * Null defaults are load-bearing: wp-shared's DTOMapper::validate() reads every
 * property directly, so an uninitialized typed property would throw Error on an
 * incomplete payload instead of producing a validation response.
 */
class BlockRequestDto
{
    public ?int $postId = null;
    public ?string $blockId = null;
    public ?string $entryId = null;
}
