<?php

namespace App\Http\Requests\Ratings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GetUserRatingsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'score' => ['nullable', 'numeric', 'in:0.0,0.5,1.0,1.5,2.0,2.5,3.0,3.5,4.0,4.5,5.0'],
            'srMin' => ['nullable', 'numeric'],
            'srMax' => ['nullable', 'numeric'],
            'dateMin' => ['nullable', 'date'],
            'dateMax' => ['nullable', 'date'],
            'mapperName' => ['nullable', 'string'],
            'mapperId' => ['nullable', 'integer'],
            'sort' => ['nullable', 'string', 'in:score,sr,ranked_date'],
            'sortDirection' => ['nullable', 'string', 'in:desc,asc'],
        ];
    }
}
