<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * Checks if a user with the given ID exists. Returns true if the user exists.
     * @param int $id The user ID to check.
     * @return bool True if the user exists.
     */
    public function exists(int $id): bool
    {
        return User::whereId($id)->exists();
    }

    /**
     * Retrieves the user with the given ID.
     * @param int $id The user's ID.
     * @return User The user.
     */
    public function get(int $id): User
    {
        return User::whereId($id)->firstOrFail();
    }

    /**
     * Retrieves a user's name from their ID. Uses the beatmap creator names table as a fallback, if the user is not
     * a winteright user.
     * @param int $id The user's ID.
     * @return string The user's name.
     */
    public function getName(int $id): string
    {
        $user = User::whereId($id)->first();

        if ($user) {
            return $user->name;
        }

        return DB::table('beatmap_creator_names')->where('id', $id)->value('name');
    }

    /**
     * Retrieves a user's ID from their name. Uses the beatmap creator names table as a fallback, if the user is not
     * a winteright user.
     * @param string $name The user's name.
     * @return int The user's ID, or -1 if the user is not a winteright user or in the beatmap creator names table.
     */
    public function getIdByName(string $name): int
    {
        $user = User::whereName($name)->first();

        if ($user) {
            return $user->id;
        }

        $creatorId = DB::table('beatmap_creator_names')->where('name', $name)->value('id');

        if ($creatorId) {
            return $creatorId;
        }

        return -1;
    }
}
