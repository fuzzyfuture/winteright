<div class="list-group-item d-flex align-items-center p-2 pe-3">
    <img src="https://assets.ppy.sh/beatmaps/{{ $rating->beatmap->set->id }}/covers/cover.jpg" alt="beatmap bg" width="175" />
    <div class="ms-2">
        <strong>{{ $rating->beatmap->url }}</strong>
        <small class="text-muted d-block">
            by {{ $rating->beatmap->creator_label }}
        </small>
    </div>
    <div class="ms-auto ps-3 text-muted text-center">
        <span class="badge bg-main fs-6">{{ number_format($rating->score / 2, 1) }}</span><br/>
        <small>{{ $rating->updated_at->format('Y-m-d') }}</small>
    </div>
</div>
