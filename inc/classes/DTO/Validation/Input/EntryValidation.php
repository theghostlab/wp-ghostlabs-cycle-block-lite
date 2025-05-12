<?php

namespace THEGHOSTLAB\CYCLE\DTO\Validation\Input;

use THEGHOSTLAB\CYCLE\DTO\Validation\DTOValidationInterface;

class EntryValidation implements DTOValidationInterface {

	static public function rules(): array {
		return [
			'postId' => ['required' => true, 'min' => 1],
			'blockId' => ['required' => true, 'min' => 26, 'max' => 26],
			'currentId' => ['required' => true, 'min' => 26, 'max' => 26],
		];
	}
}