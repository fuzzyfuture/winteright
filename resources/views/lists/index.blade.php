@extends('layouts.app')

@section('content')
    <h1 class="mb-3">lists</h1>
    <div class="card mb-4">
        <div class="card-body">
            {{ html()->form('GET', route('lists.index'))->open() }}
                <div class="row mb-3">
                    <div class="col-md-6">
                        {{ html()->label('name contains', 'name')->class('form-label') }}
                        {{ html()->text('name', $name)->class('form-control') }}
                    </div>
                    <div class="col-md-6">
                        {{ html()->label('creator name is', 'creator_name')->class('form-label') }}
                        {{ html()->text('creator_name', $creatorName)->class('form-control mb-3') }}
                    </div>
                </div>
                {{ html()->submit('search')->class('btn btn-primary float-end') }}
            {{ html()->form()->close() }}
        </div>
    </div>
    @auth
        <div class="mb-3 d-flex flex-wrap gap-1">
            <a href="{{ route('lists.new') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus"></i>
                new
            </a>
            <a href="{{ route('users.lists', Auth::id()) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-person"></i>
                my lists
            </a>
            <a href="{{ route('lists.favorites') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-heart"></i>
                my favs
            </a>
        </div>
    @endauth
    <div>
        {{ $lists->links() }}
    </div>
    <div class="container">
        @forelse($lists as $list)
            <div class="p-3 rounded shadow-sm mb-2 chart-beatmap-card">
                <h5 class="mb-1"><a href="{{ route('lists.show', $list->id) }}">{{ $list->name }}</a></h5>
                <div><small>by {{ $list->owner->link }}</small></div>
                <div>
                    <small class="text-muted">
                        {{ $list->items_count }} items | {{ $list->favorites_count }} favs | last updated: {{ $list->updated_at?->toFormattedDateString() ?? 'never' }}
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
