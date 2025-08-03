<?php

namespace App\Services;

use App\Models\UserList;
use Illuminate\Support\Collection;

class UserListService
{
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
