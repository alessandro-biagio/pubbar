<x-app-layout>
    <x-slot name="title">Carrello</x-slot>

    {{-- totale iniziale dal server, poi aggiornato live con fetch --}}
    <div class="max-w-4xl mx-auto px-6 py-10"
         x-data="{ cartTotal: {{ $total }} }">

        <h1 class="text-3xl font-bold mb-4">Carrello</h1>

        {{-- banner usato per messaggi “one-shot” lato client (sessionStorage) --}}
        <div id="cart-notice"
             class="hidden mb-6 p-4 rounded-xl border border-amber-300 bg-amber-50 text-amber-900">
        </div>

        <script>
            (function () {
                // messaggio passato dal flow pagamento -> carrello (solo una volta)
                try {
                    const msg = sessionStorage.getItem("cart_notice");
                    if (!msg) return;

                    const box = document.getElementById("cart-notice");
                    if (!box) return;

                    box.textContent = msg;
                    box.classList.remove("hidden");

                    // consumo il messaggio per non mostrarlo ai refresh successivi
                    sessionStorage.removeItem("cart_notice");
                } catch (e) {}
            })();
        </script>

        @if(count($cart) === 0)
            <p class="opacity-70">Il tuo carrello è vuoto.</p>
        @else
            <table id="cart-table" class="w-full border-collapse">
                <thead>
                    <tr class="border-b">
                        <th class="text-left p-2">Prodotto</th>
                        <th class="text-left p-2">Prezzo</th>
                        <th class="text-left p-2">Quantità</th>
                        <th class="text-left p-2">Totale</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody id="cart-body">
                    @foreach($cart as $key => $item)
                        {{-- totale riga iniziale dal server, poi aggiornato live --}}
                        <tr class="border-b"
                            x-data="{ itemTotal: {{ $item['price'] * $item['qty'] }} }">

                            <td class="p-2">{{ $item['name'] }}</td>
                            <td class="p-2">€ {{ number_format($item['price'], 2, ',', '.') }}</td>

                            {{-- qty live: POST cart.update con FormData + CSRF --}}
                            <td class="p-2">
                                <input type="number" value="{{ $item['qty'] }}" min="1"
                                       class="w-16 border rounded px-2 py-1 text-center"
                                       @change="
                                          let formData = new FormData();
                                          formData.append('key', '{{ $key }}');
                                          formData.append('qty', $event.target.value);
                                          formData.append('_token', '{{ csrf_token() }}');

                                          fetch('{{ route('cart.update') }}', {
                                              method: 'POST',
                                              headers: {
                                                  'Accept': 'application/json',
                                                  'X-Requested-With': 'XMLHttpRequest'
                                              },
                                              body: formData
                                          })
                                          .then(async (res) => {
                                              const data = await res.json().catch(() => ({}));
                                              if (!res.ok) throw data;
                                              return data;
                                          })
                                          .then(data => {
                                              if (data?.success) {
                                                  itemTotal = data.itemTotal;
                                                  cartTotal = data.cartTotal;

                                                  // aggiorna badge navbar
                                                  window.dispatchEvent(new CustomEvent('cart:updated', {
                                                      detail: { count: data.cartCount ?? 0 }
                                                  }));
                                              }
                                          })
                                          .catch(err => {
                                              console.error(err);
                                          });
                                       ">
                            </td>

                            {{-- totale riga reattivo --}}
                            <td class="p-2">
                                € <span x-text="itemTotal.toFixed(2).replace('.', ',')"></span>
                            </td>

                            {{-- remove live: POST cart.remove, rimuove la <tr> e aggiorna totale --}}
                            <td class="p-2">
                                <button type="button"
                                        class="text-red-600 hover:underline"
                                        @click="
                                            const row = $el.closest('tr');

                                            let formData = new FormData();
                                            formData.append('key', '{{ $key }}');
                                            formData.append('_token', '{{ csrf_token() }}');

                                            fetch('{{ route('cart.remove') }}', {
                                                method: 'POST',
                                                headers: {
                                                    'Accept': 'application/json',
                                                    'X-Requested-With': 'XMLHttpRequest'
                                                },
                                                body: formData
                                            })
                                            .then(async (res) => {
                                                const data = await res.json().catch(() => ({}));
                                                if (!res.ok) throw data;
                                                return data;
                                            })
                                            .then(data => {
                                                if (data?.success) {
                                                    row.remove();
                                                    cartTotal = data.cartTotal;

                                                    // aggiorna badge navbar
                                                    window.dispatchEvent(new CustomEvent('cart:updated', {
                                                        detail: { count: data.cartCount ?? 0 }
                                                    }));

                                                    // se finisce il carrello, nascondo blocchi e mostro il messaggio vuoto
                                                    const rowsLeft = document.querySelectorAll('#cart-body tr').length;
                                                    if (rowsLeft === 0 || cartTotal === 0) {
                                                        document.querySelector('#cart-table').classList.add('hidden');
                                                        document.querySelector('#cart-summary').classList.add('hidden');
                                                        document.querySelector('#cart-cta').classList.add('hidden');
                                                        document.querySelector('#empty-cart-msg').classList.remove('hidden');
                                                    }
                                                }
                                            })
                                            .catch(err => {
                                                console.error(err);
                                            });
                                        ">
                                    Rimuovi
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- totale carrello reattivo (iniziale dal server, poi aggiornato live) --}}
            <div id="cart-summary" class="mt-6 text-right text-xl font-bold">
                Totale: € <span x-text="cartTotal.toFixed(2).replace('.', ',')"></span>
            </div>

            {{-- CTA checkout: passa al flow CheckoutController@show --}}
            <div id="cart-cta" class="mt-6 text-right">
                <a href="{{ route('checkout.show') }}"
                   class="inline-flex items-center px-5 py-3 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition">
                    Procedi al checkout
                </a>
            </div>
        @endif

        {{-- fallback quando l'ultima riga viene rimossa via JS --}}
        <p id="empty-cart-msg" class="opacity-70 hidden">Il tuo carrello è vuoto.</p>
    </div>
</x-app-layout>
