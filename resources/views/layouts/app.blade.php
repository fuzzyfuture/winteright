<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>winteright</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/js/app.js'])
</head>
<body class="d-flex flex-column min-vh-100">
    <div class="flex-grow-1 py-4">
        <div class="container" id="mainContainer">
            @include('partials.header')
            @if (session('success'))
                <div class="alert alert-primary m-3" data-bs-theme="dark">{{ session('success') }}</div>
            @endif
            <main class="content p-4">
                @yield('content')
            </main>
            @include('partials.footer')
        </div>
    </div>
</body>
</html>
