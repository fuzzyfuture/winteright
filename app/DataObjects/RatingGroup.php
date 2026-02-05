<?php

namespace App\DataObjects;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class RatingGroup
{
    public function __construct(
        public User $user,
        public Collection $ratings,
        public Carbon $time,
    ) {}

    public function isSingle(): bool
    {
        return $this->ratings->count() === 1;
    }

    public function count(): int
    {
        return $this->ratings->count();
    }

    public function collapseId(): string
    {
        return "ratings-{$this->user->id}-{$this->time->timestamp}";
    }
}
