@extends('layouts.app')

@section('content')
    <h1>lists</h1>
    <h3 class="mb-3">{{ $user->name }}</h3>
    @if(Auth::check() && Auth::id() == $user->id)
        <div class="mb-3">
            <a href="{{ route('lists.new') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus"></i>
                new
            </a>
        </div>
    @endif
    {{ $lists->links() }}
    <div class="list-group mb-3">
        @forelse($lists as $list)
            <div class="list-group-item p-3 py-2">
                <b><a href="{{ route('lists.show', $list->id) }}">{{ $list->name }}</a></b>
                <div>
                    <small class="text-muted">
                        {{ $list->items_count }} items | last updated: {{ $list->updated_at?->toFormattedDateString() ?? 'never' }}
                    </small>
                </div>
            </div>
        @empty
            <p class="text-muted">no lists found.</p>
        @endforelse
    </div>
    {{ $lists->links() }}
@endsection
