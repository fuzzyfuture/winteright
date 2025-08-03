@extends('layouts.app')

@section('content')
    <h1>lists</h1>
    <div>
        {{ $lists->links() }}
    </div>
    <div class="container">
        @forelse($lists as $list)
            <div class="p-3 rounded shadow-sm mb-2 chart-beatmap-card">
                <div><a href="{{ route('lists.show', $list->id) }}">{{ $list->name }}</a></div>
                <div>
                    <small>
                        by <a href="{{ route('users.show', $list->user_id) }}">{{ $list->owner->name }}</a>
                        <a href="https://osu.ppy.sh/users/{{ $list->user_id }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           title="view on osu!"
                           class="opacity-50 small">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    </small>
                </div>
            </div>
        @empty
            <p class="text-muted">no lists found</p>
        @endforelse
    </div>
    <div>
        {{ $lists->links() }}
    </div>
@endsection
