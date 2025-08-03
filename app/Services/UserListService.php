<?php

namespace App\Services;

use App\Models\UserList;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class UserListService
{
    /**
     * Retrieves a list by ID.
     * @param int $id The ID of the list.
     * @return UserList The list.
     */
    public function get(int $id): UserList
    {
        return UserList::whereId($id)
            ->with('owner')
            ->first();
    }

    public function search(int $perPage = 50): LengthAwarePaginator
    {
        return UserList::with('owner')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
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
