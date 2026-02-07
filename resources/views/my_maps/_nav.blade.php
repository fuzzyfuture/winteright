<div class="d-flex flex-row align-items-center">
    <h1 class="mb-3">my maps</h1>
    @if ($current != 'recent')
        <div class="ms-auto">
            {{ html()->form('POST', route('my_maps.update'))->class('mb-3')->open() }}
            {{ html()->submit('<i class="bi bi-arrow-repeat"></i> update')->class('btn btn-primary') }}
            {{ html()->form()->close() }}
        </div>
    @endif
</div>
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ $current == 'recent' ? 'active' : '' }}" href="{{ route('my_maps.recent') }}">recently
            played</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $current == 'favorites' ? 'active' : '' }}"
            href="{{ route('my_maps.favorites') }}">favorites</a>
    </li>
</ul>
