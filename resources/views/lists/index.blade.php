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
    <div class="mb-3">
        <a href="{{ route('lists.new') }}" class="btn btn-sm btn-outline-primary">new</a>
    </div>
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
