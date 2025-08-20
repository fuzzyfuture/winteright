@extends('layouts.app')

@section('content')
    <div class="d-flex flex-row align-items-center">
        <h1>lists</h1>
        @can('update', $list)
            <a href="{{ route('lists.edit', $list->id) }}" class="ms-auto btn btn-outline-primary">
                <i class="bi bi-pencil"></i>
                edit
            </a>
        @endcan
    </div>
    <h2>
        {{ $list->name }} by <a href="{{ url('/users/'.$list->user_id) }}">{{ $list->owner->name }}</a>
        <a href="https://osu.ppy.sh/users/{{ $list->user_id }}"
           target="_blank"
           rel="noopener noreferrer"
           title="view on osu!"
           class="opacity-50 small">
            <i class="bi bi-box-arrow-up-right"></i>
        </a>
    </h2>
    <div class="mb-3">
        <small class="text-muted">
            created: {{ $list->created_at->toFormattedDateString() }}<br/>
            last updated: {{ $list->updated_at ? $list->updated_at->toFormattedDateString() : 'never' }}
        </small>
    </div>
    <div class="mb-4">
        {{ $list->description }}
    </div>
    <div>
        {{ $items->links() }}
    </div>
    @forelse($items as $item)
        <div class="row p-0 rounded shadow-sm mb-2 chart-beatmap-card">
            @if($item->isUser())
                <div class="col-md-2 p-2">
                    <div class="chart-beatmap-img w-100 h-100"
                         style="background-image: url('https://a.ppy.sh/{{ $item->item_id }}');">
                    </div>
                </div>
                <div class="col-md-10 p-3 ps-1">
                    <div><small class="text-muted">user</small></div>
                    @if ($item->item)
                        <h4><a href="{{ route('users.show', $item->item_id) }}">{{ $item->item->name }}</a></h4>
                    @else
                        <h4>{{ $item->item_id }}<a href="https://osu.ppy.sh/users/{{ $item->item_id }}"></a></h4>
                    @endif
                    <div>{{ $item->description }}</div>
                </div>
            @elseif($item->isBeatmap())
                <div class="col-md-2 p-2">
                    <div class="chart-beatmap-img w-100 h-100"
                         style="background-image: url('{{ $item->item->bg_url }}');">
                    </div>
                </div>
                <div class="col-md-10 p-3 ps-1">
                    <div><small class="text-muted">beatmap</small></div>
                    <h5 class="mb-1">{{ $item->item->url }}</h5>
                    <div class="mb-2">mapped by: {{ $item->item->creator_label }}</div>
                    <div>{{ $item->description }}</div>
                </div>
            @elseif($item->isBeatmapSet())
                <div class="col-md-2 p-2">
                    <div class="chart-beatmap-img w-100 h-100"
                         style="background-image: url('{{ $item->item->bg_url }}');">
                    </div>
                </div>
                <div class="col-md-10 p-3 ps-1">
                    <div><small class="text-muted">beatmap set</small></div>
                    <h5 class="mb-1">{{ $item->item->url }}</h5>
                    <div class="mb-2">mapped by: {{ $item->item->creator_label }}</div>
                    <div>{{ $item->description }}</div>
                </div>
            @endif
        </div>
    @empty
        <p>no items found</p>
    @endforelse
    <div>
        {{ $items->links() }}
    </div>
@endsection
