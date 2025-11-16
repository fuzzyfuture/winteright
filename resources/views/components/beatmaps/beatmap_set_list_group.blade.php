<div class="list-group-item d-flex align-items-center p-2 pe-3">
    <img src="https://assets.ppy.sh/beatmaps/{{ $set->id }}/covers/cover.jpg" alt="beatmap bg" width="175" />
    <div class="ms-2">
        <strong>{{ $set->url }}</strong>
        <small class="text-muted d-block">
            by {{ $set->creator_label }}
        </small>
        <small class="text-muted d-flex align-items-center gap-2 mt-1">
            {{ $set->status_badge }}
            <span class="text-muted">{{ $set->date_ranked?->format('Y-m-d') }}</span>
            {{ $set->difficultySpread }}
        </small>
    </div>
</div>
