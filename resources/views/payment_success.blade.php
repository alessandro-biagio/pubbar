<x-app-layout>
    <div class="max-w-2xl mx-auto px-6 py-12 text-center">
        <h1 class="text-3xl font-bold text-green-600">Pagamento completato con successo</h1>

        <p class="mt-4 text-lg text-gray-800">
            Ordine <strong>{{ $order->code }}</strong> confermato.
        </p>

        <p class="mt-2 text-gray-700">
            Ritiro previsto: {{ $order->pickup_at->isoFormat('ddd DD/MM HH:mm') }}
        </p>

        <p class="mt-2 text-gray-700">
            Totale pagato: € {{ number_format($order->total, 2, ',', '.') }}
        </p>

        <a href="{{ route('home') }}"
           class="mt-6 inline-block bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-xl transition">
            Torna alla home
        </a>
    </div>
</x-app-layout>
