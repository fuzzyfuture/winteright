<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>winteright</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/js/app.js'])
</head>
<body class="d-flex flex-column min-vh-100">

@include('partials.header')

<main class="flex-grow-1 py-4">
    <div class="container">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @yield('content')
    </div>
</main>

@include('partials.footer')

</body>
</html>
