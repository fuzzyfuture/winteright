<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserList;

class UserListPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determines if the given list can be viewed by the given user.
     *
     * @param  ?User  $user  The user.
     * @param  UserList  $list  The list.
     * @return bool True if the list can be viewed by the user.
     */
    public function view(?User $user, UserList $list): bool
    {
        return $list->is_public || ($user && $user->id === $list->user_id);
    }

    /**
     * Determines if the given list can be updated by the given user.
     *
     * @param  User  $user  The user.
     * @param  UserList  $list  The list.
     * @return bool True if the list can be updated by the user.
     */
    public function update(User $user, UserList $list): bool
    {
        return $user->id === $list->user_id;
    }

    /**
     * Determines if the given list can be deleted by the given user.
     *
     * @param  User  $user  The user.
     * @param  UserList  $list  The list.
     * @return bool True if the list can be deleted by the user.
     */
    public function delete(User $user, UserList $list): bool
    {
        return $user->id === $list->user_id;
    }
}
