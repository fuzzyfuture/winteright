<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'beatmap_set_id', 'content'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function beatmapSet(): BelongsTo
    {
        return $this->belongsTo(BeatmapSet::class, 'beatmap_set_id');
    }
}
