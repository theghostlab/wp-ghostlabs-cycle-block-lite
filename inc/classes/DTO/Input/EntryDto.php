<?php

namespace THEGHOSTLAB\CYCLE\DTO\Input;

class EntryDto {
	public int $postId;
	public string $blockId;
	public string $currentId;
	public ?string $startingPosition;
}