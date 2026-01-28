<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-0">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">winteright</a>
        <button class="navbar-toggler ml-auto px-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('charts.index') }}">charts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('search.index') }}">search</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('lists.index') }}">lists</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                @if (Auth::check())
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <img src="{{ Auth::user()->avatar_url }}" class="me-1" width="16" height="16" alt="Avatar">
                            <span class="text-body">{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="{{ route('users.show', Auth::id()) }}" class="dropdown-item">profile</a></li>
                            <li><a href="{{ route('settings.show') }}" class="dropdown-item">settings</a></li>
                            <li><a href="{{ route('my_maps.recent') }}" class="dropdown-item">recently played</a></li>
                            <li><a href="{{ route('affinities.mappers') }}" class="dropdown-item">affinities</a></li>
                            <li><hr class="dropdown-divider" /></li>
                            <li class="dropdown-item-text">enabled modes</li>
                            <li class="dropdown-item-text" onclick="event.stopPropagation()">
                                {{ html()->form('POST', route('settings.enabled_modes'))->class('')->open() }}
                                    <div class="form-check">
                                        {{ html()->checkbox('osu', Auth::user()->hasModeEnabled(\App\Enums\BeatmapMode::OSU))->class('form-check-input')->id('navbar-osu') }}
                                        {{ html()->label('osu', 'navbar-osu')->class('form-check-label') }}
                                    </div>
                                    <div class="form-check">
                                        {{ html()->checkbox('taiko', Auth::user()->hasModeEnabled(\App\Enums\BeatmapMode::TAIKO))->class('form-check-input')->id('navbar-taiko') }}
                                        {{ html()->label('taiko', 'navbar-taiko')->class('form-check-label') }}
                                    </div>
                                    <div class="form-check">
                                        {{ html()->checkbox('fruits', Auth::user()->hasModeEnabled(\App\Enums\BeatmapMode::FRUITS))->class('form-check-input')->id('navbar-fruits') }}
                                        {{ html()->label('fruits', 'navbar-fruits')->class('form-check-label') }}
                                    </div>
                                    <div class="form-check">
                                        {{ html()->checkbox('mania', Auth::user()->hasModeEnabled(\App\Enums\BeatmapMode::MANIA))->class('form-check-input')->id('navbar-mania') }}
                                        {{ html()->label('mania', 'navbar-mania')->class('form-check-label') }}
                                    </div>
                                    {{ html()->submit('save')->class('btn btn-sm btn-primary mt-2') }}
                                {{ html()->form()->close() }}
                            </li>
                            <li><hr class="dropdown-divider" /></li>
                            <li>
                                {{ html()->form('POST', route('auth.logout'))->open() }}
                                {{ html()->submit('logout')->class('dropdown-item') }}
                                {{ html()->form()->close() }}
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a href="{{ route('auth.login') }}" class="nav-link" >login with osu!</a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.dropdown-item-text form').forEach(function(form) {
            form.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    });
</script>
@endsection
