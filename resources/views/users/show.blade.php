@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex align-items-center mb-4">
            <img src="https://a.ppy.sh/{{ $user->id }}" alt="Avatar" class="me-3" width="64" height="64">
            <div>
                <h2 class="mb-0">
                    {{ $user->name }}
                    <a href="https://osu.ppy.sh/users/{{ $user->id }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       title="view on osu!"
                       class="opacity-50 small">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                </h2>
                @if ($user->title)
                    <div class="text-muted">{{ $user->title }}</div>
                @endif
            </div>
        </div>

        @if ($user->bio)
            <p class="mb-4">{{ $user->bio }}</p>
        @endif

        <h4 class="mb-3">rating distribution</h4>
        <div class="mb-4">
            @for ($i = 10; $i >= 0; $i--)
                @php
                    $rating = $i / 2;
                    $count = $ratingSpread[$i] ?? 0;
                    $max = $max = $ratingSpread->isEmpty() ? 1 : $ratingSpread->max();
                    $width = ($count / $max) * 100;
                @endphp

                <div class="d-flex align-items-center mb-1">
                    <div class="me-2" style="width: 3em;">{{ number_format($rating, 1) }}</div>
                    <div class="progress flex-grow-1 bg-main" style="height: 1.25rem;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $width }}%"></div>
                        <small class="ms-2">{{ $count }}</small>
                    </div>
                </div>
            @endfor
        </div>

        <h4 class="mb-3">recently rated</h4>
        <div class="list-group">
            @forelse ($recentRatings as $rating)
                <div class="list-group-item d-flex align-items-center p-3">
                    <img src="https://assets.ppy.sh/beatmaps/{{ $rating->beatmap->set->id }}/covers/cover.jpg" alt="beatmap bg" width="175" />
                    <div class="ms-3">
                        <a href="{{ url('/mapsets/'.$rating->beatmap->set->id) }}"><strong>{{ $rating->beatmap->set->artist }} - {{ $rating->beatmap->set->title }} [{{ $rating->beatmap->difficulty_name }}]</strong></a>
                        <a href="https://osu.ppy.sh/beatmapsets/{{ $rating->beatmap->set->id }}#osu/{{ $rating->beatmap->id }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           title="view on osu!"
                           class="opacity-50 small">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                        <small class="text-muted d-block">
                            by {{ $rating->beatmap->creator_label }}
                        </small>
                    </div>
                    <div class="ms-auto text-muted text-center">
                        <span class="badge bg-main fs-6">{{ number_format($rating->score / 2, 1) }}</span><br/>
                        <small>{{ $rating->updated_at->format('Y-m-d') }}</small>
                    </div>
                </div>
            @empty
                <div class="text-muted">No ratings found.</div>
            @endforelse
        </div>
    </div>
@endsection
