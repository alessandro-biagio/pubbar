<x-guest-layout>
    <x-slot name="title">Login</x-slot>

    {{-- errore credenziali: associato a "email" --}}
    @if ($errors->has('email'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first('email') }}
        </div>
    @endif

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('loginForm', (serverEmailErrors = [], serverPasswordErrors = []) => ({
                email: '',
                password: '',

                // stato interazione campi
                touched: { email: false, password: false },
                errors: { email: '', password: '' },

                // errori restituiti dal server dopo submit
                serverErrors: {
                    email: serverEmailErrors || [],
                    password: serverPasswordErrors || [],
                },
                hideServer: { email: false, password: false },

                emailRe: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,

                init() {
                    // validazione iniziale (utile con old())
                    this.validate();
                },

                // validazione live lato client
                validate(field = null) {
                    if (!field || field === 'email') {
                        const e = (this.email || '').trim();
                        if (!e) this.errors.email = 'Inserisci l’email.';
                        else if (!this.emailRe.test(e)) this.errors.email = 'Inserisci un indirizzo email valido.';
                        else this.errors.email = '';
                    }

                    if (!field || field === 'password') {
                        this.errors.password = this.password ? '' : 'Inserisci la password.';
                    }
                },

                showLive(field) {
                    return this.touched[field] && !!this.errors[field];
                },

                showServer(field) {
                    return (this.serverErrors[field]?.length > 0) && !this.hideServer[field];
                },

                onFocus(field) {
                    this.touched[field] = true;
                    this.errors[field] = '';
                    this.hideServer[field] = true;
                },

                onInput(field) {
                    this.hideServer[field] = true;
                    this.validate(field);
                },

                onBlur(field) {
                    this.touched[field] = true;
                    this.validate(field);
                },

                // abilita submit solo se campi compilati e senza errori
                isValid() {
                    return !this.errors.email &&
                           !this.errors.password &&
                           (this.email || '').trim() &&
                           this.password;
                },
            }));
        });
    </script>

    <form method="POST" action="{{ route('login') }}"
          {{-- passo al JS gli errori server --}}
          x-data="loginForm(@js($errors->get('email')), @js($errors->get('password')))"
          {{-- ripristino email dopo submit fallito --}}
          x-init="email = @js(old('email', '')); init();"
          @input="validate()"
    >
        {{-- token CSRF --}}
        @csrf

        {{-- EMAIL --}}
        <div>
            <x-input-label for="email" value="Email" />

            <x-text-input
                id="email"
                class="block mt-1 w-full"
                type="email"
                name="email"
                required
                autofocus
                autocomplete="username"
                x-model="email"
                @focus="onFocus('email')"
                @input="onInput('email')"
                @blur="onBlur('email')"
            />

            {{-- errore client --}}
            <p x-show="showLive('email')" class="mt-2 text-sm text-red-600" x-text="errors.email"></p>

            {{-- errore server --}}
            <div x-show="showServer('email')" class="mt-2">
                <x-input-error :messages="$errors->get('email')" />
            </div>
        </div>

        {{-- PASSWORD --}}
        <div class="mt-4">
            <x-input-label for="password" value="Password" />

            <x-text-input
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                x-model="password"
                @focus="onFocus('password')"
                @input="onInput('password')"
                @blur="onBlur('password')"
            />

            {{-- errore client --}}
            <p x-show="showLive('password')" class="mt-2 text-sm text-red-600" x-text="errors.password"></p>

            {{-- errore server --}}
            <div x-show="showServer('password')" class="mt-2">
                <x-input-error :messages="$errors->get('password')" />
            </div>
        </div>

        {{-- LINK + SUBMIT --}}
        <div class="flex items-center justify-between mt-6">
            @if (Route::has('register'))
                <a href="{{ route('register') }}"
                   class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md
                          focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Non hai un account? Registrati
                </a>
            @else
                <span></span>
            @endif

            <x-primary-button
                x-bind:disabled="!isValid()"
                x-bind:class="!isValid() ? 'opacity-50 cursor-not-allowed' : ''"
            >
                Accedi
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
