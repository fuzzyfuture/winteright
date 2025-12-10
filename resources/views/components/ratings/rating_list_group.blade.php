<div class="list-group-item">
    <div class="row g-2">
        <div class="col-md-3">
            <div class="audio-preview" style="background-image: url({{ $rating->beatmap->bg_url }})"  data-playing="false">
                <audio src="{{ $rating->beatmap->preview_url }}"></audio>
                <div class="button-overlay">
                    <i class="bi bi-play-fill h1 mb-0"></i>
                </div>
                <div class="mode-icon-overlay">
                    {{ $rating->beatmap->mode_icon }}
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="d-flex align-items-center">
                <div>
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
        </div>
    </div>
</div>
