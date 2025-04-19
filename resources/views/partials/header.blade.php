<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">winteright</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}">home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('charts.index') ? 'active' : '' }}" href="{{ route('charts.index') }}">charts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">search</a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto">
                @if (Auth::check())
                    <li class="nav-item d-flex align-items-center py-2">
                        <a href="{{ url("/users/".Auth::user()->osu_id) }}" class="text-decoration-none">
                            <img src="{{ Auth::user()->avatar }}" class="rounded-circle me-2" width="30" height="30" alt="Avatar">
                            <span class="text-body me-3">{{ Auth::user()->name }}</span>
                        </a>
                    </li>
                    <li class="nav-item py-2">
                        <form action="{{ route('auth.osu.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-light">logout</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item py-2">
                        <a href="{{ route('auth.osu.login') }}" class="btn btn-sm btn-outline-light">login with osu!</a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>
