@extends('layouts.app')

@section('content')
    <h1>mapped guest difficulties</h1>
    <h3 class="mb-3">{{ $user->url }}</h3>
    {{ $gds->links() }}
    <div class="list-group mb-3">
        @forelse ($gds as $gd)
            <x-beatmaps.beatmap_list_group :map="$gd" />
        @empty
            <div class="text-muted">no mapsets found.</div>
        @endforelse
    </div>
    {{ $gds->links() }}
@endsection
