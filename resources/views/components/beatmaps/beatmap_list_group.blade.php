<div class="list-group-item d-flex align-items-center p-2 pe-3">
    <img src="https://assets.ppy.sh/beatmaps/{{ $map->set->id }}/covers/cover.jpg" alt="beatmap bg" width="175" />
    <div class="ms-2">
        <strong>{{ $map->url }}</strong>
        <small class="text-muted d-block">
            by {{ $map->creator_label }}
        </small>
        <div class="mt-1 d-flex align-items-center gap-2">
            {{ $map->status_badge }}
            <small class="text-muted">{{ $map->set->date_ranked->format('Y-m-d') }}</small>
        </div>
    </div>
</div>
