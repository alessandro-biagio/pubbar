<section
    x-data="{
        current: '',
        password: '',
        confirm: '',

        touched: {
            current: false,
            password: false,
            confirm: false,
        },

        errors: {
            current: '',
            password: '',
            confirm: '',
        },

        specialRe: /[^A-Za-z0-9]/, // almeno un carattere speciale

        validate() {
            // current
            this.errors.current = this.current.length === 0
                ? 'Inserisci la password attuale.'
                : '';

            // password: min 8 + speciale
            if (this.password.length === 0) {
                this.errors.password = 'Inserisci la nuova password.';
            } else if (this.password.length < 8) {
                this.errors.password = 'La nuova password deve contenere almeno 8 caratteri.';
            } else if (!this.specialRe.test(this.password)) {
                this.errors.password = 'La nuova password deve contenere almeno un carattere speciale (es. ! @ # ?).';
            } else {
                this.errors.password = '';
            }

            // confirm
            this.errors.confirm = this.confirm.length === 0
                ? 'Conferma la nuova password.'
                : (this.password !== this.confirm ? 'Le password non coincidono.' : '');
        },

        isValid() {
            return !this.errors.current &&
                   !this.errors.password &&
                   !this.errors.confirm &&
                   this.current && this.password && this.confirm;
        },

        showError(field) {
            return this.touched[field] && !!this.errors[field];
        }
    }"
    x-init="validate()"
>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Aggiorna password
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Usa una password lunga e sicura per proteggere il tuo account.
        </p>
    </header>

    <form
        method="POST"
        action="{{ route('password.update') }}"
        class="mt-6 space-y-6"
        @input="validate()"
    >
        @csrf
        @method('PUT')

        {{-- Password attuale --}}
        <div>
            <x-input-label for="update_password_current_password" value="Password attuale" />
            <x-text-input
                id="update_password_current_password"
                name="current_password"
                type="password"
                class="mt-1 block w-full"
                autocomplete="current-password"
                x-model="current"
                @focus="touched.current = true"
                @blur="touched.current = true; validate()"
            />

            {{-- Live error (solo dopo focus/blur) --}}
            <p x-show="showError('current')" class="mt-2 text-sm text-red-600" x-text="errors.current"></p>

            {{-- Server error --}}
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        {{-- Nuova password --}}
        <div>
            <x-input-label for="update_password_password" value="Nuova password" />
            <x-text-input
                id="update_password_password"
                name="password"
                type="password"
                class="mt-1 block w-full"
                autocomplete="new-password"
                x-model="password"
                @focus="touched.password = true"
                @blur="touched.password = true; validate()"
            />

            {{-- Hint leggero quando il campo è stato toccato e NON c'è errore --}}
            <p x-show="touched.password && !errors.password" class="mt-2 text-xs text-gray-500">
                Minimo 8 caratteri e almeno 1 carattere speciale (es. ! @ # ?).
            </p>

            {{-- Live error (solo dopo focus/blur) --}}
            <p x-show="showError('password')" class="mt-2 text-sm text-red-600" x-text="errors.password"></p>

            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        {{-- Conferma password --}}
        <div>
            <x-input-label for="update_password_password_confirmation" value="Conferma nuova password" />
            <x-text-input
                id="update_password_password_confirmation"
                name="password_confirmation"
                type="password"
                class="mt-1 block w-full"
                autocomplete="new-password"
                x-model="confirm"
                @focus="touched.confirm = true"
                @blur="touched.confirm = true; validate()"
            />

            <p x-show="showError('confirm')" class="mt-2 text-sm text-red-600" x-text="errors.confirm"></p>

            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        {{-- Azioni --}}
        <div class="flex items-center gap-4">
            <x-primary-button
                x-bind:disabled="!isValid()"
                x-bind:class="!isValid() ? 'opacity-50 cursor-not-allowed' : ''"
            >
                Salva
            </x-primary-button>
        </div>
    </form>
</section>
