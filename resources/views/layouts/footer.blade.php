<footer class="mt-auto bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- Top grid --}}
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Brand / intro -->
            <div>
                <div class="flex items-center gap-3">
                    <x-application-logo class="h-8 w-auto fill-current text-slate-900" />
                    <span class="text-xs tracking-[0.25em] uppercase text-slate-500 font-light">
                        Hosteria
                    </span>
                </div>
                <p class="mt-4 text-sm font-light leading-relaxed text-slate-600">
                    Sapori autentici e birre artigianali. Un posto semplice, curato e conviviale.
                </p>

                {{-- Social --}}
                <div class="mt-6 flex items-center gap-3">
                    <a href="#" aria-label="Instagram"
                       class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white p-2
                              text-slate-600 hover:text-slate-950 hover:bg-slate-50 hover:border-slate-300 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                            <circle cx="12" cy="12" r="4"></circle>
                            <circle cx="17" cy="7" r="1.3" fill="currentColor"></circle>
                        </svg>
                    </a>

                    <a href="#" aria-label="Facebook"
                       class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white p-2
                              text-slate-600 hover:text-slate-950 hover:bg-slate-50 hover:border-slate-300 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 2h-3a5 5 0 00-5 5v3H5v4h2v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3V2z" />
                        </svg>
                    </a>

                    <a href="#" aria-label="TikTok"
                       class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white p-2
                              text-slate-600 hover:text-slate-950 hover:bg-slate-50 hover:border-slate-300 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linejoin="round" stroke-linecap="round"
                                  d="M12 3v12.5a3.5 3.5 0 11-3-3.47M12 8.5a6 6 0 006 6V9a6 6 0 01-6-6" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Navigazione -->
            <div>
                <h3 class="text-xs tracking-[0.25em] uppercase text-slate-500 font-light">
                    Navigazione
                </h3>
                <ul class="mt-4 space-y-2 text-sm font-light">
                    <li><a href="{{ route('home') }}" class="text-slate-600 hover:text-slate-950 transition">Home</a></li>
                    <li><a href="{{ route('cart.index') }}" class="text-slate-600 hover:text-slate-950 transition">Carrello</a></li>
                    @auth
                        <li><a href="{{ route('orders.my') }}" class="text-slate-600 hover:text-slate-950 transition">I miei ordini</a></li>
                        @if(auth()->user()->is_staff)
                            <li><a href="{{ route('staff.dashboard') }}" class="text-slate-600 hover:text-slate-950 transition">Dashboard staff</a></li>
                        @endif
                    @endauth
                </ul>
            </div>

            <!-- Categorie -->
            <div>
                <h3 class="text-xs tracking-[0.25em] uppercase text-slate-500 font-light">
                    Categorie
                </h3>
                <ul class="mt-4 space-y-2 text-sm font-light">
                    @forelse($categoriesNav as $c)
                        <li>
                            <a href="{{ route('category.show', $c->slug) }}"
                               class="text-slate-600 hover:text-slate-950 transition">
                                {{ $c->name }}
                            </a>
                        </li>
                    @empty
                        <li class="text-slate-400 text-sm font-light">Nessuna categoria</li>
                    @endforelse
                </ul>
            </div>

            <!-- Contatti + legale -->
            <div>
                <h3 class="text-xs tracking-[0.25em] uppercase text-slate-500 font-light">
                    Contatti
                </h3>
                <ul class="mt-4 space-y-2 text-sm font-light text-slate-600">
                    <li>
                        Email:
                        <a href="mailto:info@hosteria.test" class="text-slate-600 hover:text-slate-950 transition">
                            info@hosteria.test
                        </a>
                    </li>
                    <li>
                        Tel:
                        <a href="tel:+39000000000" class="text-slate-600 hover:text-slate-950 transition">
                            +39 334 1234 234
                        </a>
                    </li>
                    <li class="text-slate-600">
                        Indirizzo: Via Garibaldi 1, Orzinuovi
                    </li>
                </ul>

                <div class="mt-6">
                    <h3 class="text-xs tracking-[0.25em] uppercase text-slate-500 font-light">
                        Legale
                    </h3>
                    <ul class="mt-4 space-y-2 text-sm font-light">
                        <li><a href="#" class="text-slate-600 hover:text-slate-950 transition">Privacy</a></li>
                        <li><a href="#" class="text-slate-600 hover:text-slate-950 transition">Cookie</a></li>
                        <li><a href="#" class="text-slate-600 hover:text-slate-950 transition">Termini di servizio</a></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Bottom --}}
        <div class="mt-12 border-t border-slate-200 pt-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs font-light text-slate-500">
                © {{ now()->year }} Hosteria — Tutti i diritti riservati.
            </p>
            <p class="text-xs font-light text-slate-400">
                Made with ABD
            </p>
        </div>
    </div>
</footer>
