@extends('layouts.app')

@section('content')
    <h1 class="mb-4">Top beatmaps of all-time</h1>
    <div class="mb-4">
        {{ $topBeatmaps->links() }}
    </div>
    @forelse ($topBeatmaps as $beatmap)
        <div class="row p-0 p-0 rounded overflow-hidden shadow-sm mb-3 bg-dark chart-beatmap-card">
            <div class="col-md-2 rounded chart-beatmap-card"
                 style="background-image: url('https://assets.ppy.sh/beatmaps/{{ $beatmap->set->set_id }}/covers/cover.jpg');">
            </div>
            <div class="col-md-10 p-3 bg-dark">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <div>
                        <h5 class="mb-1 d-flex align-items-center gap-2">
                            <a href="{{ url("/mapsets/{$beatmap->set->set_id}") }}"
                               class="text-decoration-none text-light">
                                {{ $beatmap->set->artist ?? '?' }} - {{ $beatmap->set->title . ' [' . $beatmap->difficulty_name . ']' ?? '?' }}
                            </a>
                            <a href="https://osu.ppy.sh/beatmapsets/{{ $beatmap->set->set_id }}#osu/{{ $beatmap->beatmap_id }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               title="view on osu!"
                               class="text-light opacity-50 small">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </h5>

                        <small class="text-light d-block">
                            @if ($beatmap->set->creator)
                                Mapped by: <a href="{{ url("/users/".$beatmap->set->creator_id) }}">{{ $beatmap->set->creator->name }}</a>
                            @else
                                Mapped by: Unknown
                            @endif
                        </small>
                        <small class="text-light d-block">Status: {{ $beatmap->status_label }}</small>
                        @if ($beatmap->set->date_ranked)
                            <small class="text-light d-block">{{ $beatmap->date_label }}: {{ $beatmap->set->date_ranked->format('Y-m-d') }}</small>
                        @endif
                    </div>

                    <div class="text-end">
                        <div>
                            <span class="badge bg-primary">{{ number_format($beatmap->weighted_avg, 2) }}</span>
                        </div>
                        <div class="mt-1">
                            <small class="text-light">{{ $beatmap->ratings_count ?? $beatmap->rating_count }} ratings</small>
                        </div>
                        @auth
                            @if ($beatmap->userRating)
                                <div>
                                    <small class="text-success">You rated: {{ number_format($beatmap->userRating->score / 2, 1) }}</small>
                                </div>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    @empty
        <p class="text-muted">No beatmaps found.</p>
    @endforelse
    <div class="mt-4">
        {{ $topBeatmaps->links() }}
    </div>
@endsection
