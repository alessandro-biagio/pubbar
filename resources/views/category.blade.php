<x-app-layout>
    <x-slot name="title">{{ $category->name }}</x-slot>
    <div class="max-w-7xl mx-auto px-6 py-10">

        {{-- HEADER --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl sm:text-4xl font-semibold text-slate-900 tracking-tight">
                    {{ $category->name }}
                </h1>

                @if($category->description)
                    <p class="mt-2 text-slate-700 text-sm sm:text-base max-w-3xl">
                        {{ $category->description }}
                    </p>
                @endif
            </div>

            {{-- Bottone back --}}
            <a href="{{ route('home') }}#categorie"
               class="inline-flex items-center gap-2 rounded-lg
                      border border-slate-400 bg-slate-100
                      px-4 py-2
                      text-sm font-medium text-slate-800
                      hover:bg-slate-200 hover:border-slate-500
                      transition">
                <span class="text-base">←</span>
                Torna alle categorie
            </a>
        </div>

        {{-- GRID --}}
        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($products as $p)
                <div class="rounded-2xl border border-slate-300 bg-white overflow-hidden hover:border-slate-400 transition">

                    {{-- Immagine --}}
                    <div class="bg-slate-200">
                        <div class="aspect-[4/3] w-full p-3">
                            @if($p->image_path)
                                <img
                                    src="{{ Storage::url($p->image_path) }}"
                                    alt="{{ $p->name }}"
                                    loading="lazy"
                                    class="h-full w-full object-contain rounded-lg bg-white"
                                >
                            @else
                                <div class="h-full w-full flex items-center justify-center rounded-lg bg-white text-sm text-slate-500">
                                    Nessuna immagine
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Contenuto --}}
                    <div class="p-5">
                        {{-- Nome --}}
                        <h3 class="text-lg font-semibold text-slate-900 leading-snug">
                            {{ $p->name }}
                        </h3>

                        {{-- Descrizione --}}
                        @if($p->description)
                            <p class="mt-2 text-sm text-slate-700 line-clamp-2">
                                {{ $p->description }}
                            </p>
                        @endif

                        {{-- Varianti (se presenti) --}}
                        @if($p->variants->count() > 0)
                            <details class="mt-4">
                                <summary class="cursor-pointer select-none text-sm font-medium text-slate-800 hover:text-slate-900">
                                    <span class="underline underline-offset-4 decoration-slate-400">
                                        Vedi varianti
                                    </span>
                                    <span class="text-xs text-slate-600">({{ $p->variants->count() }})</span>
                                </summary>

                                <ul class="mt-3 space-y-2">
                                    @foreach($p->variants as $v)
                                        <li class="flex items-center justify-between gap-3 rounded-lg border border-slate-300 bg-slate-100 px-3 py-2">
                                            <span class="text-sm text-slate-800">
                                                {{ $v->name }}
                                                @if($v->volume_ml)
                                                    <span class="text-slate-600">({{ $v->volume_ml }} ml)</span>
                                                @endif
                                            </span>
                                            <span class="text-sm font-semibold text-slate-900 whitespace-nowrap">
                                                € {{ number_format($v->price, 2, ',', '.') }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        @endif

                        {{-- Footer: PREZZO + CTA --}}
                        <div class="mt-5 pt-4 border-t border-slate-200 flex items-center justify-between">
                            <div class="text-base font-semibold text-slate-900">
                                @if($p->variants->count() > 0)
                                    Da € {{ number_format($p->min_price, 2, ',', '.') }}
                                @else
                                    € {{ number_format($p->price, 2, ',', '.') }}
                                @endif
                            </div>

                            <a href="{{ route('product.show', $p->slug) }}"
                               class="inline-flex items-center gap-2 rounded-lg
                                      px-3 py-2 border border-slate-400
                                      text-sm font-medium text-slate-800
                                      hover:bg-slate-200 transition">
                                Dettagli →
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-slate-700">Nessun prodotto disponibile.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
