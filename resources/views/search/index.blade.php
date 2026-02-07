@extends('layouts.app')

@section('content')
    <h1 class="mb-3">search</h1>
    <div class="card mb-3">
        <div class="card-body">
            {{ html()->form('GET', route('search.index'))->open() }}
            <div class="row mb-3">
                <div class="col-md-6">
                    {{ html()->label('artist/title contains', 'artist_title')->class('form-label') }}
                    {{ html()->text('artist_title', $artistTitle)->class('form-control') }}
                </div>
                <div class="col-md-6">
                    {{ html()->label('mapper name is', 'mapper_name')->class('form-label') }}
                    {{ html()->text('mapper_name', $mapperName)->class('form-control mb-3') }}
                    {{ html()->label('mapper id is', 'mapperId')->class('form-label') }}
                    {{ html()->text('mapper_id', $mapperId)->class('form-control') }}
                </div>
            </div>
            {{ html()->submit('search')->class('btn btn-primary float-end') }}
            {{ html()->form()->close() }}
        </div>
    </div>
    <div>
        {{ $searchResults->links() }}
    </div>
    <div class="container">
        @forelse ($searchResults as $beatmapSet)
            <div class="row p-0 rounded overflow-hidden shadow-sm mb-2 chart-beatmap-card">
                <div class="col-md-2 py-2">
                    <div class="ms-md-1 audio-preview" style="background-image: url({{ $beatmapSet->bg_url }})"
                        data-playing="false">
                        <audio src="{{ $beatmapSet->preview_url }}"></audio>
                        <div class="button-overlay">
                            <i class="bi bi-play-fill h1 mb-0"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-10 pt-1 pb-3 p-md-3 ps-md-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">
                                {{ $beatmapSet->link }}
                            </h5>
                            <div>
                                mapped by: {{ $beatmapSet->creator_label }}
                            </div>
                            <div class="mt-1 d-flex align-items-center gap-2">
                                {{ $beatmapSet->status_badge }}
                                <span class="text-muted">{{ $beatmapSet->date_ranked?->format('Y-m-d') }}</span>
                                {{ $beatmapSet->difficulty_spread }}
                            </div>
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
