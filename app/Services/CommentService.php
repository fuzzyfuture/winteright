<?php

namespace App\Services;

use App\Enums\BeatmapMode;
use App\Enums\HideRatingsOption;
use App\Models\Comment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class CommentService
{
    /**
     * Retrieves a comment by ID.
     *
     * @param int $commentId The comment's ID.
     * @return Comment|null The comment.
     */
    public function get(int $commentId): ?Comment
    {
        return Comment::find($commentId);
    }

    /**
     * Retrieves all comments for a specified beatmap set.
     *
     * @param int $beatmapSetId The ID of the beatmap set.
     * @param bool $withTrashed True if soft-deleted comments should be included.
     * @return Collection The beatmap set's comments.
     */
    public function getAllForBeatmapSet(int $beatmapSetId, bool $withTrashed = false): Collection
    {
        $query = Comment::where('beatmap_set_id', $beatmapSetId)
            ->with('user')
            ->whereHas('user', function ($query) {
                $query->where('hide_comments', '!=', HideRatingsOption::ALL->value);
            })
            ->orderByDesc('created_at');

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->get();
    }

    /**
     * Retrieves recent comments for all beatmap sets.
     *
     * @param int $enabledModes Bitfield of enabled modes.
     * @param bool $withTrashed True if soft-deleted comments should be included.
     * @param int $limit The amount of recent comments to retrieve.
     * @return Collection The recent comments.
     */
    public function getRecent(int $enabledModes, bool $withTrashed = false, int $limit = 15): Collection
    {
        return Cache::remember('comments:recent:'.$limit.':'.$enabledModes, 120, function () use ($enabledModes, $limit, $withTrashed) {
            $modesArray = BeatmapMode::bitfieldToArray($enabledModes);
            $query = Comment::orderByDesc('created_at')
                ->with('user')
                ->with('set')
                ->whereHas('set.beatmaps', function ($query) use ($modesArray) {
                    $query->whereIn('mode', $modesArray)
                        ->where('blacklisted', false);
                })
                ->whereHas('user', function ($query) {
                    $query->where('hide_comments', HideRatingsOption::NONE->value);
                })
                ->limit($limit);

            if ($withTrashed) {
                $query->withTrashed();
            }

            return $query->get();
        });
    }

    /**
     * Creates a new comment.
     *
     * @param int $userId The user ID of the commenter.
     * @param int $beatmapSetId The ID of the beatmap set being commented on.
     * @param string $content The content of the comment.
     * @return Comment The newly created comment.
     * @throws Throwable
     */
    public function create(int $userId, int $beatmapSetId, string $content): Comment
    {
        return DB::transaction(function () use ($userId, $beatmapSetId, $content) {
            return Comment::create([
                'user_id' => $userId,
                'beatmap_set_id' => $beatmapSetId,
                'content' => $content,
            ]);
        });
    }

    /**
     * Soft-deletes a comment.
     *
     * @param int $commentId The ID of the comment to be soft-deleted.
     * @return void
     * @throws Throwable
     */
    public function delete(int $commentId): void
    {
        DB::transaction(function () use ($commentId) {
            Comment::destroy($commentId);
        });
    }
}
