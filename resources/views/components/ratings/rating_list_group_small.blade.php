<div class="list-group-item d-flex align-items-center ps-1 pe-2">
    <a href="{{ url('/users/' . $rating->user->id) }}" class="d-flex align-items-start flex-nowrap ms-1">
        <img src="{{ $rating->user->avatar_url }}" width="16" height="16" alt="Avatar">
        <small class="ms-2">{{ $rating->user->name }}</small>
    </a>
    <small class="ms-1">rated</small>
    <a href="{{ route('beatmaps.show', $rating->beatmap->set->id) }}" class="ms-1 d-flex">
        <small>{{ $rating->beatmap->set->title }} [{{ $rating->beatmap->difficulty_name }}]</small>
    </a>
    <small class="ms-auto text-nowrap text-muted" title="{{ $rating->updated_at }}">
        {{ $rating->updated_at->diffForHumans() }}
    </small>
    <span class="ms-2 badge bg-main fs-6"><small>{{ number_format($rating->score / 2, 1) }}</small></span>
</div>
