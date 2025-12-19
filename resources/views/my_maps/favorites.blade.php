@php use App\Enums\UserListItemType; @endphp
@extends('layouts.app')

@section('content')
    @include('my_maps._nav')
    {{ $sets->links() }}
    <div class="list-group">
        @foreach ($sets as $set)
            <div class="list-group-item">
                <div class="row g-2">
                    <div class="col-md-2">
                        <div class="audio-preview" style="background-image: url({{ $set->bg_url }})"
                             data-playing="false">
                            <audio src="https://b.ppy.sh/preview/{{ $set->id }}.mp3"></audio>
                            <div class="button-overlay">
                                <i class="bi bi-play-fill h1 mb-0"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="d-flex flex-row align-items-center">
                            <div>
                                <strong>{{ $set->url }}</strong>
                                <small class="text-muted d-block">
                                    by {{ $set->creator_label }}
                                </small>
                                <div class="mt-1 d-flex align-items-center gap-2">
                                    {{ $set->status_badge }}
                                    <small class="text-muted">{{ $set->date_ranked->format('Y-m-d') }}</small>
                                    <small class="text-muted">{{ $set->difficulty_spread }}</small>
                                </div>
                            </div>
                            <div class="ms-auto d-flex flex-row align-items-center">
                                <a href="{{ route('lists.add', ['item_type' => UserListItemType::BEATMAP_SET, 'item_id' => $set->id]) }}"
                                   class="ms-2 btn btn-sm btn-outline-primary p-1 py-0 opacity-50">
                                    <i class="bi bi-plus"></i><i class="bi bi-list"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    {{ $sets->links() }}
@endsection
