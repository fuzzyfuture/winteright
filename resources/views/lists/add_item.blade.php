@extends('layouts.app')

@section('content')
    @if(empty($listOptions))
        <div class="alert alert-primary" data-bs-theme="dark">
            you don't have any lists yet! <a href="{{ route('lists.new') }}">click here</a> to create your first list.
        </div>
    @endif
    <h1>lists</h1>
    <h2>new item</h2>
    {{ html()->form('POST', route('lists.add'))->open() }}
        <div class="mb-3">
            {{ html()->label('list', 'list_id')->class('form-label') }}
            {{ html()->select('list_id', $listOptions, $listId)->class('form-select') }}
        </div>
        <div class="mb-3">
            {{ html()->label('item type', 'item_type')->class('form-label') }}
            {{ html()->select('item_type', $itemTypeOptions, $itemType)->class('form-select') }}
        </div>
        <div class="mb-3">
            {{ html()->label('item id *', 'item_id')->class('form-label') }}
            {{ html()->text('item_id', $itemId)->class('form-control') }}
        </div>
        <div class="mb-3">
            {{ html()->label('description', 'description')->class('form-label') }}
            {{ html()->textarea('description')->class('form-control') }}
        </div>
        <div class="mb-4">
            {{ html()->label('order *', 'order')->class('form-label') }} <small class="ms-2 text-muted">note: lists are currently sorted by descending order. more options will be available in the future!</small>
            {{ html()->text('order', 0)->class('form-control') }}
        </div>
        {{ html()->submit('submit')->class('btn btn-primary') }}
    {{ html()->form()->close() }}
@endsection
