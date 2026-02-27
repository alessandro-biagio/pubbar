<x-app-layout>
    <x-slot name="title">Pagamento</x-slot>
    <div class="max-w-3xl mx-auto px-6 py-10">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold">Pagamento ordine {{ $order->code }}</h1>
                <p class="mt-2 opacity-70">Totale: € {{ number_format((float)$order->total, 2, ',', '.') }}</p>
            </div>

            @php
                // controllo "pagabile/modificabile" calcolato qui per mostrare/nascondere azioni
                $status     = strtolower((string)$order->status);
                $payStatus  = strtolower((string)$order->payment_status);
                $isPending  = in_array($status, ['pending','created'], true);
                $notExpired = is_null($order->expires_at) || optional($order->expires_at)->isFuture();
                $isUnpaid   = !in_array($payStatus, ['paid','succeeded','completed'], true);
                $canModify  = $isPending && $notExpired && $isUnpaid && auth()->check() && auth()->id() === (int)$order->user_id;
            @endphp

            @if($canModify)
                {{-- POST che ripristina carrello + prefill e cancella l'ordine --}}
                <form method="POST" action="{{ route('orders.revert', $order) }}">
                    @csrf
                    {{-- type=button: submit solo dopo conferma modal --}}
                    <button type="button"
                            class="px-3 py-2 rounded-lg border hover:bg-slate-50 text-sm js-confirm-btn"
                            data-confirm-title="Modifica ordine"
                            data-confirm-message="Vuoi modificare l’ordine? Gli articoli verranno rimessi nel carrello e questo ordine verrà annullato.">
                        Modifica
                    </button>
                </form>
            @endif
        </div>

        {{-- flag via querystring usato per mostrare banner dopo cancel PayPal --}}
        @if(request('canceled'))
            <div class="mt-4 p-3 bg-yellow-100 text-yellow-800 rounded">
                Pagamento annullato. Si è verificato un problema con PayPal.
            </div>
        @endif

        {{-- Recap ordine --}}
        <div class="mt-6 rounded-xl border border-slate-200 overflow-hidden bg-white">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-0">
                <div class="p-4 border-b md:border-b-0 md:border-r">
                    <div class="text-xs uppercase text-slate-500">Ritiro</div>
                    <div class="text-sm">
                        {{ optional($order->pickup_at)->format('d/m/Y H:i') ?: '—' }}
                        @if($canModify && $order->expires_at)
                            <div class="text-xs text-slate-500 mt-1">
                                Scade: {{ optional($order->expires_at)->format('d/m/Y H:i') }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-4 border-b md:border-b-0 md:border-r">
                    <div class="text-xs uppercase text-slate-500">Cliente</div>
                    <div class="text-sm space-y-1">
                        <div><span class="text-slate-500">Nome:</span> {{ $order->customer_name ?: '—' }}</div>
                        <div><span class="text-slate-500">Telefono:</span> {{ $order->customer_phone ?: '—' }}</div>
                    </div>
                </div>

                <div class="p-4">
                    <div class="text-xs uppercase text-slate-500">Note</div>
                    <div class="text-sm whitespace-pre-line">{{ $order->notes ?: '—' }}</div>
                </div>
            </div>

            <div class="border-t">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-left">
                                <th class="px-3 py-2">Prodotto</th>
                                <th class="px-3 py-2">Variante</th>
                                <th class="px-3 py-2">Qtà</th>
                                <th class="px-3 py-2">Prezzo</th>
                                <th class="px-3 py-2">Totale riga</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                        @foreach(($order->items ?? []) as $it)
                            @php
                                // compat: supporto nomi campi vecchi/nuovi (unit_price vs price, qty vs quantity)
                                $unit = (float)($it->unit_price ?? $it->price ?? 0);
                                $qty  = (int)($it->quantity ?? $it->qty ?? 1);
                                $line = $unit * $qty;
                            @endphp
                            <tr class="border-t">
                                <td class="px-3 py-2">{{ optional($it->product)->name ?? ($it->product_name ?? '—') }}</td>
                                <td class="px-3 py-2">{{ optional($it->variant)->name ?? ($it->variant_name ?? ($it->options ?? '—')) }}</td>
                                <td class="px-3 py-2">{{ $qty }}</td>
                                <td class="px-3 py-2">€ {{ number_format($unit, 2, ',', '.') }}</td>
                                <td class="px-3 py-2">€ {{ number_format($line, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach

                        @if(($order->items && $order->items->count() === 0) || !$order->items)
                            <tr>
                                <td colspan="5" class="px-3 py-4 text-center text-slate-500">
                                    Nessun articolo presente.
                                </td>
                            </tr>
                        @endif
                        </tbody>

                        <tfoot>
                            <tr class="border-t bg-slate-50">
                                <td colspan="4" class="px-3 py-3 text-right font-medium">Totale</td>
                                <td class="px-3 py-3 font-semibold">€ {{ number_format((float)$order->total, 2, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- PayPal --}}
        <div class="mt-8">
            {{-- parametri PayPal SDK (client-id + currency arrivano dal controller) --}}
            <script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency={{ $currency }}"></script>
            <div id="paypal-button-container"></div>

            {{-- modal custom usata solo per confermare "Modifica" --}}
            <div id="uiModal"
                 class="fixed inset-0 z-[99999] hidden items-center justify-center px-4"
                 aria-modal="true"
                 role="dialog">
                <div class="absolute inset-0 bg-black/60 z-[99999]" data-ui-close></div>

                <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl z-[100000]">
                    <h2 id="uiModalTitle" class="text-lg font-semibold text-slate-900">Conferma</h2>
                    <p id="uiModalMsg" class="mt-2 text-sm text-slate-600"></p>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button type="button"
                                class="px-4 py-2 rounded-lg border border-slate-300 text-slate-800 hover:bg-slate-100 transition"
                                data-ui-cancel>
                            Annulla
                        </button>

                        <button type="button"
                                class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 transition"
                                id="uiModalOk">
                            Procedi
                        </button>
                    </div>
                </div>
            </div>

            <script>
                // modal conferma "Modifica" (legge testo da data-confirm-*)
                (function () {
                    const modal  = document.getElementById('uiModal');
                    const title  = document.getElementById('uiModalTitle');
                    const msg    = document.getElementById('uiModalMsg');
                    const okBtn  = document.getElementById('uiModalOk');
                    const cancel = modal.querySelector('[data-ui-cancel]');

                    let onOk = null;

                    function openConfirm({titleText, messageText, onConfirm}) {
                        title.textContent = titleText || 'Conferma';
                        msg.textContent   = messageText || 'Confermi?';
                        onOk = typeof onConfirm === 'function' ? onConfirm : null;

                        document.body.style.overflow = 'hidden';
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                    }

                    function closeModal() {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                        document.body.style.overflow = '';
                        onOk = null;
                    }

                    modal.querySelectorAll('[data-ui-close]').forEach(el => el.addEventListener('click', closeModal));
                    cancel.addEventListener('click', closeModal);

                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
                    });

                    okBtn.addEventListener('click', () => {
                        const fn = onOk;
                        closeModal();
                        if (fn) fn();
                    });

                    document.addEventListener('DOMContentLoaded', function () {
                        document.querySelectorAll('.js-confirm-btn').forEach(function (btn) {
                            btn.addEventListener('click', function () {
                                const form = btn.closest('form');
                                openConfirm({
                                    titleText: btn.getAttribute('data-confirm-title') || 'Conferma',
                                    messageText: btn.getAttribute('data-confirm-message') || 'Confermi?',
                                    onConfirm: () => { if (form) form.submit(); }
                                });
                            });
                        });
                    });
                })();
            </script>

            <script>
                const revertBeaconUrl = @json($revertBeaconUrl ?? null);
                let paymentRedirecting = false;
                let autoRevertSent = false;

                function autoRevertOnLeave() {
                    if (!revertBeaconUrl) return;
                    if (paymentRedirecting) return;
                    if (autoRevertSent) return;
                    autoRevertSent = true;

                    try { sessionStorage.setItem('cart_revert_pending', '1'); } catch (e) {}

                    try {
                        if (navigator.sendBeacon) {
                            navigator.sendBeacon(revertBeaconUrl, new Blob([], { type: "text/plain" }));
                            return;
                        }
                    } catch (e) {}

                    fetch(revertBeaconUrl, {
                        method: "POST",
                        keepalive: true,
                        credentials: "same-origin",
                        headers: { "Accept": "application/json" },
                    }).catch(() => {});
                }

                window.addEventListener("pagehide", autoRevertOnLeave);

                async function restoreCartViaBeaconAndGoCart(cartNotice) {
                    if (!revertBeaconUrl) return;

                    // lo setto subito così pagehide non parte in mezzo
                    paymentRedirecting = true;

                    try { sessionStorage.setItem('cart_revert_pending', '1'); } catch (e) {}

                    try {
                        await fetch(revertBeaconUrl, {
                            method: "POST",
                            keepalive: true,
                            credentials: "same-origin",
                            headers: { "Accept": "application/json" },
                        }).catch(() => {});
                    } finally {
                        try { sessionStorage.setItem("cart_notice", cartNotice || ""); } catch (e) {}
                        window.location.href = "{{ route('cart.index') }}";
                    }
                }
            </script>


            <script>
                // endpoint backend + token CSRF per le chiamate ajax
                const createUrl  = "{{ route('paypal.create', $order) }}";
                const captureUrl = "{{ route('paypal.capture') }}";
                const csrf       = "{{ csrf_token() }}";

                paypal.Buttons({
                    onInit: function (data, actions) {
                        // se l'ordine non è pagabile disabilito i bottoni
                        @if(!$isPending || !$notExpired || !$isUnpaid)
                            actions.disable();
                        @endif
                    },

                    createOrder: function () {
                        // POST al backend: crea ordine PayPal e ritorna paypalOrderId
                        return fetch(createUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            }
                        })
                        .then(async res => {
                            if (!res.ok) {
                                const out = await res.json().catch(() => ({}));

                                // 409: ordine scaduto/non pagabile -> revert e torno al carrello con notice
                                if (res.status === 409) {
                                    const msg = out.error || 'Ordine scaduto o non più pagabile.';
                                    const notice = msg + " Abbiamo rimesso gli articoli nel carrello: puoi modificarli e riprovare.";
                                    await restoreCartViaBeaconAndGoCart(notice);
                                    return new Promise(() => {});
                                }

                                console.warn("PayPal createOrder failed:", res.status, out);
                                throw new Error(out.error || 'Create order failed');
                            }
                            return res.json();
                        })
                        .then(data => data.id);
                    },

                    onApprove: function (data) {
                        // POST al backend: capture PayPal -> aggiorna order + redirect
                        return fetch(captureUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ orderID: data.orderID })
                        })
                        .then(async res => {
                            if (!res.ok) {
                                const out = await res.json().catch(() => ({}));

                                // 409: ordine non più pagabile -> revert e torno al carrello con notice
                                if (res.status === 409) {
                                    const msg = out.error || 'Ordine scaduto o non più pagabile.';
                                    const notice = msg + " Abbiamo rimesso gli articoli nel carrello: puoi modificarli e riprovare.";
                                    await restoreCartViaBeaconAndGoCart(notice);
                                    return new Promise(() => {});
                                }

                                console.warn("PayPal capture failed:", res.status, out);
                                throw new Error(out.error || 'Capture failed');
                            }

                            const out = await res.json().catch(() => ({}));

                            // redirect deciso dal backend (success o fail)
                            if (out.redirect) {
                                paymentRedirecting = true;
                                window.location.href = out.redirect;
                                return;
                            }

                            console.warn("Capture ok but no redirect:", out);
                        })
                        .catch((e) => console.error(e));
                    },

                    onCancel: function () {
                        // cancel PayPal: ricarico la pagina con flag per banner
                        paymentRedirecting = true;
                        window.location.href = "{{ route('payment.show', $order) }}?canceled=1";
                    },

                    onError: function (err) {
                        console.error(err);
                        // se serve: redirect a ?canceled=1 per banner
                    }
                }).render('#paypal-button-container');
            </script>
        </div>
    </div>
</x-app-layout>
