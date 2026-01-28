@extends('layouts.app')

@section('content')
    <h1>lists</h1>
    <h2 class="mb-3">
        editing <a href="{{ route('lists.show', $list->id) }}">{{ $list->name }}</a> items
    </h2>
    <div>
        {{ $items->links() }}
    </div>
    <div class="container">
        @forelse($items as $item)
            <div class="row p-0 rounded shadow-sm mb-2 chart-beatmap-card">
                @if($item->isUser())
                    <div class="col-md-2 p-2">
                        <div class="chart-beatmap-img w-100 h-100"
                             style="background-image: url('{{ $item->item->avatar_url }}');">
                        </div>
                    </div>
                    <div class="col-md-10 p-3 ps-1">
                        <div class="d-flex">
                            <div>
                                <div><small class="text-muted">user</small></div>
                                @if ($item->item)
                                    <h5><a href="{{ route('users.show', $item->item_id) }}">{{ $item->item->name }}</a></h5>
                                @else
                                    <h5>{{ $item->item_id }}<a href="{{ $item->item->profile_url }}"></a></h5>
                                @endif
                            </div>
                            {{ html()->form('DELETE', route('lists.delete-item', $item->id))->class('ms-auto')->attribute('onsubmit', 'return confirm(\'are you sure you want to delete this item?\')')->open() }}
                                {{ html()->submit('<i class="bi bi-trash"></i> delete')->class('btn btn-primary') }}
                            {{ html()->form()->close() }}
                        </div>
                        {{ html()->form('POST', route('lists.edit-item.post', $item->id))->open() }}
                            <div class="mb-2">
                                {{ html()->label('description', 'description')->class('form-label') }}
                                {{ html()->textarea('description', $item->description)->class('form-control') }}
                            </div>
                            <div class="mb-3">
                                {{ html()->label('order *', 'order')->class('form-label') }}
                                {{ html()->text('order', $item->order)->class('form-control') }}
                            </div>
                            {{ html()->submit('save')->class('btn btn-primary') }}
                        {{ html()->form()->close() }}
                    </div>
                @elseif($item->isBeatmap())
                    <div class="col-md-2 p-2">
                        <div class="chart-beatmap-img w-100 h-100"
                             style="background-image: url('{{ $item->item->bg_url }}');">
                        </div>
                    </div>
                    <div class="col-md-10 p-3 ps-1">
                        <div class="d-flex">
                            <div>
                                <div><small class="text-muted">beatmap</small></div>
                                <h5 class="mb-1">{{ $item->item->link }}</h5>
                                <div class="mb-2">mapped by: {{ $item->item->creator_label }}</div>
                            </div>
                            {{ html()->form('DELETE', route('lists.delete-item', $item->id))->class('ms-auto')->attribute('onsubmit', 'return confirm(\'are you sure you want to delete this item?\')')->open() }}
                                {{ html()->submit('<i class="bi bi-trash"></i> delete')->class('btn btn-primary') }}
                            {{ html()->form()->close() }}
                        </div>
                        {{ html()->form('POST', route('lists.edit-item.post', $item->id))->name($item->id)->open() }}
                            <div class="mb-2">
                                {{ html()->label('description', 'description')->class('form-label') }}
                                {{ html()->textarea('description', $item->description)->class('form-control') }}
                            </div>
                            <div class="mb-3">
                                {{ html()->label('order *', 'order')->class('form-label') }}
                                {{ html()->text('order', $item->order)->class('form-control') }}
                            </div>
                            {{ html()->submit('save')->class('btn btn-primary') }}
                        {{ html()->form()->close() }}
                    </div>
                @elseif($item->isBeatmapSet())
                    <div class="col-md-2 p-2">
                        <div class="chart-beatmap-img w-100 h-100"
                             style="background-image: url('{{ $item->item->bg_url }}');">
                        </div>
                    </div>
                    <div class="col-md-10 p-3 ps-1">
                        <div class="d-flex">
                            <div>
                                <div><small class="text-muted">beatmap set</small></div>
                                <h5 class="mb-1">{{ $item->item->link }}</h5>
                                <div class="mb-3">mapped by: {{ $item->item->creator_label }}</div>
                            </div>
                            {{ html()->form('DELETE', route('lists.delete-item', $item->id))->class('ms-auto')->attribute('onsubmit', 'return confirm(\'are you sure you want to delete this item?\')')->open() }}
                                {{ html()->submit('<i class="bi bi-trash"></i> delete')->class('btn btn-primary') }}
                            {{ html()->form()->close() }}
                        </div>
                        {{ html()->form('POST', route('lists.edit-item.post', $item->id))->open() }}
                            <div class="mb-2">
                                {{ html()->label('description', 'description')->class('form-label') }}
                                {{ html()->textarea('description', $item->description)->class('form-control') }}
                            </div>
                            <div class="mb-3">
                                {{ html()->label('order *', 'order')->class('form-label') }}
                                {{ html()->text('order', $item->order)->class('form-control') }}
                            </div>
                            {{ html()->submit('save')->class('btn btn-primary') }}
                        {{ html()->form()->close() }}
                    </div>
                @endif
            </div>
        @empty
            <p>no items found</p>
        @endforelse
    </div>
    <div>
        {{ $items->links() }}
    </div>
@endsection
