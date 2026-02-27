<x-app-layout>
    <x-slot name="title">Home</x-slot>
    {{-- ===================== HERO ===================== --}}
    <section class="bg-white">
        <div class="mx-auto max-w-7xl px-6 py-14 sm:py-20">
            <div class="grid items-end gap-10 lg:grid-cols-12">
                {{-- Copy --}}
                <div class="lg:col-span-7">
                    <p class="text-xs tracking-[0.30em] text-slate-500 uppercase">
                        Hosteria • Cibo & Birra Artigianale
                    </p>

                    <h1 class="mt-4 text-5xl sm:text-6xl font-extralight tracking-tight text-slate-950">
                        Sapori autentici.
                        <br class="hidden sm:block">
                        Birre artigianali.
                    </h1>

                    <p class="mt-6 text-lg font-light leading-relaxed text-slate-600 max-w-2xl">
                        Una selezione curata di specialità: scegli una categoria e scopri il menù.
                        Pochi fronzoli, tanta qualità.
                    </p>

                    <div class="mt-10 flex flex-wrap items-center gap-3">
                        <a href="#categorie"
                           class="rounded-full bg-slate-950 px-6 py-3 text-sm font-light text-white hover:bg-slate-900 transition">
                            Esplora categorie
                        </a>

                        <a href="#categorie"
                           class="rounded-full border border-slate-200 px-6 py-3 text-sm font-light text-slate-900 hover:border-slate-300 hover:bg-slate-50 transition">
                            Guarda il menù
                        </a>
                    </div>

                    {{-- Micro info --}}
                    <div class="mt-10 flex flex-wrap gap-x-8 gap-y-3 text-sm text-slate-500">
                        <div class="flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            Ingredienti selezionati
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            Birre artigianali
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                            Servizio rapido
                        </div>
                    </div>
                </div>

                {{-- Hero image --}}
                <div class="lg:col-span-5">
                    <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-slate-100 shadow-sm">
                        <img
                            src="https://images.unsplash.com/photo-1514933651103-005eec06c04b?q=80&w=2574"
                            alt="Bar Hero"
                            class="h-[360px] w-full object-cover"
                            loading="lazy"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/35 to-transparent"></div>

                        <div class="absolute bottom-0 left-0 right-0 p-6">
                            <p class="text-xs tracking-[0.25em] uppercase text-white/80">
                                Nel cuore della città
                            </p>
                            <p class="mt-2 text-lg font-light text-white">
                                Atmosfera calda, sapori veri.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Flash messages --}}
            <div class="mt-10">
                @if (session('success'))
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-900">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-900">
                        {{ session('error') }}
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ===================== CATEGORIE ===================== --}}
    <section id="categorie" class="bg-white">
        <div class="mx-auto max-w-7xl px-6 pb-16 sm:pb-24">

            <div class="flex items-end justify-between gap-6 border-t border-slate-200 pt-10">
                <div>
                    <p class="text-xs tracking-[0.30em] text-slate-500 uppercase">Categorie</p>
                    <h2 class="mt-3 text-3xl sm:text-4xl font-extralight tracking-tight text-slate-950">
                        Esplora il menù
                    </h2>
                </div>
                <p class="hidden sm:block text-sm font-light text-slate-500 max-w-md text-right">
                    Seleziona una categoria per vedere i prodotti disponibili.
                </p>
            </div>

            <div class="mt-10 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse($categories as $c)
                    <a href="{{ route('category.show', $c->slug) }}"
                       class="group rounded-3xl border border-slate-200 bg-white overflow-hidden hover:border-slate-300 hover:shadow-lg transition">
                        {{-- Image --}}
                        <div class="relative h-56 bg-slate-100">
                            @if($c->image_path)
                                <img src="{{ Storage::url($c->image_path) }}"
                                     alt="{{ $c->name }}"
                                     class="h-full w-full object-cover transition duration-700 group-hover:scale-[1.03]"
                                     loading="lazy">
                            @else
                                <div class="h-full w-full flex items-center justify-center bg-slate-100">
                                    <span class="text-sm text-slate-400">Nessuna immagine</span>
                                </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="p-6">
                            <div class="flex items-center justify-between gap-4">
                                <h3 class="text-xl font-light text-slate-950">
                                    {{ $c->name }}
                                </h3>
                                <span class="text-[11px] tracking-widest text-slate-400 uppercase">
                                    {{ $c->slug }}
                                </span>
                            </div>

                            @if($c->description)
                                <p class="mt-3 text-sm font-light leading-relaxed text-slate-600 line-clamp-3">
                                    {{ $c->description }}
                                </p>
                            @endif

                            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4">
                                <span class="text-sm font-light text-slate-600">Apri categoria</span>
                                <span class="inline-flex items-center gap-2 text-sm text-slate-950">
                                    <span class="h-2 w-2 rounded-full bg-slate-950 transition group-hover:translate-x-0.5"></span>
                                    →
                                </span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full rounded-3xl border border-slate-200 bg-slate-50 p-12 text-center">
                        <p class="text-slate-500 font-light">Nessuna categoria disponibile al momento</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ===================== FOOTER-INFO ===================== --}}
    <section class="bg-slate-50">
        <div class="mx-auto max-w-7xl px-6 py-14 sm:py-20">
            <div class="grid gap-8 lg:grid-cols-3">
                <div class="rounded-3xl border border-slate-200 bg-white p-8">
                    <p class="text-xs tracking-[0.30em] text-slate-500 uppercase">Qualità</p>
                    <h3 class="mt-3 text-2xl font-extralight text-slate-950">Ingredienti selezionati</h3>
                    <p class="mt-4 text-sm font-light leading-relaxed text-slate-600">
                        Materie prime scelte e ricette curate per un’esperienza semplice e autentica.
                    </p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-8">
                    <p class="text-xs tracking-[0.30em] text-slate-500 uppercase">Birre</p>
                    <h3 class="mt-3 text-2xl font-extralight text-slate-950">Artigianali e speciali</h3>
                    <p class="mt-4 text-sm font-light leading-relaxed text-slate-600">
                        Selezione in continua rotazione: IPA, stout, sour e proposte stagionali.
                    </p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-8">
                    <p class="text-xs tracking-[0.30em] text-slate-500 uppercase">Atmosfera</p>
                    <h3 class="mt-3 text-2xl font-extralight text-slate-950">Conviviale</h3>
                    <p class="mt-4 text-sm font-light leading-relaxed text-slate-600">
                        Un posto dove stare bene: luci calde, musica giusta, compagnia migliore.
                    </p>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
