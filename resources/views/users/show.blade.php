@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex align-items-center mb-4">
            <img src="{{ $user->avatar }}" alt="Avatar" class="rounded-circle me-3" width="64" height="64">
            <div>
                <h2 class="mb-0">{{ $user->name }}</h2>
                @if ($user->title)
                    <div class="text-muted">{{ $user->title }}</div>
                @endif
            </div>
        </div>

        @if ($user->bio)
            <p class="mb-4">{{ $user->bio }}</p>
        @endif

        <h4>Rating Distribution</h4>
        <div class="mb-4">
            @for ($i = 10; $i >= 0; $i--)
                @php
                    $rating = $i / 2;
                    $count = $ratingSpread[$i] ?? 0;
                    $max = max($ratingSpread->values()->toArray()) ?: 1;
                    $width = ($count / $max) * 100;
                @endphp

                <div class="d-flex align-items-center mb-1">
                    <div class="me-2" style="width: 3em;">{{ number_format($rating, 1) }}</div>
                    <div class="progress flex-grow-1 bg-dark" style="height: 1.25rem;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $width }}%">
                            {{ $count }}
                        </div>
                    </div>
                </div>
            @endfor
        </div>

        <h4>Recently Rated Beatmaps</h4>
        <div class="list-group">
            @forelse ($recentRatings as $rating)
                <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                   href="{{ url("/mapsets/{$rating->beatmap->set->set_id}") }}">
                    <div>
                        <strong>{{ $rating->beatmap->set->artist ?? '?' }} - {{ $rating->beatmap->set->title ?? '?' }}</strong>
                        <div class="text-muted small">[{{ $rating->beatmap->difficulty_name }}]</div>
                    </div>
                    <span class="badge bg-primary">
                {{ number_format($rating->score / 2, 1) }}
            </span>
                </a>
            @empty
                <div class="text-muted">No ratings found.</div>
            @endforelse
        </div>
    </div>
@endsection
