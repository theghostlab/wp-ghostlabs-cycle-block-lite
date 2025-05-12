<?php

namespace THEGHOSTLAB\CYCLE\DTO\Validation\Input;

use THEGHOSTLAB\CYCLE\DTO\Validation\DTOValidationInterface;

class PostIdValidation implements DTOValidationInterface
{
    public static function rules(): array
    {
        return [
            'postId' => [
                'required' => true,
                'min' => 1,
            ]
        ];
    }
}