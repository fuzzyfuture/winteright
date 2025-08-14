@extends('layouts.app')

@section('content')
    <h1 class="mb-4">charts</h1>
    <div class="row">
        <div class="col-md-3">
            <h2>filters</h2>
            <div class="card">
                <div class="card-body">
                    <form href="{{ url('/charts') }}" method="GET">
                        <label class="form-label">year</label>
                        <select class="form-select mb-2" name="year">
                            <option value="" {{ !$year ? 'selected' : '' }}>all-time</option>
                            @foreach ($beatmapYears as $beatmapYear)
                                <option value="{{ $beatmapYear }}" {{ $year == $beatmapYear ? 'selected' : '' }}>{{ $beatmapYear }}</option>
                            @endforeach
                        </select>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="excludeRated" {{ $excludeRated ? 'checked' : '' }}>
                            <label class="form-check-label">exclude rated beatmaps</label>
                        </div>
                        <button type="submit" class="btn btn-primary float-end">filter</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <h2>top {{ $excludeRated ? 'unrated ' : '' }}beatmaps of {{ $year ?? 'all-time' }}</h2>
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
