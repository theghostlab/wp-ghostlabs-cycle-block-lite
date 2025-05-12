<?php

namespace THEGHOSTLAB\CYCLE\DTO\Validation\Input;

use THEGHOSTLAB\CYCLE\DTO\Validation\DTOValidationInterface;

class UpdateBlockValidation implements DTOValidationInterface {

	static public function rules(): array {
		return [
			'postId' => ['required' => true, 'min' => 1],
			'blockId' => ['required' => true, 'min' => 26, 'max' => 26],
			'entryId' => ['required' => true, 'min' => 26, 'max' => 26],
		];
	}
}