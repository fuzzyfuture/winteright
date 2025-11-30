@extends('layouts.app')

@section('content')
    <h1>ratings</h1>
    <h3 class="mb-3">{{ $user->url }} - {{ $score ? number_format($score, 1) : 'all' }}</h3>
    @if ($user->hide_ratings != \App\Enums\HideRatingsOption::ALL->value || Auth::id() == $user->id)
        {{ $ratings->links() }}
        <div class="list-group mb-3">
            @forelse ($ratings as $rating)
                <x-ratings.rating_list_group :rating="$rating" />
            @empty
                <div class="text-muted">no ratings found.</div>
            @endforelse
        </div>
        {{ $ratings->links() }}
    @else
        <div class="alert alert-sm alert-primary" data-bs-theme="dark">
            this user's ratings are private.
        </div>
    @endif
@endsection
