@extends('layouts.app')

@section('content')
    <div class="d-flex flex-row align-items-center">
        <h1>lists - edit</h1>
        @can('delete', $list)
            {{ html()->form('DELETE', route('lists.delete', $list->id))->class('ms-auto')->attribute('onsubmit', 'return confirm(\'are you sure you want to delete this list?\')')->open() }}
                {{ html()->submit('<i class="bi bi-trash"></i> delete')->class('btn btn-outline-primary') }}
            {{ html()->form()->close() }}
        @endcan
    </div>
    <h2 class="mb-3">editing <a href="{{ route('lists.show', $list->id) }}">{{ $list->name }}</a></h2>
    {{ html()->form('POST', route('lists.edit.post', $list->id))->open() }}
        <div class="mb-3">
            {{ html()->label('name *', 'name')->class('form-label') }}
            {{ html()->text('name', $list->name)->class('form-control') }}
        </div>
        <div class="mb-3">
            {{ html()->label('description', 'description')->class('form-label') }}
            {{ html()->textarea('description', $list->description)->class('form-control') }}
        </div>
        <div class="mb-4">
            {{ html()->label('visibility', 'is_public')->class('form-label') }}
            {{ html()->select('is_public', [1 => 'public', 0 => 'private'], $list->is_public)->class('form-select') }}
        </div>
    {{ html()->submit('submit')->class('btn btn-primary') }}
@endsection
