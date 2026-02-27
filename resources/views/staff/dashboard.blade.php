<x-app-layout>
    <x-slot name="title">Dashboard Staff</x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("Benvenuto nella dashboard dello staff!") }}
                </div>
            </div>

            <div class="mt-6 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Riquadro gestione ordini -->
                <a href="{{ route('staff.orders.index') }}"
                   class="block bg-white border border-gray-200 rounded-xl shadow-sm p-6 hover:shadow-md transition">
                    <div class="text-lg font-semibold">Gestione Ordini</div>
                    <div class="text-gray-600 text-sm mt-1">
                        Visualizza e aggiorna lo stato degli ordini
                    </div>
                </a>

                <!-- Riquadro gestione quantità (capienza cucina) -->
                <a href="{{ route('staff.capacity.edit') }}"
                   class="block bg-white border border-gray-200 rounded-xl shadow-sm p-6 hover:shadow-md transition">
                    <div class="text-lg font-semibold">Gestione Quantità</div>
                    <div class="text-gray-600 text-sm mt-1">
                        Aggiorna i limiti orari delle categorie
                    </div>
                </a>

                <!-- Riquadro gestione prodotti -->
                <a href="{{ route('staff.products.index') }}"
                   class="block bg-white border border-gray-200 rounded-xl shadow-sm p-6 hover:shadow-md transition">
                    <div class="text-lg font-semibold">Gestione Prodotti</div>
                    <div class="text-gray-600 text-sm mt-1">
                        Crea, modifica o elimina i prodotti del catalogo
                    </div>
                </a>

                <!-- Riquadro gestione categorie -->
                <a href="{{ route('staff.categories.index') }}"
                   class="block bg-white border border-gray-200 rounded-xl shadow-sm p-6 hover:shadow-md transition">
                    <div class="text-lg font-semibold">Gestione Categorie</div>
                    <div class="text-gray-600 text-sm mt-1">
                        Aggiungi, modifica o disattiva le categorie disponibili
                    </div>
                </a>
                <a href="{{ route('staff.users.index') }}"
                class="block bg-white border border-gray-200 rounded-xl shadow-sm p-6 hover:shadow-md transition">
                    <div class="text-lg font-semibold">Gestione Utenti</div>
                    <div class="text-gray-600 text-sm mt-1">Visiona utenti e storico ordini</div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
