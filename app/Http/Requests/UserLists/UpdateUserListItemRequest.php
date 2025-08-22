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
        $listId = $this->route('id');
        $userListService = app(UserListService::class);

        try {
            $list = $userListService->get($listId);
            return Gate::allows('update', $list);
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
            'item_id' => ['integer', 'required', 'exists:user_list_items,id'],
            'description' => ['string', 'nullable'],
            'order' => ['integer', 'required'],
        ];
    }
}
