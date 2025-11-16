@extends('layouts.app')

@section('content')
    <h1>lists</h1>
    <h3 class="mb-3">by {{ $user->url }}</h3>
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
            <x-lists.list_list_group :list="$list" />
        @empty
            <p class="text-muted">no lists found.</p>
        @endforelse
    </div>
    {{ $lists->links() }}
@endsection
