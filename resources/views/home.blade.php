@php
    use App\Helpers\OsuUrl;
    use Carbon\Carbon;
@endphp
@extends('layouts.app')

@section('content')
    <div class="d-lg-flex flex-lg-row align-items-center gap-5 mb-4">
        <div class="mb-4 mb-lg-0">
            <h1>winteright</h1>
            <p class="lead mb-0">a beatmap rating platform for osu!</p>
        </div>
        <div class="d-flex flex-row gap-5">
            <div class="text-center">
                <h2 class="text-primary">{{ number_format($stats['beatmapSets']) }}</h2>
                <p class="text-muted mb-0">beatmap sets</p>
            </div>
            <div class="text-center">
                <h2 class="text-primary">{{ number_format($stats['beatmaps']) }}</h2>
                <p class="text-muted mb-0">beatmaps</p>
            </div>
            <div class="text-center">
                <h2 class="text-primary">{{ number_format($stats['ratings']) }}</h2>
                <p class="text-muted mb-0">ratings</p>
            </div>
        </div>
    </div>
    <hr class="mb-4" />
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="d-flex align-items-end mb-3">
                <h3 class="mb-0">recently added</h3>
                @if ($lastSynced)
                    <small class="text-muted d-block ms-3">last
                        updated: {{ Carbon::parse($lastSynced)->diffForHumans() }}</small>
                @endif
            </div>
            <ul class="list-group mb-4">
                @foreach ($recentlyRanked as $set)
                    <x-beatmaps.beatmap_set_list_group :set="$set" />
                @endforeach
            </ul>
            <div>
                <h3 class="mb-3">faq</h3>
                <h4>why "winteright"?</h4>
                <p>
                    i was playing <a href="{{ OsuUrl::beatmapInfo(1281337, 'osu', 2661429) }}" target="_blank">this map</a>
                    when i decided i want to actually start this project! <small class="text-muted">great map :-)</small>
                </p>
                <h4>how do i blacklist my maps?</h4>
                <p>
                    feel free to contact me via <a href="https://x.com/tsukafan" target="_blank">twitter</a> or
                    <a href="{{ OsuUrl::userProfile(2966685) }}" target="_blank">osu!</a> and i'll add you asap!
                </p>
            </div>
        </div>
        <div class="col-lg-5">
            <h3 class="mb-3">recent ratings</h3>
            <ul class="list-group mb-4">
                @foreach ($recentRatings as $group)
                    @if ($group->isSingle())
                        <x-ratings.rating_list_group_small :rating="$group->ratings->first()" />
                    @else
                        <div class="list-group-item p-0">
                            <div class="rating-group-header p-2 ps-1 pe-2 cursor-pointer d-flex align-items-center"
                                data-bs-toggle="collapse" data-bs-target="#{{ $group->collapseId() }}">
                                <a href="{{ route('users.show', $group->user->id) }}"
                                    class="d-flex align-items-start flex-nowrap ms-1">
                                    <img src="{{ $group->user->avatar_url }}" width="16" height="16" alt="Avatar">
                                    <small class="ms-2">{{ $group->user->name }}</small>
                                </a>
                                <small class="ms-1">rated <b>{{ $group->count() }} maps</b></small>
                                <small class="ms-auto text-nowrap text-muted" title="{{ $group->time }}">
                                    {{ $group->time->diffForHumans() }}
                                </small>
                                <i class="ms-2 bi bi-chevron-down"></i>
                            </div>
                            <div id="{{ $group->collapseId() }}" class="collapse">
                                @foreach ($group->ratings as $rating)
                                    <div
                                        class="rating-group-rating p-1 mx-3 d-flex align-items-center {{ $loop->last ? 'pb-2' : '' }}">
                                        <a href="{{ route('beatmaps.show', $rating->beatmap->set->id) }}"
                                            class="ms-1 d-flex">
                                            <small>
                                                {{ $rating->beatmap->set->title }}
                                                [{{ $rating->beatmap->difficulty_name }}]
                                            </small>
                                        </a>
                                        <small class="ms-auto text-nowrap text-muted" title="{{ $rating->updated_at }}">
                                            {{ $rating->updated_at->diffForHumans() }}
                                        </small>
                                        <span class="ms-2 badge bg-main fs-6">
                                            <small>{{ number_format($rating->score / 2, 1) }}</small>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </ul>
            <h3 class="mb-3">recent comments</h3>
            <ul class="list-group mb-3">
                @foreach ($recentComments as $comment)
                    <div class="list-group-item {{ $comment->trashed() ? 'opacity-50' : '' }}">
                        <div class="d-flex align-items-start flex-nowrap">
                            <a href="{{ route('users.show', $comment->user->id) }}"
                                class="d-flex align-items-start flex-nowrap">
                                <img src="{{ $comment->user->avatar_url }}" width="16" height="16" alt="Avatar">
                                <small class="ms-2">{{ $comment->user->name }}</small>
                            </a>
                            <small class="ms-1">on</small>
                            <a href="{{ route('beatmaps.show', $comment->set->id ?? 0) }}" class="ms-1 d-flex">
                                <small>
                                    {{ $comment->set->title }}
                                </small>
                            </a>
                            <small class="text-muted ms-auto" title="{{ $comment->created_at }}">
                                {{ $comment->created_at->diffForHumans() }}
                            </small>
                        </div>
                        <div>
                            {{ $comment->content }}
                        </div>
                    </div>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
