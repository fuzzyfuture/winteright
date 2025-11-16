@extends('layouts.app')

@section('content')
    <h1 class="mb-3">charts</h1>
    <div class="row">
        <div class="col-md-3">
            <h2 class="mb-3">filters</h2>
            <div class="card">
                <div class="card-body">
                    {{ html()->form('GET', route('charts.index'))->open() }}
                        {{ html()->label('year', 'year')->class('form-label') }}
                        {{ html()->select('year', $yearOptions, $year)->class('form-select mb-2') }}
                        <div class="form-check mb-2">
                            {{ html()->checkbox('exclude_rated', $excludeRated)->class('form-check-input') }}
                            {{ html()->label('exclude rated beatmaps', 'exclude_rated')->class('form-check-label') }}
                        </div>
                        {{ html()->submit('filter')->class('btn btn-primary float-end') }}
                    {{ html()->form()->close() }}
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="d-flex align-items-end mb-3">
                <h2 class="mb-0">top {{ $excludeRated ? 'unrated ' : '' }}beatmaps of {{ $year ?? 'all-time' }}</h2>
                @if ($lastUpdated)
                    <small class="text-muted d-block ms-3 mb-1">last updated: {{ $lastUpdated->diffForHumans() }}</small>
                @endif
            </div>
            <div>
                {{ $topBeatmaps->links() }}
            </div>
            <div class="container">
                @forelse ($topBeatmaps as $beatmap)
                    <div class="row p-0 rounded overflow-hidden shadow-sm mb-2 chart-beatmap-card">
                        <div class="col-md-2 p-2">
                            <div class="chart-beatmap-img w-100 h-100"
                                 style="background-image: url('https://assets.ppy.sh/beatmaps/{{ $beatmap->set->id }}/covers/cover.jpg');">
                            </div>
                        </div>
                        <div class="col-md-10 p-3 ps-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <h5 class="mb-1">{{ $beatmap->url }}</h5>
                                    <div>
                                        mapped by: {{ $beatmap->creator_label }}
                                    </div>
                                    <div class="mt-1 d-flex align-items-center gap-2">
                                        {{ $beatmap->status_badge }}
                                        <small class="text-muted">{{ $beatmap->set->date_ranked->format('Y-m-d') }}</small>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <div>
                                        <span class="badge bg-main fs-5">{{ number_format($beatmap->weighted_avg, 2) }}</span>
                                    </div>
                                    <div class="mt-1">
                                        {{ $beatmap->ratings_count }} ratings
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
        </div>
    </div>
@endsection
