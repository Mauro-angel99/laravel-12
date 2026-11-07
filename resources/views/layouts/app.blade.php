<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts for Blade layout (non-Inertia) -->
    @vite(['resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    {{-- Sidebar, barra in alto e titolo della pagina (slot $header, opzionale) --}}
    <x-app-shell :header="$header ?? null" id="app">
        {{-- se la pagina usa slot (Livewire/Breeze) --}}
        {{ $slot ?? '' }}

        {{-- oppure se vuoi usare @yield per sezioni Blade --}}
        @yield('content')
    </x-app-shell>
</body>

</html>
