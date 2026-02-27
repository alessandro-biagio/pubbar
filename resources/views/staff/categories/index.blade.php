{{-- resources/views/staff/categories/index.blade.php --}}
@extends('layouts.staff')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6"
     x-data="{
        showDelete: false,
        deleteName: '',
        deleteAction: '',
        deleteMsg: ''
     }">

    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold">Categorie</h1>
        <a href="{{ route('staff.categories.create') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            + Nuova categoria
        </a>
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-gray-600">
                            <th class="py-2 pr-4">Nome</th>
                            <th class="py-2 pr-4">Slug</th>
                            <th class="py-2 pr-4">Attiva</th>
                            <th class="py-2 pr-4"># Prodotti</th>
                            <th class="py-2 pr-4">Descrizione</th>
                            <th class="py-2 pr-4 text-right">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $cat)
                            @php
                                $prodCount = $cat->products_count ?? $cat->products()->count();
                                $confirmMsg = "ATTENZIONE: stai per eliminare la categoria \"{$cat->name}\".\n\n".
                                              "Conseguenze:\n".
                                              "• Tutti i prodotti associati verranno eliminati (ON DELETE CASCADE).\n".
                                              "• Le varianti di tali prodotti verranno eliminate.\n".
                                              "• Gli override di capacità con slug \"{$cat->slug}\" non avranno più effetto.\n".
                                              "• Eventuali articoli nel carrello legati a questi prodotti non saranno più acquistabili.\n\n".
                                              "Vuoi procedere?";
                            @endphp
                            <tr class="border-b">
                                <td class="py-2 pr-4 font-medium">{{ $cat->name }}</td>
                                <td class="py-2 pr-4 text-gray-600">{{ $cat->slug }}</td>
                                <td class="py-2 pr-4">
                                    @if ($cat->is_active)
                                        <span class="inline-block px-2 py-0.5 text-xs rounded bg-green-100 text-green-800">sì</span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 text-xs rounded bg-gray-200 text-gray-700">no</span>
                                    @endif
                                </td>
                                <td class="py-2 pr-4 text-gray-700">{{ $prodCount }}</td>
                                <td class="py-2 pr-4 text-gray-700">
                                    {{ \Illuminate\Support\Str::limit($cat->description, 80) }}
                                </td>
                                <td class="py-2 pr-0 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('staff.categories.edit', $cat) }}"
                                           class="px-3 py-1 rounded border text-gray-700 hover:bg-gray-50">
                                            Modifica
                                        </a>

                                        {{-- Elimina: apre modal --}}
                                        <button type="button"
                                                class="px-3 py-1 rounded border border-red-300 text-red-700 hover:bg-red-50"
                                                @click="
                                                    showDelete = true;
                                                    deleteName = @js($cat->name);
                                                    deleteAction = @js(route('staff.categories.destroy', $cat));
                                                    deleteMsg = @js($confirmMsg);
                                                ">
                                            Elimina
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="py-6 text-center text-gray-500" colspan="6">
                                    Nessuna categoria presente.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $categories->links() }}
            </div>
        </div>
    </div>

    <div>
        <a href="{{ route('staff.capacity.edit') }}"
           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
            Gestisci capacità per categoria
        </a>
    </div>

    {{-- MODAL DELETE --}}
    <div x-cloak x-show="showDelete"
         class="fixed inset-0 z-50 flex items-center justify-center px-4"
         aria-modal="true" role="dialog"
         @keydown.escape.window="showDelete=false">

        {{-- overlay --}}
        <div class="absolute inset-0 bg-black/50" @click="showDelete=false"></div>

        {{-- box --}}
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
            <h2 class="text-lg font-semibold text-slate-900">
                Conferma eliminazione
            </h2>

            <p class="mt-2 text-sm text-slate-600">
                Stai per eliminare <span class="font-semibold text-slate-900" x-text="deleteName"></span>.
            </p>

            {{-- Messaggio lungo (mantiene i newline) --}}
            <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <pre class="whitespace-pre-wrap text-sm text-slate-700 leading-relaxed" x-text="deleteMsg"></pre>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <button type="button"
                        class="px-4 py-2 rounded-lg border border-slate-300 text-slate-800 hover:bg-slate-100 transition"
                        @click="showDelete=false">
                    Annulla
                </button>

                <form :action="deleteAction" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition">
                        Elimina
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
