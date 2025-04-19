@extends('layouts.app')

@section('content')
    <div class="text-center">
        <h1 class="display-4">winteright</h1>
        <p class="lead">a community-powered beatmap rating platform for osu!</p>
    </div>
    <div class="row justify-content-center mt-5 mb-4 text-center">
        <div class="col-md-3">
            <h2 class="text-primary">{{ number_format($stats['beatmapSets']) }}</h2>
            <p class="text-muted">beatmap sets</p>
        </div>
        <div class="col-md-3">
            <h2 class="text-primary">{{ number_format($stats['beatmaps']) }}</h2>
            <p class="text-muted">beatmaps</p>
        </div>
        <div class="col-md-3">
            <h2 class="text-primary">{{ number_format($stats['ratings']) }}</h2>
            <p class="text-muted">ratings</p>
        </div>
    </div>
    <div>
        <h3>Why "winteright"?</h3>
        <p>I was playing <a href="https://osu.ppy.sh/beatmapsets/1281337#osu/2661429" target="_blank">this map</a> when I decided I want to actually start this project.</p>
    </div>
@endsection
