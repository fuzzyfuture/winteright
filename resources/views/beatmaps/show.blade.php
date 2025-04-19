@extends('layouts.app')

@section('content')
    <div class="mb-4 text-center">
        <h1 class="display-5">{{ $beatmapSet->artist }} - {{ $beatmapSet->title }}</h1>
        <p class="text-muted">
            mapset by
            <strong>
                @if ($beatmapSet->creator_label)
                    <a href="{{ url('/users/' . $beatmapSet->creator_label['osu_id']) }}">{{ $beatmapSet->creator_label['name'] }}</a>
                @else
                    {{ $beatmapSet->creator_id }}
                @endif
            </strong>
        </p>
        <img src="https://assets.ppy.sh/beatmaps/{{ $beatmapSet->set_id }}/covers/cover.jpg"
             class="img-fluid rounded shadow-sm mb-4"
             style="max-height: 300px; object-fit: cover;"
             alt="{{ $beatmapSet->artist }} - {{ $beatmapSet->title }} banner">
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <h4>Info</h4>
            <ul class="list-unstyled">
                <li><strong>Genre:</strong> {{ $beatmapSet->genre_label }}</li>
                <li><strong>Language:</strong> {{ $beatmapSet->language_label }}</li>
                <li><strong>Storyboard:</strong> {{ $beatmapSet->has_storyboard ? 'Yes' : 'No' }}</li>
                <li><strong>Video:</strong> {{ $beatmapSet->has_video ? 'Yes' : 'No' }}</li>
            </ul>
        </div>

        <div class="col-md-6">
            <h4>osu! link</h4>
            <a href="https://osu.ppy.sh/beatmapsets/{{ $beatmapSet->set_id }}" target="_blank">
                view on osu! <i class="bi bi-box-arrow-up-right"></i>
            </a>
        </div>
    </div>

    <hr>

    <h3 class="mb-3">Difficulties</h3>
    <div class="list-group">
        @foreach ($beatmapSet->beatmaps as $beatmap)
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <div class="d-inline-flex gap-2 mb-1 align-items-baseline">
                        <h5 class="mb-0">{{ $beatmap->difficulty_name }}</h5>
                        <small class="text-muted">
                            @php
                                $labels = $creatorLabels[$beatmap->beatmap_id] ?? [];
                                $isRedundant =
                                    count($labels) === 1 &&
                                    isset($labels[0]['osu_id'], $beatmap->set->creator_id) &&
                                    $labels[0]['osu_id'] === $beatmap->set->creator_id;
                            @endphp

                            @if ($labels && !$isRedundant)
                                by
                                @foreach ($labels as $index => $creator)
                                    @if ($creator['name'])
                                        <a href="{{ url('/users/' . $creator['osu_id']) }}">{{ $creator['name'] }}</a>
                                    @else
                                        {{ $creator['osu_id'] }}
                                    @endif
                                    {{ $index < count($labels) - 1 ? ', ' : '' }}
                                @endforeach
                            @endif
                        </small>
                    </div> <br />
                    <small class="text-muted">
                        sr: {{ number_format($beatmap->sr, 2) }} |
                        status: {{ $beatmap->status_label ?? 'Unknown' }} |
                        ratings: {{ $beatmap->ratings->count() }}
                    </small>
                </div>
                @auth
                    <div class="text-end">
                        <form method="POST" action="{{ route('ratings.update', $beatmap->id) }}" class="d-flex align-items-center gap-2">
                            @csrf
                            <select name="score" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                <option value="">Unrated</option>
                                @foreach (range(0, 10) as $i)
                                    <option value="{{ $i }}" @selected(optional($beatmap->userRating)->score === $i)>
                                        {{ number_format($i / 2, 1) }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                @endauth
            </div>
        @endforeach
    </div>
@endsection
