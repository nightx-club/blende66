<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f6f0e7">
    @isset($description)<meta name="description" content="{{ $description }}">@endisset
    <title>{{ $title ?? 'Blende6' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/blende6-logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#fbf8f3] text-[#29322b] antialiased">
    @yield('content')
    @stack('scripts')
</body>
</html>
