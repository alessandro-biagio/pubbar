{{-- resources/views/staff/users/index.blade.php --}}
@extends('layouts.staff')
@php($title = 'Gestione Utenti')

@section('content')
<div class="px-4 sm:px-0 space-y-4">

    {{-- ... filtro e flash ... --}}

    <div class="overflow-x-auto bg-white border border-slate-200 rounded-2xl">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left">
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">Nome</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Staff</th>
                    <th class="px-4 py-3">Superuser</th>
                    @if(auth()->user()?->is_superuser)
                        <th class="px-4 py-3 text-right">Azioni</th>
                    @endif
                </tr>
            </thead>

            <tbody>
            @forelse($users as $u)
                @php($isStaff = $u->is_superuser || $u->is_staff)
                <tr class="border-t">
                    <td class="px-4 py-3 font-medium">
                        <a href="{{ route('staff.users.orders', $u) }}" class="underline">#{{ $u->id }}</a>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('staff.users.orders', $u) }}" class="underline">{{ $u->name ?? '—' }}</a>
                    </td>
                    <td class="px-4 py-3">{{ $u->email }}</td>

                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded text-xs {{ $isStaff ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-800' }}">
                            {{ $isStaff ? 'Sì' : 'No' }}
                        </span>
                    </td>

                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded text-xs {{ $u->is_superuser ? 'bg-purple-100 text-purple-700' : 'bg-gray-200 text-gray-800' }}">
                            {{ $u->is_superuser ? 'Sì' : 'No' }}
                        </span>
                    </td>

                    @if(auth()->user()?->is_superuser)
                        <td class="px-4 py-3 text-right space-x-2">

                            {{-- Toggle STAFF: NON submit --}}
                            @if(!$u->is_superuser)
                                <form method="POST"
                                      action="{{ route('staff.users.toggleStaff', $u) }}"
                                      class="inline js-confirm-form">
                                    @csrf
                                    @method('PATCH')

                                    <button type="button"
                                            class="px-2 py-1 border rounded text-xs"
                                            data-confirm-msg="{{ $u->is_staff
                                                ? "Confermi di rimuovere {$u->name} dallo staff?"
                                                : "Confermi di abilitare {$u->name} nello staff?"
                                            }}">
                                        {{ $u->is_staff ? 'Rimuovi staff' : 'Rendi staff' }}
                                    </button>
                                </form>
                            @endif

                            {{-- Toggle SUPERUSER: NON submit --}}
                            <form method="POST"
                                  action="{{ route('staff.users.toggleSuperuser', $u) }}"
                                  class="inline js-confirm-form">
                                @csrf
                                @method('PATCH')

                                <button type="button"
                                        class="px-2 py-1 border rounded text-xs"
                                        data-confirm-msg="Confermi il cambio superuser per {{ $u->name }}?">
                                    {{ $u->is_superuser ? 'Rimuovi superuser' : 'Rendi superuser' }}
                                </button>
                            </form>

                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ auth()->user()?->is_superuser ? 6 : 5 }}"
                        class="px-4 py-8 text-center text-slate-500">
                        Nessun utente trovato.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $users->links() }}</div>

    {{-- MODAL (vanilla JS, stile "Elimina") --}}
    <div id="confirmModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-black/50" data-close-modal></div>

        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <h2 class="text-lg font-semibold text-slate-900">Conferma operazione</h2>
            <p id="confirmModalMsg" class="mt-2 text-sm text-slate-600"></p>

            <div class="mt-6 flex items-center justify-end gap-3">
                <button type="button"
                        class="px-4 py-2 rounded-lg border border-slate-300 text-slate-800 hover:bg-slate-100 transition"
                        data-close-modal>
                    Annulla
                </button>

                <button type="button"
                        class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 transition"
                        id="confirmModalOk">
                    Conferma
                </button>
            </div>
        </div>
    </div>

</div>

<script>
(function () {
    const modal = document.getElementById('confirmModal');
    const msgEl = document.getElementById('confirmModalMsg');
    const okBtn = document.getElementById('confirmModalOk');

    let pendingForm = null;

    function openModal(message, form) {
        pendingForm = form;
        msgEl.textContent = message || 'Confermi?';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        pendingForm = null;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        msgEl.textContent = '';
    }

    // chiusura overlay + bottone annulla
    modal.querySelectorAll('[data-close-modal]').forEach(el => {
        el.addEventListener('click', closeModal);
    });

    // ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

    // click sui bottoni azione -> apre popup
    document.querySelectorAll('.js-confirm-form button[type="button"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const form = btn.closest('form');
            const message = btn.getAttribute('data-confirm-msg') || 'Confermi?';
            openModal(message, form);
        });
    });

    // conferma -> submit “silenzioso” (non triggera submit event, quindi NO alert)
    okBtn.addEventListener('click', function () {
        if (!pendingForm) return;

        // blocco doppio click: disabilito il bottone che hai cliccato nella tabella
        const tableBtn = pendingForm.querySelector('button[type="button"]');
        if (tableBtn) {
            tableBtn.disabled = true;
            tableBtn.textContent = 'Attendere...';
        }

        // IMPORTANT: form.submit() non scatena event listeners / confirm globali
        pendingForm.submit();
        closeModal();
    });
})();
</script>
@endsection
