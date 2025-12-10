<div class="list-group-item">
    <div class="row g-2">
        <div class="col-md-3">
            <div class="audio-preview" style="background-image: url({{ $set->bg_url }})" data-playing="false">
                <audio src="{{ $set->preview_url }}"></audio>
                <div class="button-overlay">
                    <i class="bi bi-play-fill h1 mb-0"></i>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div>
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
    </div>
</div>
