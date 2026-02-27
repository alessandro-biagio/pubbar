<x-app-layout>
    <x-slot name="title">I miei ordini</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">I miei ordini</h2>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        @if (session('success'))
            <div class="rounded-lg bg-green-50 border border-green-200 p-3 text-green-800 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-lg bg-red-50 border border-red-200 p-3 text-red-800 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if($orders->count() === 0)
            <div class="rounded-xl border border-slate-200 bg-white p-6 text-slate-600">
                Non hai ancora effettuato ordini.
            </div>
        @else
            @foreach($orders as $o)
                @php
                    $status = strtolower($o->status ?? 'pending');
                    $statusClasses = [
                        'pending'   => 'bg-yellow-100 text-yellow-800',
                        'created'   => 'bg-yellow-100 text-yellow-800',
                        'paid'      => 'bg-green-100 text-green-700',
                        'preparing' => 'bg-blue-100 text-blue-700',
                        'ready'     => 'bg-emerald-100 text-emerald-700',
                        'cancelled' => 'bg-red-100 text-red-700',
                    ][$status] ?? 'bg-slate-100 text-slate-700';

                    $items = $o->items ?? collect();
                    $itemsCount = $items instanceof \Illuminate\Support\Collection
                        ? $items->count()
                        : (is_countable($items) ? count($items) : 0);
                @endphp

                <section class="rounded-2xl border border-slate-200 bg-white">
                    {{-- Header ordine --}}
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 p-4 border-b">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold">Ordine #{{ $o->id }}</h3>
                                @if(!empty($o->code))
                                    <span class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-700">
                                        Codice: {{ $o->code }}
                                    </span>
                                @endif
                            </div>

                            <div class="text-sm text-slate-600">
                                Creato il: {{ optional($o->created_at)->format('d/m/Y H:i') ?: '—' }}
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-xs px-2 py-1 rounded uppercase {{ $statusClasses }}">
                                {{ $o->status ?? 'pending' }}
                            </span>
                        </div>
                    </div>

                    {{-- Meta ritiro + pagamento --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4">
                        <div>
                            <div class="text-xs uppercase text-slate-500">Ritiro</div>
                            <div class="text-sm">
                                {{ optional($o->pickup_at)->format('d/m/Y H:i') ?: '—' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase text-slate-500">Pagamento</div>
                            <div class="text-sm">
                                {{ $o->payment_provider ?? '—' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase text-slate-500">Stato pagamento</div>
                            <div class="text-sm uppercase">
                                {{ $o->payment_status ?? '—' }}
                            </div>
                        </div>
                    </div>

                    {{-- Articoli --}}
                    <details class="group">
                        <summary class="cursor-pointer select-none list-none px-4 py-3 border-t flex items-center justify-between">
                            <span class="text-sm font-medium">
                                Articoli ({{ $itemsCount }})
                            </span>
                            <svg class="h-4 w-4 transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </summary>

                        <div class="px-4 pb-4">
                            <div class="overflow-x-auto border rounded-xl">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-slate-50">
                                        <tr class="text-left">
                                            <th class="px-3 py-2">Prodotto</th>
                                            <th class="px-3 py-2">Variante</th>
                                            <th class="px-3 py-2">Qtà</th>
                                            <th class="px-3 py-2">Totale riga</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white">
                                    @forelse($items as $it)
                                        @php
                                            $unit = (float)($it->unit_price ?? $it->price ?? 0);
                                            $qty  = (int)($it->quantity ?? 1);
                                            $line = $unit * $qty;
                                        @endphp
                                        <tr class="border-t">
                                            <td class="px-3 py-2">
                                                {{ optional($it->product)->name ?? ($it->product_name ?? '—') }}
                                            </td>
                                            <td class="px-3 py-2">
                                                {{ optional($it->variant)->name ?? ($it->variant_name ?? ($it->options ?? '—')) }}
                                            </td>
                                            <td class="px-3 py-2">{{ $qty }}</td>
                                            <td class="px-3 py-2">€ {{ number_format($line, 2, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-3 py-4 text-center text-slate-500">
                                                Nessun articolo presente per questo ordine.
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </details>

                    {{-- Totale ordine --}}
                    <div class="flex items-center justify-end gap-4 p-4 border-t">
                        <div class="text-sm text-slate-600">Totale ordine</div>
                        <div class="text-lg font-semibold">
                            € {{ number_format((float)($o->total ?? 0), 2, ',', '.') }}
                        </div>
                    </div>
                </section>
            @endforeach

            <div>{{ $orders->links() }}</div>
        @endif
    </div>
</x-app-layout>
