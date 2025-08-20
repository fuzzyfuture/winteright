<?php

namespace App\Validators;

class RatingValidator extends ValidatorWrapper
{
    protected array $rules = [
        'update' => [
            'score' => ['nullable', 'integer', 'between:0,10'],
        ]
    ];
}
