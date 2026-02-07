<?php

namespace App\DataObjects;

use App\Helpers\OsuUrl;
use Illuminate\Support\HtmlString;

class TopRatedMapper
{
    public function __construct(
        public int $creatorId,
        public ?string $username,
        public ?string $creatorName,
        public int $ratingCount,
        public float $averageScore,
        public float $bayesian
    ) {}

    public function getName(): string
    {
        return $this->username ?? $this->creatorName ?? $this->creatorId;
    }

    public function getProfileUrl(): string
    {
        return OsuUrl::userProfile($this->creatorId);
    }

    public function getAvatarUrl(): string
    {
        return OsuUrl::userAvatar($this->creatorId);
    }

    public function getLink(): HtmlString
    {
        if ($this->username) {
            $localLink = '<a href="'.route('users.show', $this->creatorId).'">'.e($this->username).'</a>';
        } elseif ($this->creatorName) {
            $localLink = e($this->creatorName);
        } else {
            $localLink = $this->creatorId;
        }

        $extLink = '<a href="'.$this->getProfileUrl().'"
               target="_blank"
               rel="noopener noreferrer"
               title="view on osu!"
               class="opacity-50 small">
                <i class="bi bi-box-arrow-up-right"></i>
            </a>';

        return new HtmlString($localLink.$extLink);
    }
}
