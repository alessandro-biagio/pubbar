@props([
    'code' => 'Errore',
    'title' => 'Qualcosa è andato storto',
    'message' => 'La pagina richiesta non è disponibile.',
    'primaryLabel' => 'Torna alla Home',
    'primaryHref' => route('home'),
    'secondaryLabel' => 'Indietro',
    'secondaryHref' => url()->previous(),
])

<div class="max-w-3xl mx-auto px-6 py-12">
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="px-6 sm:px-8 py-8">
            <div class="flex items-start justify-between gap-6">
                <div>
                    <p class="text-[11px] tracking-[0.30em] uppercase text-slate-500">
                        Errore {{ $code }}
                    </p>

                    <h1 class="mt-3 text-2xl sm:text-3xl font-semibold text-slate-900 tracking-tight">
                        {{ $title }}
                    </h1>

                    <p class="mt-3 text-slate-600 text-sm sm:text-base leading-relaxed">
                        {{ $message }}
                    </p>
                </div>

                <div class="hidden sm:flex items-center justify-center h-12 w-12 rounded-2xl border border-slate-200 bg-slate-50 text-slate-700">
                    ⚠️
                </div>
            </div>

            <div class="mt-8 flex flex-col sm:flex-row gap-3">
                <a href="{{ $primaryHref }}"
                   class="inline-flex items-center justify-center rounded-xl px-5 py-3 bg-slate-900 text-white font-semibold hover:bg-slate-800 transition">
                    {{ $primaryLabel }}
                </a>

                <a href="{{ $secondaryHref }}"
                   class="inline-flex items-center justify-center rounded-xl px-5 py-3 border border-slate-200 bg-white text-slate-900 font-semibold hover:bg-slate-50 transition">
                    {{ $secondaryLabel }}
                </a>
            </div>

            <div class="mt-6 text-xs text-slate-500">
                Se pensi sia un errore, riprova più tardi o contatta lo staff.
            </div>
        </div>
    </div>
</div>
