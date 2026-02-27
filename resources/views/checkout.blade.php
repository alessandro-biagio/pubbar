<x-app-layout>
    <x-slot name="title">Checkout</x-slot>
    <div class="max-w-3xl mx-auto px-6 py-10">
        <h1 class="text-3xl font-bold">Checkout</h1>

        @if(session('success'))
            <div class="mt-4 p-3 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        {{-- Riepilogo carrello --}}
        <div class="mt-6 border rounded-xl overflow-hidden">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b">
                        <th class="text-left p-2">Prodotto</th>
                        <th class="text-left p-2">Prezzo</th>
                        <th class="text-left p-2">Qty</th>
                        <th class="text-left p-2">Totale</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cart as $item)
                        <tr class="border-b">
                            <td class="p-2">{{ $item['name'] }}</td>
                            <td class="p-2">€ {{ number_format($item['price'], 2, ',', '.') }}</td>
                            <td class="p-2">{{ $item['qty'] }}</td>
                            <td class="p-2">€ {{ number_format($item['price'] * $item['qty'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4 text-right font-bold">
                Totale: € {{ number_format($total, 2, ',', '.') }}
            </div>
        </div>

        {{-- Form checkout --}}
        <form action="{{ route('checkout.place') }}" method="POST" class="mt-8 space-y-5">
            @csrf

            <div>
                <label for="pickup_at" class="block font-semibold">Orario di ritiro</label>
                <select id="pickup_at" name="pickup_at" class="border rounded px-3 py-2 w-full">
                    @foreach($slots as $s)
                        <option value="{{ $s['value'] }}" {{ old('pickup_at')===$s['value']?'selected':'' }}>
                            {{ $s['label'] }}
                        </option>
                    @endforeach
                </select>
                @error('pickup_at') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold">Nome (opzionale)</label>
                    <input
                        type="text"
                        name="customer_name"
                        class="border rounded px-3 py-2 w-full"
                        value="{{ old('customer_name', session('checkout.prefill.customer_name')) }}"
                    >
                </div>
                <div>
                    <label class="block font-semibold">Telefono (opzionale)</label>
                    <input
                        type="text"
                        name="customer_phone"
                        class="border rounded px-3 py-2 w-full"
                        value="{{ old('customer_phone', session('checkout.prefill.customer_phone')) }}"
                    >
                </div>
            </div>

            <div>
                <label class="block font-semibold">Note (opzionale)</label>
                <textarea
                    name="notes"
                    rows="3"
                    class="border rounded px-3 py-2 w-full"
                    placeholder="Es. niente maionese"
                >{{ old('notes', session('checkout.prefill.notes')) }}</textarea>
            </div>

            <button type="submit"
                    class="inline-flex items-center px-5 py-3 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition">
                Conferma ordine
            </button>
        </form>
    </div>
</x-app-layout>
