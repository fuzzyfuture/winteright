@extends('layouts.app')

@section('content')
    <h1 class="mb-3">lists - new</h1>
    {{ html()->form('POST', route('lists.new.post'))->open() }}
        <div class="mb-3">
            {{ html()->label('name *', 'name')->class('form-label') }}
            {{ html()->text('name')->class('form-control') }}
        </div>
        <div class="mb-3">
            {{ html()->label('description', 'description')->class('form-label') }}
            {{ html()->textarea('description')->class('form-control') }}
        </div>
        <div class="mb-4">
            {{ html()->label('visibility', 'is_public')->class('form-label') }}
            {{ html()->select('is_public', [1 => 'public', 0 => 'private'])->class('form-select') }}
        </div>
        {{ html()->submit('submit')->class('btn btn-primary') }}
    {{ html()->form()->close() }}
@endsection
