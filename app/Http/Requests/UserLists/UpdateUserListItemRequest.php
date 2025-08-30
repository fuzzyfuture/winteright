<?php

namespace App\Http\Requests\UserLists;

use App\Services\UserListService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Throwable;

class UpdateUserListItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $itemId = $this->route('id');
        $userListService = app(UserListService::class);

        try {
            $item = $userListService->getItem($itemId);
            return Gate::allows('update', $item->list);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'description' => ['string', 'nullable'],
            'order' => ['integer', 'required'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            redirect()->back()
                ->withErrors($validator)
                ->withInput([])
        );
    }
}
