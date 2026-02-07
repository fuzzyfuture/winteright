<div class="list-group-item p-3 py-2">
    <b><a href="{{ route('lists.show', $list->id) }}">{{ $list->name }}</a></b>
    <div>
        <small class="text-muted">
            {{ $list->items_count }} items | {{ $list->favorites_count }} favs | last updated:
            {{ $list->updated_at?->toFormattedDateString() ?? 'never' }}
        </small>
    </div>
</div>
