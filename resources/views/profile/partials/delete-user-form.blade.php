<section class="space-y-6"
    x-data="{
        password: '',
        touched: false,
        error: '',

        validate() {
            this.error = (!this.password || this.password.length === 0)
                ? 'Inserisci la password per confermare.'
                : '';
        },

        showError() {
            return this.touched && !!this.error;
        },

        isValid() {
            return !this.error && this.password.length > 0;
        }
    }"
    x-init="validate()"
>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Elimina account
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Una volta eliminato l’account, tutte le risorse e i dati associati verranno cancellati in modo permanente.
        </p>
    </header>

    {{-- ⛔ SUPERUSER: blocco eliminazione --}}
    @if(auth()->user()->is_superuser)
        <div class="rounded-lg border border-yellow-300 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
            Questo account è un <strong>super user</strong> e non può essere eliminato.
        </div>
    @else
        {{-- ✅ UTENTE NORMALE --}}
        <x-danger-button
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        >
            Elimina account
        </x-danger-button>
    @endif

    {{-- MODAL (solo se NON superuser) --}}
    @if(!auth()->user()->is_superuser)
        <x-modal name="confirm-user-deletion"
                 :show="$errors->userDeletion->isNotEmpty()"
                 focusable>

            <form method="POST"
                  action="{{ route('profile.destroy') }}"
                  class="p-6"
                  @input="validate()">
                @csrf
                @method('DELETE')

                <h2 class="text-lg font-medium text-gray-900">
                    Sei sicuro di voler eliminare il tuo account?
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Inserisci la password per confermare l’eliminazione definitiva dell’account.
                </p>

                <div class="mt-6">
                    <x-input-label for="delete_password" value="Password" class="sr-only" />

                    <x-text-input
                        id="delete_password"
                        name="password"
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="Password"
                        x-model="password"
                        @focus="touched = true"
                        @blur="touched = true; validate()"
                        autocomplete="current-password"
                    />

                    {{-- Live error --}}
                    <p x-show="showError()"
                       class="mt-2 text-sm text-red-600"
                       x-text="error"></p>

                    {{-- Server error --}}
                    <x-input-error
                        :messages="$errors->userDeletion->get('password')"
                        class="mt-2"
                    />
                </div>

                <div class="mt-6 flex justify-end">
                    <x-secondary-button type="button" x-on:click="$dispatch('close')">
                        Annulla
                    </x-secondary-button>

                    <x-danger-button
                        class="ms-3"
                        x-bind:disabled="!isValid()"
                        x-bind:class="!isValid() ? 'opacity-50 cursor-not-allowed' : ''"
                    >
                        Elimina account
                    </x-danger-button>
                </div>
            </form>
        </x-modal>
    @endif
</section>
