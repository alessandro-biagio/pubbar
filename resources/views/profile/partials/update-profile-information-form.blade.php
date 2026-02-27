<section
    x-data="{
        name: @js(old('name', $user->name)),
        email: @js(old('email', $user->email)),

        touched: { name: false, email: false },

        errors: { name: '', email: '' },

        emailRe: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,

        validate() {
            // NAME
            const n = (this.name || '').trim();
            if (!n) {
                this.errors.name = 'Inserisci il nome.';
            } else if (n.length < 2) {
                this.errors.name = 'Il nome deve contenere almeno 2 caratteri.';
            } else if (n.length > 255) {
                this.errors.name = 'Il nome è troppo lungo.';
            } else {
                this.errors.name = '';
            }

            // EMAIL
            const e = (this.email || '').trim();
            if (!e) {
                this.errors.email = 'Inserisci l’email.';
            } else if (!this.emailRe.test(e)) {
                this.errors.email = 'Inserisci un indirizzo email valido.';
            } else if (e.length > 255) {
                this.errors.email = 'L’email è troppo lunga.';
            } else {
                this.errors.email = '';
            }
        },

        showError(field) {
            return this.touched[field] && !!this.errors[field];
        },

        isValid() {
            return !this.errors.name &&
                   !this.errors.email &&
                   (this.name || '').trim() &&
                   (this.email || '').trim();
        }
    }"
    x-init="validate()"
>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Informazioni profilo
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Aggiorna le informazioni del tuo profilo e il tuo indirizzo email.
        </p>
    </header>

    <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form
        method="POST"
        action="{{ route('profile.update') }}"
        class="mt-6 space-y-6"
        @input="validate()"
    >
        @csrf
        @method('PATCH')

        {{-- NOME --}}
        <div>
            <x-input-label for="name" value="Nome" />
            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full"
                required
                autofocus
                autocomplete="name"
                x-model="name"
                @focus="touched.name = true"
                @blur="touched.name = true; validate()"
            />

            {{-- Live error (solo dopo focus/blur) --}}
            <p x-show="showError('name')" class="mt-2 text-sm text-red-600" x-text="errors.name"></p>

            {{-- Server error --}}
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        {{-- EMAIL --}}
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input
                id="email"
                name="email"
                type="email"
                class="mt-1 block w-full"
                required
                autocomplete="username"
                x-model="email"
                @focus="touched.email = true"
                @blur="touched.email = true; validate()"
            />

            <p x-show="showError('email')" class="mt-2 text-sm text-red-600" x-text="errors.email"></p>

            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3">
                    <p class="text-sm text-gray-800">
                        La tua email non è verificata.

                        <button
                            form="send-verification"
                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Clicca qui per inviare di nuovo l’email di verifica.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            Ti abbiamo inviato un nuovo link di verifica via email.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- AZIONI --}}
        <div class="flex items-center gap-4">
            <x-primary-button
                x-bind:disabled="!isValid()"
                x-bind:class="!isValid() ? 'opacity-50 cursor-not-allowed' : ''"
            >
                Salva
            </x-primary-button>

            {{-- se vuoi NASCONDERE "Salvato." come avevi fatto nell'altro, elimina questo blocco --}}
            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >
                    Salvato.
                </p>
            @endif
        </div>
    </form>
</section>
