@extends('layouts.app')

@section('content')
    <h1>mapped beatmap sets</h1>
    <h3 class="mb-3">{{ $user->link }}</h3>
    {{ $mapsets->links() }}
    <div class="list-group mb-3">
        @forelse ($mapsets as $mapset)
            <x-beatmaps.beatmap_set_list_group :set="$mapset" />
        @empty
            <div class="text-muted">no mapsets found.</div>
        @endforelse
    </div>
    {{ $mapsets->links() }}
@endsection
