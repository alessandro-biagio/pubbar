<x-app-layout>
    <x-slot name="title">{{ $product->name }}</x-slot>
    <div class="max-w-5xl mx-auto px-6 py-10">

        {{-- ritorno alla categoria del prodotto --}}
        <a href="{{ route('category.show', $product->category->slug) }}"
           class="inline-flex items-center gap-2 text-sm font-medium text-slate-800
                  hover:text-slate-900 underline underline-offset-4 decoration-slate-400">
            ← Torna a {{ $product->category->name }}
        </a>

        <h1 class="mt-4 text-3xl sm:text-4xl font-semibold text-slate-900 tracking-tight">
            {{ $product->name }}
        </h1>

        {{-- immagine prodotto (path salvato in public disk) --}}
        <div class="mt-5 rounded-2xl border border-slate-300 overflow-hidden bg-slate-200">
            <div class="h-56 sm:h-64 w-full p-3">
                @if($product->image_path)
                    <img
                        src="{{ Storage::url($product->image_path) }}"
                        alt="{{ $product->name }}"
                        class="h-full w-full object-contain rounded-xl bg-white"
                        loading="lazy"
                    >
                @else
                    <div class="h-full w-full flex items-center justify-center rounded-xl bg-white text-sm text-slate-500">
                        Nessuna immagine
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">

            {{-- descrizione --}}
            <div class="rounded-2xl border border-slate-300 bg-white p-6">
                <p class="text-sm font-semibold text-slate-900">Descrizione</p>

                @if($product->description)
                    <p class="mt-3 text-slate-700 text-sm sm:text-base leading-relaxed">
                        {{ $product->description }}
                    </p>
                @else
                    <p class="mt-3 text-slate-600 text-sm">
                        Nessuna descrizione disponibile.
                    </p>
                @endif
            </div>

            {{-- box acquisto: POST a cart.add con product_id + (variant_id) + qty --}}
            <div class="rounded-2xl border border-slate-300 bg-white p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Acquisto</p>
                        <p class="mt-1 text-sm text-slate-700">
                            Seleziona variante e quantità
                        </p>
                    </div>

                    {{-- prezzo mostrato in base a varianti o prezzo base --}}
                    <div class="text-right">
                        @if($product->variants->count() > 0)
                            <div class="text-[11px] uppercase tracking-wider text-slate-600">Da</div>
                            <div class="mt-1 inline-flex items-center rounded-full bg-slate-100 border border-slate-300 px-3 py-1 text-sm font-semibold text-slate-900">
                                € {{ number_format($product->variants->min('price'), 2, ',', '.') }}
                            </div>
                        @else
                            <div class="text-[11px] uppercase tracking-wider text-slate-600">Prezzo</div>
                            <div class="mt-1 inline-flex items-center rounded-full bg-slate-100 border border-slate-300 px-3 py-1 text-sm font-semibold text-slate-900">
                                € {{ number_format($product->price, 2, ',', '.') }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- novalidate: blocco i popup HTML5 e gestisco io il controllo variante --}}
                <form id="add-to-cart-form"
                      action="{{ route('cart.add') }}"
                      method="POST"
                      class="mt-6 space-y-5"
                      novalidate>
                    @csrf

                    {{-- parametro principale per il CartController@add --}}
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    {{-- lista varianti: variant_id viene inviato solo se selezionata --}}
                    @if($product->variants->count() > 0)
                        <div>
                            <label class="block text-sm font-semibold text-slate-900 mb-2">
                                Scegli una variante
                            </label>

                            <div class="space-y-2">
                                @foreach($product->variants as $v)
                                    <label class="flex items-center justify-between gap-4 rounded-xl border border-slate-300 bg-slate-100 px-4 py-3 cursor-pointer hover:bg-slate-200 transition">
                                        <div class="flex items-center gap-3">
                                            {{-- niente required: se manca blocco submit via JS --}}
                                            <input type="radio" name="variant_id" value="{{ $v->id }}">

                                            <span class="text-sm text-slate-800 font-medium">
                                                {{ $v->name }}
                                                @if($v->volume_ml)
                                                    <span class="text-slate-600">({{ $v->volume_ml }} ml)</span>
                                                @endif
                                            </span>
                                        </div>

                                        <span class="text-sm font-semibold text-slate-900">
                                            € {{ number_format($v->price, 2, ',', '.') }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="border-t border-slate-200 pt-5 flex items-end justify-between gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-900 mb-2">
                                Quantità
                            </label>

                            {{-- qty viene validata anche lato server (min 1) --}}
                            <input type="number" name="qty" value="1" min="1"
                                   class="w-28 rounded-xl border border-slate-300 px-3 py-2">
                        </div>

                        {{-- submit classico: redirect home + flash success --}}
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-xl px-5 py-3
                                       bg-slate-900 text-white font-semibold hover:bg-slate-800 transition">
                            Aggiungi al carrello
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    {{-- toast semplice per bloccare submit se manca variant_id --}}
    <div id="variant-toast"
         class="fixed bottom-6 right-6 z-50 hidden max-w-sm rounded-2xl border border-slate-300 bg-white shadow-lg p-4">
        <div class="flex items-start gap-3">
            <div class="mt-0.5 text-slate-900">⚠️</div>

            <div class="flex-1">
                <p class="text-sm font-semibold text-slate-900">Attenzione</p>
                <p id="variant-toast-msg" class="mt-1 text-sm text-slate-700">
                    Selezionare una delle varianti.
                </p>
            </div>

            <button type="button"
                    class="text-slate-500 hover:text-slate-700"
                    onclick="document.getElementById('variant-toast').classList.add('hidden')">
                ✕
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('add-to-cart-form');
            if (!form) return;

            const toast = document.getElementById('variant-toast');
            const msg   = document.getElementById('variant-toast-msg');

            function showToast(text) {
                if (msg) msg.textContent = text;

                toast.classList.remove('hidden');

                // timer globale per non sovrapporre più toast
                clearTimeout(window.__variantToastTimer);
                window.__variantToastTimer = setTimeout(() => {
                    toast.classList.add('hidden');
                }, 3000);
            }

            form.addEventListener('submit', (e) => {
                // se il prodotto ha varianti, variant_id è obbligatorio lato UX
                const hasVariants = {{ $product->variants->count() > 0 ? 'true' : 'false' }};

                if (hasVariants) {
                    const checked = form.querySelector('input[name="variant_id"]:checked');
                    if (!checked) {
                        e.preventDefault();
                        showToast('Selezionare una delle varianti.');
                    }
                }
            });
        });
    </script>
</x-app-layout>
