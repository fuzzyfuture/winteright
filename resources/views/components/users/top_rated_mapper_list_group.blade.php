<div class="list-group-item d-flex align-items-center p-3 py-2">
    <img class="me-2" src="{{ $mapper->getAvatarUrl() }}" width="30" height="30" alt="avatar for {{ $mapper->getName() }}"/>
    <b class="text-muted">{{ $mapper->getLink() }}</b>
    <div class="ms-auto text-muted text-center">
        <div class="badge bg-main fs-6">{{ number_format($mapper->averageScore / 2, 2) }}</div>
        <div>
            <small>{{ $mapper->ratingCount }} ratings</small>
        </div>
    </div>
</div>
