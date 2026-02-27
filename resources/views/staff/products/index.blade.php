@extends('layouts.staff')

@section('content')
<div class="container mx-auto p-4 space-y-6"
     x-data="{
        showDelete: false,
        deleteName: '',
        deleteAction: ''
     }">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Prodotti</h1>
        <a href="{{ route('staff.products.create') }}" class="px-3 py-2 rounded bg-black text-white">Nuovo</a>
    </div>

    <form class="flex flex-wrap gap-2 items-end border p-3 rounded">
        <div>
            <label class="text-sm">Cerca</label>
            <input type="text" name="q" value="{{ $q }}" class="border rounded p-2" placeholder="Nome o descrizione">
        </div>
        <div>
            <label class="text-sm">Categoria</label>
            <select name="category_id" class="border rounded p-2">
                <option value="">Tutte</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected($categoryId==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <button class="px-3 py-2 bg-gray-800 text-white rounded">Filtra</button>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full border rounded">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-2 text-left">Immagine</th>
                    <th class="p-2 text-left">Nome</th>
                    <th class="p-2 text-left">Categoria</th>
                    <th class="p-2 text-left">Prezzo</th>
                    <th class="p-2 text-left">Disponibile</th>
                    <th class="p-2"></th>
                </tr>
            </thead>
            <tbody>
            @forelse($products as $p)
                <tr class="border-t">
                    <td class="p-2">
                        @if($p->image_path)
                            <img src="{{ asset('storage/'.$p->image_path) }}" alt="" class="h-12 w-12 object-cover rounded">
                        @endif
                    </td>
                    <td class="p-2 font-medium">{{ $p->name }}</td>
                    <td class="p-2">{{ $p->category?->name ?? '—' }}</td>
                    <td class="p-2">€ {{ number_format($p->price, 2, ',', '.') }}</td>
                    <td class="p-2">
                        <span class="px-2 py-1 rounded text-xs {{ $p->is_available ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-800' }}">
                            {{ $p->is_available ? 'Sì' : 'No' }}
                        </span>
                    </td>

                    <td class="p-2 text-right space-x-2">
                        <a href="{{ route('staff.products.edit',$p) }}" class="px-2 py-1 border rounded">Modifica</a>

                        {{-- Bottone che apre il popup --}}
                        <button type="button"
                                class="px-2 py-1 border rounded text-red-600"
                                @click="
                                    showDelete = true;
                                    deleteName = @js($p->name);
                                    deleteAction = @js(route('staff.products.destroy', $p));
                                ">
                            Elimina
                        </button>
                    </td>
                </tr>
            @empty
                <tr><td class="p-4 text-gray-500" colspan="6">Nessun prodotto.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $products->links() }}</div>

    {{-- MODAL DELETE --}}
    <div x-cloak x-show="showDelete"
         class="fixed inset-0 z-50 flex items-center justify-center px-4"
         aria-modal="true" role="dialog"
         @keydown.escape.window="showDelete=false">

        {{-- overlay --}}
        <div class="absolute inset-0 bg-black/50" @click="showDelete=false"></div>

        {{-- box --}}
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <h2 class="text-lg font-semibold text-slate-900">Conferma eliminazione</h2>
            <p class="mt-2 text-sm text-slate-600">
                Vuoi eliminare <span class="font-semibold text-slate-900" x-text="deleteName"></span>?
                Questa azione non può essere annullata.
            </p>

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
