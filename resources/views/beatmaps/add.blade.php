@extends('layouts.app')

@section('content')
    <h1 class="mb-3">add beatmap set</h1>
    {{ html()->form('POST', route('beatmaps.add.post'))->open() }}
        <div class="mb-3">
            {{ html()->label('url *', 'url')->class('form-label') }}
            {{ html()->text('url')->class('form-control') }}
        </div>
        {{ html()->submit('submit')->class('btn btn-primary') }}
    {{ html()->form()->close() }}
@endsection
