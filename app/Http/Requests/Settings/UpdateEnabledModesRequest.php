<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateEnabledModesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'osu' => ['boolean'],
            'taiko' => ['boolean'],
            'fruits' => ['boolean'],
            'mania' => ['boolean'],
        ];
    }

    public function after(): array
    {
        return [
            $this->validateAtLeastOneMode(...)
        ];
    }

    private function validateAtLeastOneMode($validator): void
    {
        if (!$this->boolean('osu') && !$this->boolean('taiko') &&
            !$this->boolean('fruits') && !$this->boolean('mania')) {
            $validator->errors()->add('modes', 'at least one mode must be selected.');
        }
    }
}
