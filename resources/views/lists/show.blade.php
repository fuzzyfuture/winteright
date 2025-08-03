@extends('layouts.app')

@section('content')
    <h1>lists</h1>
    <h2>
        {{ $list->name }} by <a href="{{ url('/users/'.$list->user_id) }}">{{ $list->owner->name }}</a>
        <a href="https://osu.ppy.sh/users/{{ $list->user_id }}"
           target="_blank"
           rel="noopener noreferrer"
           title="view on osu!"
           class="opacity-50 small">
            <i class="bi bi-box-arrow-up-right"></i>
        </a>
    </h2>
    <div class="mb-3">
        <small class="text-muted">
            created: {{ $list->created_at->toFormattedDateString() }}<br/>
            last updated: {{ $list->updated_at ? $list->updated_at->toFormattedDateString() : 'never' }}
        </small>
    </div>
    <div>
        {{ $list->description }}
    </div>
@endsection
