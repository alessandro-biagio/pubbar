@php
    $old = fn($k, $def='') => old($k, $product[$k] ?? $def);
@endphp

@if ($errors->any())
<div class="bg-red-50 text-red-700 p-3 rounded">
    <ul class="list-disc list-inside text-sm">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
</div>
@endif

<div class="grid md:grid-cols-2 gap-4">
    <div>
        <label class="text-sm">Nome *</label>
        <input name="name" value="{{ $old('name') }}" class="w-full border rounded p-2" required>
    </div>

    <div>
        <label class="text-sm">Categoria *</label>
        <select name="category_id" class="w-full border rounded p-2" required>
            <option value="">Seleziona…</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected($old('category_id')==$c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="text-sm">Prezzo base (euro) *</label>
        <input type="number" inputmode="decimal" step="0.01" min="0" class="w-full border rounded p-2"
               name="price" value="{{ $old('price') !== '' ? $old('price') : ($product->price ?? '') }}" required>
        <p class="text-xs text-gray-500 mt-1">
            Se il prodotto ha varianti con prezzi diversi, questo è il prezzo “base” (usato quando non selezioni varianti).
        </p>
    </div>

    <div class="md:col-span-2">
        <label class="text-sm">Descrizione</label>
        <textarea name="description" rows="4" class="w-full border rounded p-2">{{ $old('description') }}</textarea>
    </div>

    <div>
        <label class="text-sm">Immagine {{ $product ? '(lascia vuoto per non cambiare)' : '*' }}</label>
        <input type="file" name="image" accept="image/*" class="w-full border rounded p-2">
    </div>

    <div class="flex items-center gap-2">
        <input type="checkbox" name="is_available" value="1" @checked($old('is_available', $product->is_available ?? true))>
        <span>Disponibile</span>
    </div>

    @if($product?->image_path)
        <div class="md:col-span-2">
            <img src="{{ asset('storage/'.$product->image_path) }}" class="h-24 rounded object-cover">
        </div>
    @endif
</div>
