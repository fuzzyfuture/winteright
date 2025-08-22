<?php

namespace App\Services;

use App\Enums\UserListItemType;
use App\Models\Beatmap;
use App\Models\UserList;
use App\Models\UserListItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class UserListService
{
    /**
     * Retrieves a list by ID.
     * @param int $id The ID of the list.
     * @return UserList|null The list.
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
            ->withCount('items')
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
            ->orderByDesc('order')
            ->orderBy('created_at')
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
            ->withCount('items')
            ->where('is_public', true)
            ->orderByRaw('COALESCE(updated_at, created_at) DESC');

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
     *
     * @param int $userId The user's ID.
     * @param bool $includePrivate Whether to include private lists in the results. Defaults to false.
     */
    public function getForUser(int $userId, bool $includePrivate = false): Collection
    {
        $query = UserList::where('user_id', $userId);

        if (!$includePrivate) {
            $query->where('is_public', true);
        }

        return $query->get();
    }

    /**
     * Retrieves a user's lists for their profile.
     *
     * @param int $userId The user's ID.
     * @param bool $includePrivate Whether to include private lists in the results. Defaults to false. Should only
     * be true when a user is viewing their own profile.
     */
    public function getForProfile(int $userId, bool $includePrivate = false): LengthAwarePaginator
    {
        $query = UserList::where('user_id', $userId)
            ->with('owner')
            ->withCount('items');

        if (!$includePrivate) {
            $query->where('is_public', true);
        }

        return $query->paginate(50);
    }

    /**
     * Creates a new list.
     * @param int $userId The user ID of the creator of the list.
     * @param string $name The name of the list.
     * @param ?string $description The list's description.
     * @param bool $isPublic True if the list should be public.
     * @return UserList The newly created list.
     * @throws Throwable
     */
    public function create(int $userId, string $name, ?string $description, bool $isPublic): UserList
    {
        return DB::transaction(function () use ($userId, $name, $description, $isPublic) {
            return UserList::create([
                'user_id' => $userId,
                'name' => $name,
                'description' => $description,
                'is_public' => $isPublic,
            ]);
        });
    }

    /**
     * Updates a list.
     * @param int $listId The ID of the list to update.
     * @param string $name The name of the list.
     * @param string $description The list's description.
     * @param bool $isPublic True if the list should be public.
     * @return UserList The updated list.
     * @throws Throwable
     */
    public function update(int $listId, string $name, string $description, bool $isPublic): UserList
    {
        return DB::transaction(function () use ($listId, $name, $description, $isPublic) {
            $list = UserList::findOrFail($listId);

            $list->name = $name;
            $list->description = $description;
            $list->is_public = $isPublic;

            $list->save();

            return $list;
        });
    }

    /**
     * Deletes a list.
     * @param int $listId The ID of the list to be deleted.
     * @return void
     * @throws Throwable
     */
    public function delete(int $listId): void
    {
        DB::transaction(function () use ($listId) {
            UserList::destroy($listId);
        });
    }
}
