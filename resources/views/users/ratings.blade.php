@extends('layouts.app')

@section('content')
    <h1>ratings</h1>
    <h3 class="mb-3">{{ $user->url }} - {{ $score ? number_format($score, 1) : 'all' }}</h3>
    {{ $ratings->links() }}
    <div class="list-group mb-3">
        @forelse ($ratings as $rating)
            <div class="list-group-item d-flex align-items-center p-3">
                <img src="https://assets.ppy.sh/beatmaps/{{ $rating->beatmap->set->id }}/covers/cover.jpg" alt="beatmap bg" width="175" />
                <div class="ms-3">
                    <strong>{{ $rating->beatmap->url }}</strong>
                    <small class="text-muted d-block">
                        by {{ $rating->beatmap->creator_label }}
                    </small>
                </div>
                <div class="ms-auto text-muted text-center">
                    <span class="badge bg-main fs-6">{{ number_format($rating->score / 2, 1) }}</span><br/>
                    <small>{{ $rating->updated_at->format('Y-m-d') }}</small>
                </div>
            </div>
        @empty
            <div class="text-muted">No ratings found.</div>
        @endforelse
    </div>
    {{ $ratings->links() }}
@endsection
