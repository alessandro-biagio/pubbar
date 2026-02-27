@extends('layouts.staff')
@section('content')
    <div class="max-w-7xl mx-auto px-6 py-8">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold">Gestione Ordini</h1>

            <form method="GET" class="flex items-center gap-2">
                <input
                    type="text"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Cerca ID, nome, telefono, utente, data…"
                    class="border rounded-lg px-3 py-2 text-sm w-80"
                />
                <select name="status" class="border rounded-lg px-3 py-2 text-sm w-36">
                    <option value="">Tutti gli stati</option>
                    @foreach($allStatuses as $s)
                        <option value="{{ $s }}" @selected($status===$s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button class="px-3 py-2 text-sm rounded-lg bg-slate-900 text-white">Filtra</button>
            </form>
        </div>

        {{-- Tabs con conteggi --}}
        @php
            $tabs = [];
            $tabs[] = [
                'label' => 'Tutti',
                'value' => '',
                'count' => array_sum($counts),
                'active' => $status === null,
                'url' => route('staff.orders.index'),
            ];
            foreach ($counts as $s => $c) {
                $tabs[] = [
                    'label' => ucfirst($s),
                    'value' => $s,
                    'count' => $c,
                    'active' => $status === $s,
                    'url' => route('staff.orders.index',['status'=>$s]),
                ];
            }
        @endphp

        <div class="mt-4 flex flex-wrap items-center gap-2">
            @foreach($tabs as $t)
                <a href="{{ $t['url'] }}"
                   class="px-3 py-1.5 rounded-full border text-sm {{ $t['active'] ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-700 border-slate-200' }}">
                    {{ $t['label'] }} <span class="opacity-70">({{ $t['count'] }})</span>
                </a>
            @endforeach
        </div>

        {{-- Tabella ordini --}}
        <div class="mt-6 overflow-x-auto bg-white border border-slate-200 rounded-2xl">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50">
                <tr class="text-left">
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">Cliente</th>
                    <th class="px-4 py-3">Telefono</th>
                    <th class="px-4 py-3">Totale</th>
                    <th class="px-4 py-3">Slot</th>
                    <th class="px-4 py-3">Creato</th>
                    <th class="px-4 py-3">Utente</th>
                    <th class="px-4 py-3">Note</th>
                    <th class="px-4 py-3">Stato</th>
                    <th class="px-4 py-3">Azione</th>
                </tr>
                </thead>
                <tbody>
                @forelse($orders as $o)
                    <tr class="border-t">
                        <td class="px-4 py-3 font-medium">
                            <button
                                type="button"
                                class="underline js-order-modal"
                                data-order-id="{{ $o->id }}"
                                data-order-url="{{ route('staff.orders.modal', $o) }}"
                            >
                                #{{ $o->id }}
                            </button>
                        </td>
                        <td class="px-4 py-3">{{ $o->customer_name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $o->customer_phone ?? '—' }}</td>
                        <td class="px-4 py-3">€ {{ number_format($o->total ?? 0, 2, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            @if(!empty($o->pickup_at))
                                {{ $o->pickup_at->format('H:i') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            {{ $o->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                        </td>

                        {{-- UTENTE --}}
                        <td class="px-4 py-3">
                            @if($o->user)
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $o->user->name }}</span>
                                    <span class="text-xs text-slate-500">{{ $o->user->email }}</span>
                                </div>
                            @else
                                <span class="text-slate-400 text-sm">Guest</span>
                            @endif
                        </td>

                        {{-- NOTE (box scrollabile) --}}
                        <td class="px-4 py-3">
                            @if(!empty($o->notes))
                                <div class="max-h-20 overflow-y-auto max-w-xs p-2 border rounded bg-slate-50 text-sm">
                                    {{ $o->notes }}
                                </div>
                            @else
                                <span class="text-slate-400 text-sm">—</span>
                            @endif
                        </td>

                        {{-- STATO --}}
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs
                                @class([
                                    'bg-yellow-50 text-yellow-800 border border-yellow-200' => $o->status === 'pending',
                                    'bg-blue-50 text-blue-800 border border-blue-200'       => $o->status === 'paid',
                                    'bg-indigo-50 text-indigo-800 border border-indigo-200' => $o->status === 'preparing',
                                    'bg-green-50 text-green-800 border border-green-200'    => $o->status === 'ready',
                                    'bg-slate-100 text-slate-700 border border-slate-200'   => $o->status === 'cancelled',
                                ])
                            ">
                                {{ ucfirst($o->status) }}
                            </span>
                        </td>

                        {{-- AZIONE --}}
                        <td class="px-4 py-3">
                            @php $options = \App\Models\Order::ALLOWED_TRANSITIONS[$o->status] ?? []; @endphp

                            @if(!empty($options))
                                <form method="POST" action="{{ route('staff.orders.updateStatus', $o) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="border rounded-lg px-2 py-1">
                                        @foreach($options as $opt)
                                            <option value="{{ $opt }}">{{ ucfirst($opt) }}</option>
                                        @endforeach
                                    </select>
                                    <button class="px-2 py-1 rounded-lg bg-slate-900 text-white text-xs">Aggiorna</button>
                                </form>
                            @else
                                <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-slate-500">Nessun ordine trovato.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>

    {{-- MODAL dettagli ordine (AJAX) --}}
    <div id="orderModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-black/50" data-close-order-modal></div>

        <div class="relative w-full max-w-4xl rounded-2xl bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-lg font-semibold text-slate-900">Dettagli ordine</h2>
                <button type="button" class="px-3 py-1.5 rounded-lg border text-sm" data-close-order-modal>Chiudi</button>
            </div>

            <div id="orderModalBody" class="mt-4">
                {{-- contenuto AJAX --}}
                <div class="text-slate-500 text-sm">Caricamento…</div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('orderModal');
            const body  = document.getElementById('orderModalBody');

            function openModal() {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                body.innerHTML = '<div class="text-slate-500 text-sm">Caricamento…</div>';
            }

            // chiusura overlay + bottone
            modal.querySelectorAll('[data-close-order-modal]').forEach(el => {
                el.addEventListener('click', closeModal);
            });

            // ESC
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
            });

            // click sui bottoni ordine
            document.querySelectorAll('.js-order-modal').forEach(btn => {
                btn.addEventListener('click', async function () {
                    const url = btn.getAttribute('data-order-url');
                    if (!url) return;

                    openModal();
                    body.innerHTML = '<div class="text-slate-500 text-sm">Caricamento…</div>';

                    try {
                        const res = await fetch(url, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });

                        if (!res.ok) throw new Error('HTTP ' + res.status);

                        const html = await res.text();
                        body.innerHTML = html;
                    } catch (err) {
                        body.innerHTML = `
                            <div class="p-4 rounded-xl border border-red-200 bg-red-50 text-red-800 text-sm">
                                Errore nel caricamento dettagli ordine. Riprova.
                            </div>
                        `;
                        console.error(err);
                    }
                });
            });
        })();
        </script>
@endsection


