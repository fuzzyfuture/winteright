@extends('layouts.app')

@section('content')
    <h1>affinities</h1>
    <h2 class="mb-3">mappers</h2>
    {{ $topRatedMappers->links() }}
    <div class="list-group mb-3">
        @forelse ($topRatedMappers as $mapper)
            <x-users.top_rated_mapper_list_group :mapper="$mapper" />
        @empty
            <div class="text-muted">no ratings found.</div>
        @endforelse
    </div>
    {{ $topRatedMappers->links() }}
@endsection
