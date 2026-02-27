<x-app-layout>
    <div class="max-w-2xl mx-auto px-6 py-12 text-center">
        <h1 class="text-3xl font-bold text-red-600">Pagamento non riuscito</h1>

        <p class="mt-4 text-lg text-gray-800">
            Ordine <strong>{{ $order->code }}</strong>
        </p>

        <p class="mt-2 text-gray-700">
            {{ $fail_message ?? 'Il pagamento non è andato a buon fine. Per favore riprova o effettua un nuovo ordine.' }}
        </p>

        <p class="mt-2 text-gray-700">
            Totale: € {{ number_format($order->total, 2, ',', '.') }}
        </p>

        <a href="{{ route('home') }}"
           class="mt-6 inline-block bg-gray-800 hover:bg-gray-900 text-white font-semibold px-6 py-3 rounded-xl transition">
            Torna al menu
        </a>
    </div>
</x-app-layout>
