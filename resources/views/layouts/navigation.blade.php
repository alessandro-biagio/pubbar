<nav x-data="{ open: false }" class="bg-white/90 backdrop-blur border-b border-slate-200">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- Left: Logo + Links -->
            <div class="flex items-center gap-10">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                        <x-application-logo class="block h-9 w-auto fill-current text-slate-900" />
                        <span class="hidden sm:inline text-sm tracking-[0.20em] uppercase text-slate-500 font-light">
                            Hosteria
                        </span>
                    </a>
                </div>

                <!-- Desktop Links -->
                <div class="hidden sm:flex sm:items-center gap-8">
                    <x-nav-link
                        :href="route('home')"
                        :active="request()->routeIs('home')"
                        class="!border-b-0 !px-0 !py-0"
                    >
                        <span class="text-sm font-light tracking-wide {{ request()->routeIs('home') ? 'text-slate-950' : 'text-slate-600 hover:text-slate-950' }}">
                            {{ __('Home') }}
                        </span>
                    </x-nav-link>

                    <div x-data="{ catOpen: false }" class="relative">
                        <button
                            type="button"
                            @click="catOpen = !catOpen"
                            @keydown.escape.window="catOpen = false"
                            @click.away="catOpen = false"
                            class="inline-flex items-center gap-2 text-sm font-light tracking-wide
                                   text-slate-600 hover:text-slate-950 transition"
                        >
                            Categorie
                            <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                      d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                                      clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div
                            x-cloak
                            x-show="catOpen"
                            x-transition.origin.top.left
                            class="absolute left-0 mt-3 w-64 overflow-hidden rounded-2xl border border-slate-200
                                   bg-white shadow-lg shadow-slate-900/5 z-50"
                        >
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-[11px] tracking-[0.25em] uppercase text-slate-500">
                                    Categorie
                                </p>
                            </div>

                            <div class="py-2">
                                @forelse(($categoriesNav ?? []) as $c)
                                    <a
                                        href="{{ route('category.show', $c->slug) }}"
                                        class="flex items-center justify-between px-4 py-2.5 text-sm font-light
                                               text-slate-700 hover:bg-slate-50 hover:text-slate-950 transition"
                                    >
                                        <span>{{ $c->name }}</span>
                                        <span class="text-[11px] tracking-widest uppercase text-slate-400">
                                            {{ $c->slug }}
                                        </span>
                                    </a>
                                @empty
                                    <div class="px-4 py-3 text-sm font-light text-slate-400">
                                        Nessuna categoria
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    @auth
                        @if(auth()->user()->is_staff)
                            <x-nav-link
                                :href="route('staff.dashboard')"
                                :active="request()->routeIs('staff.*')"
                                class="!border-b-0 !px-0 !py-0"
                            >
                                <span class="text-sm font-light tracking-wide {{ request()->routeIs('staff.*') ? 'text-slate-950' : 'text-slate-600 hover:text-slate-950' }}">
                                    {{ __('Dashboard') }}
                                </span>
                            </x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Right: Orders + Cart + Auth -->
            <div class="hidden sm:flex sm:items-center gap-5">
                @auth
                    <a href="{{ route('orders.my') }}" class="text-sm font-light text-slate-600 hover:text-slate-950 transition">
                        I miei ordini
                    </a>
                @endauth

                @php
                    // Badge carrello: somma qty dalla sessione 'cart' (stessa del CartController)
                    $cartCount = collect(session('cart', []))->sum(function ($it) {
                        return (int) ($it['qty'] ?? 0);
                    });
                @endphp

                <a href="{{ route('cart.index') }}"
                   class="relative inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-2
                          hover:bg-slate-50 hover:border-slate-300 transition"
                   aria-label="Vai al carrello">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-800" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25h9.75m-9.75 0L6.056 6.272A1.125 1.125 0 0 0 4.95 5.25H3.75m3.75 9l-.563 3.094A1.125 1.125 0 0 0 8.03 18.75h8.69a1.125 1.125 0 0 0 1.093-.906l1.312-6.562A1.125 1.125 0 0 0 18.03 9.75H7.5m10.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm-9 0a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                    </svg>

                    <span id="cart-badge"
                          class="ml-2 inline-flex items-center justify-center rounded-full bg-slate-950 text-white
                                 text-[11px] font-medium px-2 py-0.5 {{ $cartCount > 0 ? '' : 'hidden' }}">
                        {{ $cartCount }}
                    </span>
                </a>

                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2
                                       text-sm font-light text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition"
                            >
                                <span>{{ Auth::user()->name }}</span>
                                <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profilo') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @endauth

                @guest
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}" class="text-sm font-light text-slate-600 hover:text-slate-950 transition">
                            {{ __('Log in') }}
                        </a>
                        <a href="{{ route('register') }}"
                           class="rounded-full border border-slate-200 px-4 py-2 text-sm font-light text-slate-900
                                  hover:bg-slate-50 hover:border-slate-300 transition">
                            {{ __('Registrati') }}
                        </a>
                    </div>
                @endguest
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = !open"
                    class="inline-flex items-center justify-center p-2 rounded-xl border border-slate-200 bg-white
                           text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden border-t border-slate-200 bg-white">
        <div class="px-4 pt-4 pb-6 space-y-2">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">
                {{ __('Home') }}
            </x-responsive-nav-link>

            <div class="pt-3">
                <div class="px-2 py-2 text-[11px] tracking-[0.25em] uppercase text-slate-500">
                    Categorie
                </div>

                <div class="mt-2 space-y-1">
                    @forelse(($categoriesNav ?? []) as $c)
                        <a href="{{ route('category.show', $c->slug) }}"
                           class="block rounded-xl px-3 py-2 text-sm font-light text-slate-700 hover:bg-slate-50 hover:text-slate-950 transition">
                            {{ $c->name }}
                        </a>
                    @empty
                        <div class="px-3 py-2 text-sm font-light text-slate-400">
                            Nessuna categoria
                        </div>
                    @endforelse
                </div>
            </div>

            @auth
                @if(auth()->user()->is_staff)
                    <x-responsive-nav-link :href="route('staff.dashboard')" :active="request()->routeIs('staff.*')">
                        {{ __('Dashboard') }}
                    </x-responsive-nav-link>
                @endif

                <x-responsive-nav-link :href="route('orders.my')" :active="request()->routeIs('orders.my')">
                    I miei ordini
                </x-responsive-nav-link>
            @endauth

            @php
                $cartCountMobile = collect(session('cart', []))->sum(function ($it) {
                    return (int) ($it['qty'] ?? 0);
                });
            @endphp

            <x-responsive-nav-link :href="route('cart.index')" :active="request()->routeIs('cart.index')">
                Carrello
                <span id="cart-badge-mobile"
                      class="ml-2 inline-flex items-center justify-center text-[11px] font-medium px-2 py-0.5 rounded-full bg-slate-950 text-white {{ $cartCountMobile > 0 ? '' : 'hidden' }}">
                    {{ $cartCountMobile }}
                </span>
            </x-responsive-nav-link>
        </div>

        <div class="border-t border-slate-200 px-4 py-4">
            @auth
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-sm font-light text-slate-900">{{ Auth::user()->name }}</div>
                    <div class="text-xs font-light text-slate-600">{{ Auth::user()->email }}</div>

                    <div class="mt-3 flex gap-2">
                        <a href="{{ route('profile.edit') }}"
                           class="flex-1 text-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-light text-slate-900 hover:bg-slate-50 transition">
                            Profilo
                        </a>

                        <form method="POST" action="{{ route('logout') }}" class="flex-1">
                            @csrf
                            <button type="submit"
                                class="w-full rounded-full bg-slate-950 px-4 py-2 text-sm font-light text-white hover:bg-slate-900 transition">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('login') }}"
                       class="text-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-light text-slate-900 hover:bg-slate-50 transition">
                        Log in
                    </a>
                    <a href="{{ route('register') }}"
                       class="text-center rounded-full bg-slate-950 px-4 py-2 text-sm font-light text-white hover:bg-slate-900 transition">
                        Registrati
                    </a>
                </div>
            @endauth
        </div>
    </div>

    <script>
        (function () {
            // aggiorna badge carrello in navbar (desktop + mobile) dopo update/remove via AJAX
            function setBadge(el, count) {
                if (!el) return;
                const n = parseInt(count || 0, 10);

                if (n > 0) {
                    el.textContent = n;
                    el.classList.remove('hidden');
                } else {
                    el.textContent = '';
                    el.classList.add('hidden');
                }
            }

            window.addEventListener('cart:updated', function (e) {
                const count = e?.detail?.count ?? 0;
                setBadge(document.getElementById('cart-badge'), count);
                setBadge(document.getElementById('cart-badge-mobile'), count);
            });
        })();
    </script>
</nav>
