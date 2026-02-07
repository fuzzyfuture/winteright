<?php

namespace App\Models;

use App\Enums\UserListItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserListItem extends Model
{
    protected $fillable = ['list_id', 'item_type', 'item_id', 'description', 'order', 'created_at', 'updated_at'];

    protected $casts = ['item_type' => UserListItemType::class];

    public function list(): BelongsTo
    {
        return $this->belongsTo(UserList::class);
    }

    public function item(): MorphTo
    {
        return $this->morphTo();
    }

    public function isUser(): bool
    {
        return $this->item_type == UserListItemType::USER;
    }

    public function isBeatmap(): bool
    {
        return $this->item_type == UserListItemType::BEATMAP;
    }

    public function isBeatmapSet(): bool
    {
        return $this->item_type == UserListItemType::BEATMAP_SET;
    }
}
