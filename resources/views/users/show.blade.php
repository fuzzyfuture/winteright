@php use App\Enums\HideRatingsOption; @endphp
@extends('layouts.app')

@section('content')
    <div class="d-flex align-items-center mb-4">
        <img src="{{ $user->avatar_url }}" alt="Avatar" class="me-3" width="64" height="64">
        <div>
            <h2 class="mb-0">
                {{ $user->name }}
                <a href="{{ $user->profile_url }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   title="view on osu!"
                   class="opacity-50 small">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
            </h2>
            @if ($user->title)
                <div class="text-muted">{{ $user->title }}</div>
            @endif
        </div>
        @auth
            <a href="{{ route('lists.add', ['item_type' => \App\Enums\UserListItemType::USER, 'item_id' => $user->id]) }}"
               class="ms-auto btn btn-outline-primary align-self-start">
                <i class="bi bi-plus"></i><i class="bi bi-list"></i>
                add to list
            </a>
        @endauth
    </div>

    @if ($user->bio)
        <p class="mb-4">{{ $user->bio }}</p>
    @endif
    @if ($user->hide_ratings != HideRatingsOption::ALL->value || Auth::id() == $user->id)
        <div class="row g-4 mb-4">
            <div class="col-lg-5">
                <h4 class="mb-3">ratings</h4>
                <div>
                    @for ($i = 10; $i >= 0; $i--)
                        @php
                            $rating = $i / 2;
                            $count = $ratingSpread[$i] ?? 0;
                            $max = $max = $ratingSpread->isEmpty() ? 1 : $ratingSpread->max();
                            $width = ($count / $max) * 100;
                        @endphp

                        <a href="{{ url('/users/'.$user->id.'/ratings?score='.number_format($rating, 1)) }}"
                           class="rating-bar d-flex align-items-center">
                            <div class="me-2" style="width: 3em;">
                                {{ number_format($rating, 1) }}
                            </div>
                            <div class="progress flex-grow-1 bg-main d-flex align-items-center" style="height: 24px;">
                                <div class="progress-bar" role="progressbar"
                                     style="width: {{ $width }}%; height: 100%;"></div>
                                <small class="ms-2">{{ $count }}</small>
                            </div>
                        </a>
                    @endfor
                </div>
            </div>
            <div class="col-lg-7">
                <h4 class="mb-3">recently rated</h4>
                <div class="list-group">
                    @forelse ($recentRatings as $rating)
                        <x-ratings.rating_list_group :rating="$rating"/>
                    @empty
                        <div class="text-muted">no ratings found.</div>
                    @endforelse
                    @if ($recentRatings->isNotEmpty())
                        <div class="list-group-item text-end">
                            <a href="{{ route('users.ratings', $user->id) }}">view all</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <h4 class="mb-3">ratings</h4>
        <div class="alert alert-sm alert-primary" data-bs-theme="dark">
            this user's ratings are private.
        </div>
    @endif
    <h4 class="mb-3">lists</h4>
    <div class="list-group mb-4">
        @forelse ($lists as $list)
            <x-lists.list_list_group :list="$list"/>
        @empty
            <div class="text-muted">no lists found.</div>
        @endforelse
        @if ($lists->isNotEmpty())
            <div class="list-group-item text-end">
                <a href="{{ route('users.lists', $user->id) }}">view all</a>
            </div>
        @endif
    </div>
    <div class="row g-4">
        <div class="col-lg-6">
            <h4 class="mb-3">mapped beatmap sets</h4>
            <div class="list-group">
                @forelse ($beatmapSets as $set)
                    <x-beatmaps.beatmap_set_list_group :set="$set"/>
                @empty
                    <div class="text-muted">no mapsets found.</div>
                @endforelse
                @if ($beatmapSets->isNotEmpty())
                    <div class="list-group-item text-end">
                        <a href="{{ route('users.mapsets', $user->id) }}">view all</a>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-6">
            <h4 class="mb-3">mapped guest difficulties</h4>
            <div class="list-group">
                @forelse ($guestDifficulties as $map)
                    <x-beatmaps.beatmap_list_group :map="$map"/>
                @empty
                    <div class="text-muted">no guest difficulties found.</div>
                @endforelse
                @if ($guestDifficulties->isNotEmpty())
                    <div class="list-group-item text-end">
                        <a href="{{ route('users.gds', $user->id) }}">view all</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
