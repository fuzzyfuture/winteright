<?php

namespace App\Http\Requests\UserLists;

use App\Enums\UserListItemType;
use App\Services\BeatmapService;
use App\Services\UserListService;
use App\Services\UserService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Throwable;

class AddUserListItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $listId = $this->input('list_id');
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
            'list_id' => ['integer', 'required', 'exists:user_lists,id'],
            'item_type' => [Rule::enum(UserListItemType::class), 'required'],
            'item_id' => ['integer', 'required', $this->validateItemExists()],
            'description' => ['string', 'nullable'],
            'order' => ['integer', 'required'],
        ];
    }

    /**
     * Defines a validation function for ensuring the added item exists.
     *
     * @return Closure The validation function.
     */
    private function validateItemExists(): Closure
    {
        return function ($attribute, $value, $fail) {
            $itemType = $this->input('item_type');
            $enumCase = UserListItemType::tryFrom($itemType);

            if ($enumCase == UserListItemType::USER) {
                $userService = app(UserService::class);

                if (! $userService->exists($value)) {
                    $fail('user with id ' . $value . ' does not exist.');
                }
            } elseif ($enumCase == UserListItemType::BEATMAP) {
                $beatmapService = app(BeatmapService::class);

                if (! $beatmapService->exists($value)) {
                    $fail('beatmap with id ' . $value . ' does not exist.');
                }
            } elseif ($enumCase == UserListItemType::BEATMAP_SET) {
                $beatmapService = app(BeatmapService::class);

                if (! $beatmapService->setExists($value)) {
                    $fail('beatmap set with id ' . $value . ' does not exist.');
                }
            }
        };
    }
}
