@extends('layouts.app')

@section('content')
    <h1 class="mb-4">top beatmaps of all-time</h1>
    <div>
        {{ $topBeatmaps->links() }}
    </div>
    <div class="container">
        @forelse ($topBeatmaps as $beatmap)
            <div class="row p-0 rounded overflow-hidden shadow-sm mb-2 chart-beatmap-card">
                <div class="col-md-2 p-2">
                    <div class="chart-beatmap-img w-100 h-100"
                         style="background-image: url('https://assets.ppy.sh/beatmaps/{{ $beatmap->set->set_id }}/covers/cover.jpg');">
                    </div>
                </div>
                <div class="col-md-10 p-3 ps-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div>
                            <h5 class="mb-1 d-flex align-items-center gap-2">
                                <a href="{{ url("/mapsets/{$beatmap->set->set_id}") }}">
                                    {{ $beatmap->set->artist ?? '?' }} - {{ $beatmap->set->title . ' [' . $beatmap->difficulty_name . ']' ?? '?' }}
                                </a>
                                <a href="https://osu.ppy.sh/beatmapsets/{{ $beatmap->set->set_id }}#osu/{{ $beatmap->beatmap_id }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   title="view on osu!"
                                   class="opacity-50 small">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </h5>

                            <div>
                                mapped by: {{ $beatmap->creator_label }}
                            </div>
                            <div>status: {{ $beatmap->status_label }}</div>
                            @if ($beatmap->set->date_ranked)
                                <div>{{ $beatmap->date_label }}: {{ $beatmap->set->date_ranked->format('Y-m-d') }}</div>
                            @endif
                        </div>

                        <div class="text-end">
                            <div>
                                <span class="badge bg-main fs-5">{{ number_format($beatmap->weighted_avg, 2) }}</span>
                            </div>
                            <div class="mt-1">
                                {{ $beatmap->ratings_count ?? $beatmap->rating_count }} ratings
                            </div>
                            @auth
                                @if ($beatmap->userRating)
                                    <div class="text-success">you rated: {{ number_format($beatmap->userRating->score / 2, 1) }}
                                    </div>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">no beatmaps found</p>
        @endforelse
    </div>
    <div>
        {{ $topBeatmaps->links() }}
    </div>
@endsection
