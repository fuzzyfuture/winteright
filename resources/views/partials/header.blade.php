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
                    <a class="nav-link" href="#">search</a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto">
                @if (Auth::check())
                    <li class="nav-item">
                        <a href="{{ url("/users/".Auth::user()->osu_id) }}" class="nav-link">
                            <img src="{{ Auth::user()->avatar }}" class="me-1" width="16" height="16" alt="Avatar">
                            <span class="text-body">{{ Auth::user()->name }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <form action="{{ route('auth.osu.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="nav-link w-100 text-start">logout</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item">
                        <a href="{{ route('auth.osu.login') }}" class="nav-link" >login with osu!</a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>
