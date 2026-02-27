{{-- resources/views/layouts/staff.blade.php --}}
<x-app-layout>

    {{-- Sub-nav staff (link rapidi) --}}
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <nav class="flex gap-4 text-sm">
                <a href="{{ route('staff.dashboard') }}" class="hover:underline">Dashboard</a>
                <a href="{{ route('staff.orders.index') }}" class="hover:underline">Ordini</a>
                @if (Route::has('staff.products.index'))
                    <a href="{{ route('staff.products.index') }}" class="hover:underline">Prodotti</a>
                @endif
                @if (Route::has('staff.categories.index'))
                    <a href="{{ route('staff.categories.index') }}" class="hover:underline">Categorie</a>
                @endif
                <a href="{{ route('staff.capacity.edit') }}" class="hover:underline">Quantità cucina</a>
                <a href="{{ route('staff.users.index') }}" class="hover:underline">Gestione Utenti</a>
            </nav>
        </div>
    </div>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash messages comuni (opzionali) --}}
            @if (session('success'))
                <div class="mb-4 bg-green-100 text-green-800 px-4 py-2 rounded">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 bg-red-100 text-red-800 px-4 py-2 rounded">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Contenuto specifico della pagina --}}
            @yield('content')
        </div>
    </div>

    {{-- (Opzionale) punto di aggancio per script pagina-specifici --}}
    @stack('scripts')
</x-app-layout>
