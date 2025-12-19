@php use App\Enums\UserListItemType; @endphp
@extends('layouts.app')

@section('content')
    @include('my_maps._nav')
    <div class="list-group">
        @foreach ($beatmaps as $map)
            <div class="list-group-item">
                <div class="row g-2">
                    <div class="col-md-2">
                        <div class="audio-preview" style="background-image: url({{ $map->bg_url }})"
                             data-playing="false">
                            <audio src="https://b.ppy.sh/preview/{{ $map->set->id }}.mp3"></audio>
                            <div class="button-overlay">
                                <i class="bi bi-play-fill h1 mb-0"></i>
                            </div>
                            <div class="mode-icon-overlay">
                                {{ $map->mode_icon }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="d-flex flex-row align-items-center">
                            <div>
                                <strong>{{ $map->url }}</strong>
                                <small class="text-muted d-block">
                                    by {{ $map->creator_label }}
                                </small>
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    {{ $map->status_badge }}
                                    <small class="text-muted">{{ $map->set->date_ranked->format('Y-m-d') }}</small>
                                </div>
                            </div>
                            <div class="ms-auto d-flex flex-row align-items-center">
                                <a href="{{ route('lists.add', ['item_type' => UserListItemType::BEATMAP, 'item_id' => $map->id]) }}"
                                   class="ms-2 btn btn-sm btn-outline-primary p-1 py-0 opacity-50">
                                    <i class="bi bi-plus"></i><i class="bi bi-list"></i>
                                </a>
                                {{ html()->form('POST', route('ratings.update', $map->id))->class('d-flex align-items-center gap-2 ms-3')->open() }}
                                {{ html()->select('score', $ratingOptions, $map->userRating?->score ?? '')->class('form-select form-select-sm w-auto')->attribute('onchange', 'this.form.submit()')->attribute('autocomplete', 'off') }}
                                {{ html()->form()->close() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
