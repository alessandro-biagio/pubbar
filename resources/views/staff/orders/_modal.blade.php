@php
    $o = $order;
@endphp

<div class="space-y-4">
    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="text-xs uppercase text-slate-500">Ordine</div>
            <div class="text-lg font-semibold">#{{ $o->id }} <span class="text-slate-400 text-sm">({{ $o->code ?? '—' }})</span></div>
        </div>

        <div class="text-right">
            <div class="text-xs uppercase text-slate-500">Stato</div>
            <div class="inline-flex items-center px-2 py-1 rounded-full text-xs border
                @class([
                    'bg-yellow-50 text-yellow-800 border-yellow-200' => $o->status === 'pending',
                    'bg-blue-50 text-blue-800 border-blue-200'       => $o->status === 'paid',
                    'bg-indigo-50 text-indigo-800 border-indigo-200' => $o->status === 'preparing',
                    'bg-green-50 text-green-800 border-green-200'    => $o->status === 'ready',
                    'bg-slate-100 text-slate-700 border-slate-200'   => $o->status === 'cancelled',
                ])
            ">
                {{ ucfirst($o->status ?? '—') }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Cliente --}}
        <div class="space-y-2">
            <div>
                <div class="text-xs uppercase text-slate-500">Cliente</div>
                <div class="font-medium">{{ $o->customer_name ?? '—' }}</div>
            </div>

            <div>
                <div class="text-xs uppercase text-slate-500">Telefono</div>
                <div>{{ $o->customer_phone ?? '—' }}</div>
            </div>

            <div>
                <div class="text-xs uppercase text-slate-500">Creato</div>
                <div>{{ optional($o->created_at)->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }}</div>
            </div>

            <div>
                <div class="text-xs uppercase text-slate-500">Slot</div>
                <div>{{ optional($o->pickup_at)->format('d/m/Y H:i') ?? '—' }}</div>
            </div>

            <div>
                <div class="text-xs uppercase text-slate-500">Totale</div>
                <div class="font-semibold">€ {{ number_format((float)($o->total ?? 0), 2, ',', '.') }}</div>
            </div>
        </div>

        {{-- Utente + pagamento --}}
        <div class="space-y-2">
            <div>
                <div class="text-xs uppercase text-slate-500">Utente</div>
                @if($o->user)
                    <div class="font-medium">{{ $o->user->name }}</div>
                    <div class="text-xs text-slate-500">{{ $o->user->email }}</div>
                @else
                    <div class="text-slate-500">Guest</div>
                @endif
            </div>

            <div>
                <div class="text-xs uppercase text-slate-500">Pagamento</div>
                <div>{{ $o->payment_provider ?? '—' }}</div>
            </div>

            <div>
                <div class="text-xs uppercase text-slate-500">Stato pagamento</div>
                <div class="uppercase">{{ $o->payment_status ?? '—' }}</div>
            </div>

            <div class="text-xs break-all">
                <div class="text-xs uppercase text-slate-500">Riferimenti</div>
                <div>{{ $o->payment_session_id ? ('Session: '.$o->payment_session_id) : '—' }}</div>
                <div>{{ $o->payment_intent_id ? ('Intent: '.$o->payment_intent_id) : '' }}</div>
            </div>
        </div>

        {{-- Note --}}
        <div class="space-y-2">
            <div class="text-xs uppercase text-slate-500">Note</div>
            @if(!empty($o->notes))
                <div class="whitespace-pre-line max-h-44 overflow-y-auto p-3 rounded-lg border bg-slate-50 text-sm">
                    {{ $o->notes }}
                </div>
            @else
                <div class="text-slate-500">—</div>
            @endif
        </div>
    </div>

    {{-- Articoli --}}
    <div>
        <div class="text-xs uppercase text-slate-500 mb-2">Articoli ordinati</div>

        <div class="overflow-x-auto border rounded-xl">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left">
                        <th class="px-3 py-2">Prodotto</th>
                        <th class="px-3 py-2">Variante</th>
                        <th class="px-3 py-2">Qtà</th>
                        <th class="px-3 py-2">Prezzo</th>
                        <th class="px-3 py-2">Totale riga</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($o->items as $it)
                    @php
                        $qty  = (int)($it->quantity ?? 1);
                        $unit = (float)($it->unit_price ?? $it->price ?? 0);
                    @endphp
                    <tr class="border-t">
                        <td class="px-3 py-2">
                            {{ optional($it->product)->name ?? ($it->product_name ?? '—') }}
                        </td>
                        <td class="px-3 py-2">
                            {{ optional($it->variant)->name ?? ($it->variant_name ?? ($it->options ?? '—')) }}
                        </td>
                        <td class="px-3 py-2">{{ $qty }}</td>
                        <td class="px-3 py-2">€ {{ number_format($unit, 2, ',', '.') }}</td>
                        <td class="px-3 py-2">€ {{ number_format($unit * $qty, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-6 text-center text-slate-500">
                            Nessun articolo presente per questo ordine.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
