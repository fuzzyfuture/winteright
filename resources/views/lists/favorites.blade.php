@extends('layouts.app')

@section('content')
    <h1>lists</h1>
    <h2 class="mb-3">my favorites</h2>
    {{ $lists->links() }}
    <div class="container">
        @forelse($lists as $list)
            <div class="p-3 rounded shadow-sm mb-2 chart-beatmap-card d-flex">
                <div>
                    <h5 class="mb-1"><a href="{{ route('lists.show', $list->id) }}">{{ $list->name }}</a></h5>
                    <div><small>by {{ $list->owner->url }}</small></div>
                    <div>
                        <small class="text-muted">
                            {{ $list->items_count }} items | {{ $list->favorites_count }} favs | last updated: {{ $list->updated_at?->toFormattedDateString() ?? 'never' }}
                        </small>
                    </div>
                </div>
                {{ html()->form('POST', route('lists.unfavorite', $list->id))->class('ms-auto')->open() }}
                    {{ html()->submit('<i class="bi bi-heartbreak"></i> unfavorite')->class('ms-1 btn btn-outline-primary') }}
                {{ html()->form()->close() }}
            </div>
        @empty
            <p class="text-muted">no lists found</p>
        @endforelse
    </div>
    {{ $lists->links() }}
@endsection
