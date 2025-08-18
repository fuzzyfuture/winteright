<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>winteright</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta property="og:title" content="winteright">
    <meta property="og:description" content="a beatmap rating platform for osu!">
    <meta property="og:url" content="https://winteright.net/">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="winteright">

    @vite(['resources/js/app.js'])
</head>
<body class="d-flex flex-column min-vh-100">
    <div class="flex-grow-1 py-4">
        <div class="container" id="mainContainer">
            @include('partials.header')
            @if (session('success'))
                <div class="alert alert-primary m-3 mb-0" data-bs-theme="dark">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <div class="alert alert-primary m-3 mb-0" data-bs-theme="dark">{{ strtolower($error) }}</div>
                @endforeach
            @endif
            <main class="content p-4">
                @yield('content')
            </main>
            @yield('scripts')
            @include('partials.footer')
        </div>
    </div>
</body>
</html>
