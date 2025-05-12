<?php

namespace THEGHOSTLAB\CYCLE\DTO\Validation\Input;

use THEGHOSTLAB\CYCLE\DTO\Validation\DTOValidationInterface;

class ClearTestPreviewValidation implements DTOValidationInterface {

	static public function rules(): array {
		return [
			'postId' => ['required' => true, 'min' => 1],
			'clientId' => ['required' => true, 'min' => 36, 'max' => 36],
		];
	}
}