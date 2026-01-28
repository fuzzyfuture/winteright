<?php

namespace App\Http\Requests\Settings;

use App\Enums\HideRatingsOption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateHideRatingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'hide_ratings' => ['required', Rule::enum(HideRatingsOption::class)]
        ];
    }
}
