@extends('layouts.app')

@section('content')
    <h1>winteright</h1>
    <p class="lead mb-4">a community-powered beatmap rating platform for osu!</p>
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
        <p>I was playing <a href="https://osu.ppy.sh/beatmapsets/1281337#osu/2661429" target="_blank">this map</a> when I decided I want to actually start this project.</p>
    </div>
    <div class="row">
        <div class="col-md-6">
            <h3 class="mb-1">recently ranked</h3>
            @if ($lastSynced)
                <small class="text-muted d-block mb-3">last updated: {{ \Carbon\Carbon::parse($lastSynced)->diffForHumans() }}</small>
            @endif
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
        <div class="col-md-6">
            <h3 class="mb-1">recent ratings</h3>
        </div>
    </div>
@endsection
