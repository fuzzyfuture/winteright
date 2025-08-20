<?php

namespace App\Services;

use App\Enums\UserListItemType;
use App\Models\Beatmap;
use App\Models\UserList;
use App\Models\UserListItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class UserListService
{
    /**
     * Retrieves a list by ID.
     * @param int $id The ID of the list.
     * @return UserList The list.
     */
    public function get(int $id): ?UserList
    {
        return UserList::find($id);
    }

    /**
     * Retrieves a list by ID. Includes the owner relation.
     * @param int $id The ID of the list.
     * @return UserList The list, if it exists. Null if the list does not exist.
     */
    public function getWithOwner(int $id): UserList
    {
        return UserList::whereId($id)
            ->with('owner')
            ->firstOrFail();
    }

    /**
     * Retrieves a list's items.
     * @param int $id The list's ID.
     * @param int $perPage The amount of items to display per-page.
     * @return LengthAwarePaginator The list's paginated items.
     */
    public function getItems(int $id, int $perPage = 50): LengthAwarePaginator
    {
        $items = UserListItem::where('list_id', $id)
            ->with('item')
            ->paginate($perPage);

        $beatmaps = $items->getCollection()
            ->filter(function ($item) {
               return $item->item_type == UserListItemType::BEATMAP && $item->item instanceof Beatmap;
            })
            ->map(function ($item) {
                return $item->item;
            });

        $beatmaps->load('set');

        return $items;
    }

    /**
     * Retrieves lists with the specified search parameters.
     * @param string|null $name The list name to search for.
     * @param string|null $creatorName The creator name to search for.
     * @param int $perPage The amount of results to display per page.
     * @return LengthAwarePaginator The paginated results.
     */
    public function search(?string $name, ?string $creatorName, int $perPage = 50): LengthAwarePaginator
    {
        $query = UserList::with('owner')
            ->orderBy('created_at', 'desc');

        if (!blank($name)) {
            $query->where('name', 'LIKE', '%'.$name.'%');
        }

        if (!blank($creatorName)) {
            $userService = app(UserService::class);
            $creatorId = $userService->getIdByNameUsersOnly($creatorName);

            $query->where('user_id', $creatorId);
        }

        return $query->paginate($perPage);
    }

    /**
     * Retrieves a user's lists.
     * @param int $userId The user ID.
     * @return Collection The user's lists.
     */
    public function getForUser(int $userId): Collection
    {
        return UserList::where('user_id', $userId)->get();
    }
}
