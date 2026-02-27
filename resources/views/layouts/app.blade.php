<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>
            {{ trim($title ?? '') !== '' ? $title.' · '.config('app.name', 'Hosteria') : config('app.name', 'Hosteria') }}
        </title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    
    <script>
        (function () {
            try {
                const pending = sessionStorage.getItem('cart_revert_pending');
                if (!pending) return;

                // se il server dice che il carrello è già pieno, chiudo tutto
                const cartCount = Number(@json($cartCount ?? 0));
                if (cartCount > 0) {
                    sessionStorage.removeItem('cart_revert_pending');
                    sessionStorage.removeItem('cart_revert_reloaded');
                    return;
                }

                // reload massimo 1 volta, altrimenti rischio loop
                if (sessionStorage.getItem('cart_revert_reloaded')) {
                    sessionStorage.removeItem('cart_revert_pending');
                    sessionStorage.removeItem('cart_revert_reloaded');
                    return;
                }

                sessionStorage.setItem('cart_revert_reloaded', '1');

                setTimeout(() => {
                    window.location.reload();
                }, 250);
            } catch (e) {}
        })();
        </script>

    <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen flex flex-col">
            {{-- Navbar --}}
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            {{-- PERCORSO (slug) SEMPRE VISIBILE --}}
            @include('layouts.path')

            <!-- Page Content -->
            <main class="flex-1">
                {{ $slot }}
            </main>

            {{-- Footer globale --}}
            @include('layouts.footer')
        </div>
    </body>
</html>
