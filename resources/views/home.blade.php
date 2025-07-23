@extends('layouts.app')

@section('content')
    <h1>winteright</h1>
    <p class="lead mb-4">a beatmap rating platform for osu!</p>
    <div class="d-flex flex-row gap-5 mb-4">
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
    <div class="mb-4">
        <h3>why "winteright"?</h3>
        <p>i was playing <a href="https://osu.ppy.sh/beatmapsets/1281337#osu/2661429" target="_blank">this map</a> when i decided i want to actually start this project!</p>
    </div>
    <div class="row g-4">
        <div class="col-lg-5">
            <h3 class="mb-3">recent ratings</h3>
            <ul class="list-group mb-3">
                @foreach ($recentRatings as $rating)
                    <div class="list-group-item d-flex align-items-center">
                        <a href="{{ url("/users/".$rating->user->id) }}" class="d-flex flex-nowrap">
                            <img src="https://a.ppy.sh/{{ $rating->user->id }}" width="16" height="16" alt="Avatar">
                            <span class="d-block ms-1">{{ $rating->user->name }}</span>
                        </a>
                        <span class="ms-2">rated</span>
                        <a href="{{ url('/mapsets/'.$rating->beatmap->set->set_id) }}" class="ms-2">
                            <strong>{{ $rating->beatmap->set->title }} [{{ $rating->beatmap->difficulty_name }}]</strong>
                        </a>
                        <span class="ms-auto badge bg-main fs-6">{{ number_format($rating->score / 2, 1) }}</span>
                    </div>
                @endforeach
            </ul>
            <h3 class="mb-3">recent comments</h3>
            <ul class="list-group">

            </ul>
        </div>
        <div class="col-lg-7">
            <div class="d-flex align-items-end mb-3">
                <h3 class="mb-0">recently ranked</h3>
                @if ($lastSynced)
                    <small class="text-muted d-block ms-3">last updated: {{ \Carbon\Carbon::parse($lastSynced)->diffForHumans() }}</small>
                @endif
            </div>
            <ul class="list-group">
                @foreach ($recentlyRanked as $set)
                    <div class="list-group-item d-flex align-items-center">
                        <img src="https://assets.ppy.sh/beatmaps/{{ $set->set_id }}/covers/cover.jpg" alt="beatmap bg" width="175" />
                        <div class="ms-2">
                            <a href="{{ url("/mapsets/$set->set_id") }}"><strong>{{ $set->artist }} - {{ $set->title }}</strong></a>
                            <a href="https://osu.ppy.sh/beatmapsets/{{ $set->set_id }}#osu"
                               target="_blank"
                               rel="noopener noreferrer"
                               title="view on osu!"
                               class="opacity-50 small">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                            <small class="text-muted d-block">
                                by {{ $set->creator_label }}
                            </small>
                            <small class="text-muted d-block">
                                {{ $set->beatmaps_count }} difficult{{ $set->beatmaps_count !== 1 ? 'ies' : 'y' }}
                            </small>
                        </div>
                        <span class="text-muted ms-auto">{{ $set->date_ranked?->format('Y-m-d') }}</span>
                    </div>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
