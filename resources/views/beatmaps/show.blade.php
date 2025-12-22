@php use App\Enums\UserListItemType; @endphp
@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <h1>{{ $beatmapSet->title }}</h1>
            <h3>{{ $beatmapSet->artist }}</h3>
            <p class="text-muted">
                mapset by
                <strong>{!! $beatmapSet->creator_label !!}</strong>
            </p>
            <div class="audio-preview rounded shadow-sm mb-4"
                 style="height: 175px; background-image: url({{ $beatmapSet->bg_url }})">
                <audio src="{{ $beatmapSet->preview_url }}"></audio>
                <div class="button-overlay">
                    <i class="bi bi-play-fill h1 mb-0"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <h4>info</h4>
            <ul class="list-unstyled">
                <li><strong>date ranked:</strong> {{ $beatmapSet->date_ranked->toFormattedDateString() }}</li>
                <li><strong>genre:</strong> {{ $beatmapSet->genre_label }}</li>
                <li><strong>language:</strong> {{ $beatmapSet->language_label }}</li>
                <li><strong>storyboard:</strong> {{ $beatmapSet->has_storyboard ? 'yes' : 'no' }}</li>
                <li><strong>video:</strong> {{ $beatmapSet->has_video ? 'yes' : 'no' }}</li>
            </ul>
            <h4 class="mb-3">osu! links</h4>
            <a class="btn btn-sm btn-outline-primary w-100 mb-2" href="{{ $beatmapSet->info_url }}" target="_blank">
                <i class="bi bi-info-circle me-1"></i> beatmap info
            </a>
            <a class="btn btn-sm btn-outline-primary w-100 mb-2" href="{{ $beatmapSet->direct_url }}">
                <i class="bi bi-download me-1"></i> osu!direct
            </a>
        </div>
        <div class="col-md-3">
            <div class="d-flex">
                @auth
                    <a href="{{ route('lists.add', ['item_type' => UserListItemType::BEATMAP_SET, 'item_id' => $beatmapSet->id]) }}"
                       class="ms-auto btn btn-outline-primary">
                        <i class="bi bi-plus"></i><i class="bi bi-list"></i>
                        add to list
                    </a>
                @endauth
            </div>
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
                @php($hiddenCount = 0)
                @foreach ($beatmapSet->beatmaps as $beatmap)
                    @if(Auth::check() && !Auth::user()->hasModeEnabled($beatmap->mode))
                        @php($hiddenCount++)
                        @continue
                    @endif
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
                                {{ $beatmap->status_label }}
                                @if (!$beatmap->blacklisted)
                                    | ratings: {{ $beatmap->ratings->count()}}
                                @endif
                            </small>
                        </div>
                        <div class="ms-auto text-end d-flex flex-row align-items-center">
                            @auth
                                <a href="{{ route('lists.add', ['item_type' => UserListItemType::BEATMAP, 'item_id' => $beatmap->id]) }}"
                                   class="ms-2 btn btn-sm btn-outline-primary p-1 py-0 opacity-50">
                                    <i class="bi bi-plus"></i><i class="bi bi-list"></i>
                                </a>
                            @endauth
                            @if (!$beatmap->blacklisted)
                                <span class="badge bg-main fs-5 ms-3">{{ number_format($beatmap->weighted_avg, 2) }}</span>
                            @endif
                            @auth
                                {{ html()->form('POST', route('ratings.update', $beatmap->id))->class('d-flex align-items-center gap-2 ms-3')->open() }}
                                    {{ html()->select('score', $ratingOptions, $beatmap->userRating?->score ?? '')->class('form-select form-select-sm w-auto')->attribute('onchange', 'this.form.submit()') }}
                                {{ html()->form()->close() }}
                            @endauth
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($hiddenCount > 0)
                <div class="mt-2">
                    <small class="text-muted">{{ $hiddenCount }} difficulties from other modes hidden</small>
                </div>
            @endif
        </div>
        <div class="col-md-4">
            <h4 class="mb-3">ratings</h4>
            <div id='ratings'>
                @include('beatmaps._ratings')
            </div>
        </div>
    </div>
@endsection
