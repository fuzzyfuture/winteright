@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <h1>
                {{ $beatmapSet->title }}
                <a href="https://osu.ppy.sh/beatmapsets/{{ $beatmapSet->id }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   title="view on osu!"
                   class="opacity-50 small">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
            </h1>
            <h3>{{ $beatmapSet->artist }}</h3>
            <p class="text-muted">
                mapset by
                <strong>{!! $beatmapSet->creatorLabel !!}</strong>
            </p>
            <img src="https://assets.ppy.sh/beatmaps/{{ $beatmapSet->id }}/covers/cover.jpg"
                 class="img-fluid rounded shadow-sm mb-4"
                 style="max-height: 300px; object-fit: cover;"
                 alt="{{ $beatmapSet->artist }} - {{ $beatmapSet->title }} banner">
        </div>
        <div class="col-md-6">
            <h4>info</h4>
            <ul class="list-unstyled">
                <li><strong>date ranked:</strong> {{ $beatmapSet->date_ranked->toFormattedDateString() }}</li>
                <li><strong>genre:</strong> {{ $beatmapSet->genre_label }}</li>
                <li><strong>language:</strong> {{ $beatmapSet->language_label }}</li>
                <li><strong>storyboard:</strong> {{ $beatmapSet->has_storyboard ? 'yes' : 'no' }}</li>
                <li><strong>video:</strong> {{ $beatmapSet->has_video ? 'yes' : 'no' }}</li>
            </ul>
            <h4>osu! link</h4>
            <a href="https://osu.ppy.sh/beatmapsets/{{ $beatmapSet->id }}" target="_blank">
                view on osu! <i class="bi bi-box-arrow-up-right"></i>
            </a>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-8">
            <h4 class="mb-3">difficulties</h4>
            @if ($beatmapSet->beatmaps->contains('blacklisted', true))
                <div class="alert alert-sm alert-primary" data-bs-theme="dark">
                    note: some beatmaps in this set are blacklisted - you are still able to rate them, but their average ratings
                    are hidden and they will not appear on the charts.
                </div>
            @endif
            <div class="list-group">
                @foreach ($beatmapSet->beatmaps as $beatmap)
                    <div class="list-group-item d-flex align-items-center{{ $beatmap->blacklisted ? ' opacity-50' : '' }}">
                        {{ $beatmap->mode_icon }}
                        <div class="ms-3">
                            <div class="d-inline-flex gap-1 align-items-baseline">
                                <div class="fw-bold">{{ $beatmap->difficulty_name }}</div>
                                <small class="text-muted">
                                    by {{ $beatmap->creator_label }}
                                </small>
                            </div>
                            <br/>
                            <small class="text-muted">
                                sr: {{ number_format($beatmap->sr, 2) }} |
                                status: {{ $beatmap->status_label ?? 'Unknown' }}
                                @if (!$beatmap->blacklisted)
                                    | ratings: {{ $beatmap->ratings->count()}}
                                @endif
                            </small>
                        </div>
                        <div class="ms-auto text-end d-flex flex-row">
                            @if (!$beatmap->blacklisted)
                                <span class="badge bg-main fs-5">{{ number_format($beatmap->weighted_avg, 2) }}</span>
                            @endif
                            @auth
                                <form method="POST" action="{{ route('ratings.update', $beatmap->id) }}"
                                      class="d-flex align-items-center gap-2 ms-3">
                                    @csrf
                                    <select name="score" class="form-select form-select-sm w-auto"
                                            onchange="this.form.submit()">
                                        <option value="">unrated</option>
                                        @foreach (range(0, 10) as $i)
                                            <option value="{{ $i }}" @selected(optional($beatmap->userRating)->score === $i)>
                                                {{ number_format($i / 2, 1) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            @endauth
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="col-md-4">
            <h4 class="mb-3">ratings</h4>
            <div id='ratings'>
                @include('partials.beatmapset.ratings')
            </div>
        </div>
    </div>
@endsection
