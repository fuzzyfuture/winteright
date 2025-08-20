<?php

namespace App\Validators;

class UserListValidator extends ValidatorWrapper
{
    protected array $rules = [
        'create' => [
            'name' => ['string', 'required'],
            'description' => ['string', 'nullable'],
            'is_public' => ['boolean'],
        ],
        'update' => [
            'name' => ['string', 'required'],
            'description' => ['string', 'nullable'],
            'is_public' => ['boolean'],
        ]
    ];
}
