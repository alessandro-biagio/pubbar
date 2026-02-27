{{-- resources/views/staff/users/orders.blade.php --}}
@extends('layouts.staff')
@php($title = 'Storico ordini — '.($user->name ?? '—').' (#'.$user->id.')')

@section('content')
<div class="px-4 sm:px-0 space-y-4">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Storico ordini</h1>
            <p class="text-sm text-slate-600">
                Utente: <span class="font-medium">{{ $user->name ?? '—' }}</span>
                — ID #{{ $user->id }} — {{ $user->email }}
            </p>
        </div>
        <a href="{{ route('staff.users.index') }}" class="px-3 py-2 text-sm rounded-lg border">↩︎ Torna agli utenti</a>
    </div>

    <div class="overflow-x-auto bg-white border border-slate-200 rounded-2xl">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left align-top">
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">Codice</th>
                    <th class="px-4 py-3">Creato il</th>
                    <th class="px-4 py-3">Stato</th>
                    <th class="px-4 py-3">Ritiro</th>
                </tr>
            </thead>
            <tbody>

            {{-- Lista ordini --}}
            @foreach($orders as $o)
                {{-- Riga testata ordine --}}
                <tr class="border-t">
                    <td class="px-4 py-3 font-medium">#{{ $o->id }}</td>
                    <td class="px-4 py-3">{{ $o->code }}</td>
                    <td class="px-4 py-3">{{ optional($o->created_at)->format('d/m/Y H:i') ?: '—' }}</td>
                    <td class="px-4 py-3 uppercase">{{ $o->status ?? 'pending' }}</td>
                    <td class="px-4 py-3">
                        {{ optional($o->pickup_at)->format('d/m/Y H:i') ?: '—' }}
                        <div class="text-xs text-slate-500">
                            {{ optional($o->expires_at)->format('d/m/Y H:i') ? ('Scade: '. $o->expires_at->format('d/m/Y H:i')) : '' }}
                        </div>
                    </td>
                </tr>

                {{-- Riga dettagli ordine (indirizzo rimosso) --}}
                <tr class="border-b bg-slate-50/40">
                    <td colspan="5" class="px-4 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                            {{-- Colonna 1: Dati cliente (senza indirizzo) --}}
                            <div class="space-y-1">
                                <div class="text-xs uppercase text-slate-500">Cliente</div>
                                <div class="font-medium">{{ $o->customer_name ?? '—' }}</div>

                                <div class="text-xs uppercase text-slate-500 mt-3">Telefono</div>
                                <div>{{ $o->customer_phone ?? '—' }}</div>

                                <div class="text-xs uppercase text-slate-500 mt-3">Note</div>
                                <div class="whitespace-pre-line">{{ $o->notes ?? '—' }}</div>
                            </div>

                            {{-- Colonna 2-3: Articoli --}}
                            <div class="space-y-2 md:col-span-2">
                                <div class="text-xs uppercase text-slate-500">Articoli ordinati</div>

                                <div class="overflow-x-auto border rounded-lg">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-white">
                                            <tr class="text-left">
                                                <th class="px-3 py-2">Prodotto</th>
                                                <th class="px-3 py-2">Variante</th>
                                                <th class="px-3 py-2">Qtà</th>
                                                <th class="px-3 py-2">Prezzo unit.</th>
                                                <th class="px-3 py-2">Totale riga</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($o->items as $it)
                                            <tr class="border-t">
                                                <td class="px-3 py-2">
                                                    {{ optional($it->product)->name ?? ($it->product_name ?? '—') }}
                                                </td>
                                                <td class="px-3 py-2">
                                                    {{ optional($it->variant)->name ?? ($it->variant_name ?? ($it->options ?? '—')) }}
                                                </td>
                                                <td class="px-3 py-2">{{ $it->quantity ?? 1 }}</td>
                                                <td class="px-3 py-2">
                                                    {{ isset($it->unit_price) ? ('€ '.number_format((float)$it->unit_price, 2, ',', '.')) : (isset($it->price) ? ('€ '.number_format((float)$it->price, 2, ',', '.')) : '—') }}
                                                </td>
                                                <td class="px-3 py-2">
                                                    {{ '€ '.number_format( ((float)($it->unit_price ?? $it->price ?? 0)) * (int)($it->quantity ?? 1), 2, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach

                                        {{-- Nessun articolo? --}}
                                        @if(($o->items && $o->items->count() === 0) || !$o->items)
                                            <tr>
                                                <td colspan="5" class="px-3 py-4 text-center text-slate-500">
                                                    Nessun articolo presente per questo ordine.
                                                </td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Info pagamento sintetiche --}}
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
                                    <div>
                                        <div class="text-xs uppercase text-slate-500">Pagamento</div>
                                        <div>{{ $o->payment_provider ?? '—' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs uppercase text-slate-500">Stato pagamento</div>
                                        <div class="uppercase">{{ $o->payment_status ?? '—' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs uppercase text-slate-500">Riferimenti</div>
                                        <div class="text-xs break-all">
                                            <div>{{ $o->payment_session_id ? ('Session: '.$o->payment_session_id) : '—' }}</div>
                                            <div>{{ $o->payment_intent_id ? ('Intent: '.$o->payment_intent_id) : '' }}</div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach

            {{-- Nessun ordine per l'utente --}}
            @if($orders->count() === 0)
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                        Nessun ordine per questo utente.
                    </td>
                </tr>
            @endif

            </tbody>
        </table>
    </div>

    <div>{{ $orders->links() }}</div>
</div>
@endsection
