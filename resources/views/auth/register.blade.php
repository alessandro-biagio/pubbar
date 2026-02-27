<x-guest-layout>
    <x-slot name="title">Registrazione</x-slot>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('registerForm', (serverErrors = {}) => ({
                name: '',
                email: '',
                password: '',
                confirm: '',

                // stato interazione campi (mostro errori live solo dopo che l'utente ci passa)
                touched: { name: false, email: false, password: false, confirm: false },
                errors:  { name: '', email: '', password: '', confirm: '' },

                // errori server dopo submit (MessageBag -> array JS)
                serverErrors: {
                    name: serverErrors.name || [],
                    email: serverErrors.email || [],
                    password: serverErrors.password || [],
                    confirm: serverErrors.confirm || [],
                },
                hideServer: { name: false, email: false, password: false, confirm: false },

                emailRe: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                specialRe: /[^A-Za-z0-9]/, // requisito: almeno 1 carattere speciale

                init() {
                    // validazione iniziale (utile con old())
                    this.validate();
                },

                // validazione live lato client (la validazione vera resta quella server)
                validate(field = null) {
                    if (!field || field === 'name') {
                        const n = (this.name || '').trim();
                        if (!n) this.errors.name = 'Inserisci il nome.';
                        else if (n.length < 2) this.errors.name = 'Il nome deve contenere almeno 2 caratteri.';
                        else this.errors.name = '';
                    }

                    if (!field || field === 'email') {
                        const e = (this.email || '').trim();
                        if (!e) this.errors.email = 'Inserisci l’email.';
                        else if (!this.emailRe.test(e)) this.errors.email = 'Inserisci un indirizzo email valido.';
                        else this.errors.email = '';
                    }

                    if (!field || field === 'password') {
                        if (!this.password) this.errors.password = 'Inserisci la password.';
                        else if (this.password.length < 8) this.errors.password = 'La password deve contenere almeno 8 caratteri.';
                        else if (!this.specialRe.test(this.password)) this.errors.password = 'La password deve contenere almeno un carattere speciale.';
                        else this.errors.password = '';

                        // se cambia password, ricalcolo anche conferma
                        if (field === 'password') this.validate('confirm');
                    }

                    if (!field || field === 'confirm') {
                        if (!this.confirm) this.errors.confirm = 'Conferma la password.';
                        else if (this.confirm !== this.password) this.errors.confirm = 'Le password non coincidono.';
                        else this.errors.confirm = '';
                    }
                },

                showLive(field) {
                    return this.touched[field] && !!this.errors[field];
                },

                showServer(field) {
                    return (this.serverErrors[field]?.length > 0) && !this.hideServer[field];
                },

                onFocus(field) {
                    // quando entro: nascondo l'errore server e pulisco live
                    this.touched[field] = true;
                    this.errors[field] = '';
                    this.hideServer[field] = true;
                },

                onInput(field) {
                    // mentre scrivo: nascondo server error e valido il campo
                    this.hideServer[field] = true;
                    this.validate(field);
                },

                onBlur(field) {
                    this.touched[field] = true;
                    this.validate(field);
                },

                // abilitazione bottone
                isValid() {
                    return !this.errors.name &&
                           !this.errors.email &&
                           !this.errors.password &&
                           !this.errors.confirm &&
                           (this.name || '').trim() &&
                           (this.email || '').trim() &&
                           this.password &&
                           this.confirm;
                },
            }));
        });
    </script>

    <form method="POST" action="{{ route('register') }}"
          {{-- passo gli errori server ad Alpine (password_confirmation -> confirm) --}}
          x-data="registerForm({
              name: @js($errors->get('name')),
              email: @js($errors->get('email')),
              password: @js($errors->get('password')),
              confirm: @js($errors->get('password_confirmation')),
          })"
          {{-- ripristino valori dopo submit fallito --}}
          x-init="
              name = @js(old('name',''));
              email = @js(old('email',''));
              init();
          "
    >
        {{-- token CSRF --}}
        @csrf

        {{-- Nome --}}
        <div>
            <x-input-label for="name" value="Nome" />
            <x-text-input
                id="name"
                class="block mt-1 w-full"
                type="text"
                name="name"
                required
                autofocus
                autocomplete="name"
                x-model="name"
                @focus="onFocus('name')"
                @input="onInput('name')"
                @blur="onBlur('name')"
            />

            {{-- errore live --}}
            <p x-show="showLive('name')" class="mt-2 text-sm text-red-600" x-text="errors.name"></p>

            {{-- errore server --}}
            <div x-show="showServer('name')" class="mt-2">
                <x-input-error :messages="$errors->get('name')" />
            </div>
        </div>

        {{-- Email --}}
        <div class="mt-4">
            <x-input-label for="email" value="Email" />
            <x-text-input
                id="email"
                class="block mt-1 w-full"
                type="email"
                name="email"
                required
                autocomplete="username"
                x-model="email"
                @focus="onFocus('email')"
                @input="onInput('email')"
                @blur="onBlur('email')"
            />

            {{-- errore live --}}
            <p x-show="showLive('email')" class="mt-2 text-sm text-red-600" x-text="errors.email"></p>

            {{-- errore server --}}
            <div x-show="showServer('email')" class="mt-2">
                <x-input-error :messages="$errors->get('email')" />
            </div>
        </div>

        {{-- Password --}}
        <div class="mt-4">
            <x-input-label for="password" value="Password" />

            <x-text-input
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                x-model="password"
                @focus="onFocus('password')"
                @input="onInput('password')"
                @blur="onBlur('password')"
            />

            {{-- errore live --}}
            <p x-show="showLive('password')" class="mt-2 text-sm text-red-600" x-text="errors.password"></p>

            {{-- errore server --}}
            <div x-show="showServer('password')" class="mt-2">
                <x-input-error :messages="$errors->get('password')" />
            </div>

            {{-- reminder requisiti (stesso check in JS + validation server) --}}
            <p class="mt-2 text-xs text-gray-600">
                Minimo 8 caratteri e almeno 1 carattere speciale.
            </p>
        </div>

        {{-- Conferma Password --}}
        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Conferma password" />

            <x-text-input
                id="password_confirmation"
                class="block mt-1 w-full"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                x-model="confirm"
                @focus="onFocus('confirm')"
                @input="onInput('confirm')"
                @blur="onBlur('confirm')"
            />

            {{-- errore live --}}
            <p x-show="showLive('confirm')" class="mt-2 text-sm text-red-600" x-text="errors.confirm"></p>

            {{-- errore server: arriva su password_confirmation --}}
            <div x-show="showServer('confirm')" class="mt-2">
                <x-input-error :messages="$errors->get('password_confirmation')" />
            </div>
        </div>

        {{-- Link + Bottone --}}
        <div class="flex items-center justify-between mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md
                      focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
               href="{{ route('login') }}">
                Hai già un account? Accedi
            </a>

            <x-primary-button class="ms-4"
                x-bind:disabled="!isValid()"
                x-bind:class="!isValid() ? 'opacity-50 cursor-not-allowed' : ''"
            >
                Registrati
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
