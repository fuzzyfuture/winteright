@extends('layouts.app')

@section('content')
    <h1 class="mb-4">search</h1>
    <div class="card mb-4">
        <div class="card-body">
            <form href="{{ url('/charts') }}" method="GET">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">artist/title contains</label>
                        <input class="form-control" name="artistTitle" value="{{ $artistTitle }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">mapper name is</label>
                        <input class="form-control mb-3" name="mapperName" value="{{ $mapperName }}">
                        <label class="form-label">mapper id is</label>
                        <input class="form-control" name="mapperId" value="{{ $mapperId }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary float-end">search</button>
            </form>
        </div>
    </div>
    <div>
        {{ $searchResults->links() }}
    </div>
    <div class="container">
        @forelse ($searchResults as $beatmapSet)
            <div class="row p-0 rounded overflow-hidden shadow-sm mb-2 chart-beatmap-card">
                <div class="col-md-2 p-2">
                    <div class="chart-beatmap-img w-100 h-100"
                         style="background-image: url('https://assets.ppy.sh/beatmaps/{{ $beatmapSet->id }}/covers/cover.jpg');">
                    </div>
                </div>
                <div class="col-md-10 p-3 ps-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div>
                            <h5 class="mb-1">
                                {{ $beatmapSet->url }}
                            </h5>
                            <div>
                                mapped by: {{ $beatmapSet->creator_label }}
                            </div>
                            @if ($beatmapSet->date_ranked)
                                <div>ranked: {{ $beatmapSet->date_ranked->format('Y-m-d') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">no beatmaps found</p>
        @endforelse
    </div>
    <div>
        {{ $searchResults->links() }}
    </div>
@endsection
