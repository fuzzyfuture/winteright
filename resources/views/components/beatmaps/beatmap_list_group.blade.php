<div class="list-group-item">
    <div class="row g-2">
        <div class="col-md-3">
            <div class="audio-preview" style="background-image: url({{ $map->bg_url }})" data-playing="false">
                <audio src="{{ $map->preview_url }}"></audio>
                <div class="button-overlay">
                    <i class="bi bi-play-fill h1 mb-0"></i>
                </div>
                <div class="mode-icon-overlay">
                    {{ $map->mode_icon }}
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div>
                <strong>{{ $map->link }}</strong>
                <small class="text-muted d-block">
                    by {{ $map->creator_label }}
                </small>
                <div class="mt-1 d-flex align-items-center gap-2">
                    {{ $map->status_badge }}
                    <small class="text-muted">{{ $map->set->date_ranked->format('Y-m-d') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>
