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
        <p>i was playing <a href="https://osu.ppy.sh/beatmapsets/1281337#osu/2661429" target="_blank">this map</a> when i decided i want to actually start this project! <small class="text-muted">great map :-)</small></p>
    </div>
    <div class="row g-4">
        <div class="col-lg-5">
            <h3 class="mb-3">recent ratings</h3>
            <ul class="list-group mb-3">
                @foreach ($recentRatings as $rating)
                    <div class="list-group-item d-flex align-items-center ps-1 pe-2">
                        <a href="{{ url("/users/".$rating->user->id) }}" class="d-flex align-items-start flex-nowrap ms-1">
                            <img src="https://a.ppy.sh/{{ $rating->user->id }}" width="16" height="16" alt="Avatar">
                            <small class="ms-2">{{ $rating->user->name }}</small>
                        </a>
                        <small class="ms-1">rated</small>
                        <a href="{{ url('/mapsets/'.$rating->beatmap->set->id) }}" class="ms-1 d-flex">
                            <small>{{ $rating->beatmap->set->title }} [{{ $rating->beatmap->difficulty_name }}]</small>
                        </a>
                        <span class="ms-auto badge bg-main fs-6"><small>{{ number_format($rating->score / 2, 1) }}</small></span>
                        <small class="ms-2 text-nowrap" title="{{ $rating->updated_at }}">{{ $rating->updated_at->diffForHumans() }}</small>
                    </div>
                @endforeach
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
                        <img src="https://assets.ppy.sh/beatmaps/{{ $set->id }}/covers/cover.jpg" alt="beatmap bg" width="175" />
                        <div class="ms-2">
                            <a href="{{ url("/mapsets/$set->id") }}"><strong>{{ $set->artist }} - {{ $set->title }}</strong></a>
                            <a href="https://osu.ppy.sh/beatmapsets/{{ $set->id }}#osu"
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
