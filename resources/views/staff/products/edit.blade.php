@extends('layouts.staff')

@section('content')
<div class="container mx-auto p-4 space-y-6">
    <h1 class="text-2xl font-semibold">Modifica: {{ $product->name }}</h1>


    {{-- FORM PRODOTTO --}}
    <form
        id="productEditForm"
        method="POST"
        action="{{ route('staff.products.update',$product) }}"
        enctype="multipart/form-data"
        class="space-y-4 max-w-2xl"
        novalidate
    >
        @csrf @method('PUT')
        @include('staff.products.partials.form', ['product'=>$product])

        <div class="flex items-center gap-2">
            <button type="submit" class="px-3 py-2 bg-black text-white rounded">Salva</button>
            <a href="{{ route('staff.products.index') }}" class="px-3 py-2 border rounded">Annulla</a>
        </div>
    </form>

    {{-- VARIANTI --}}
    <div class="p-4 rounded border space-y-4">
        <h2 class="text-lg font-semibold">Varianti</h2>

        {{-- Aggiungi variante --}}
        <form
            id="variantCreateForm"
            method="POST"
            action="{{ route('staff.products.variants.store', $product) }}"
            class="grid md:grid-cols-6 gap-3 items-end"
            novalidate
        >
            @csrf
            <div class="md:col-span-2">
                <label class="text-sm">Nome variante *</label>
                <input name="name" class="w-full border rounded p-2" placeholder="Es. Bicchiere 0,2 / Piadina" required>
            </div>
            <div>
                <label class="text-sm">Volume (ml)</label>
                <input type="number" min="0" name="volume_ml" class="w-full border rounded p-2" placeholder="Es. 200">
            </div>
            <div>
                <label class="text-sm">Prezzo (euro) *</label>
                <input type="number" step="0.01" min="0" name="price" class="w-full border rounded p-2" required>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_available" value="1" checked>
                <span class="text-sm">Disponibile</span>
            </div>
            <div class="md:col-span-6">
                <button type="submit" class="px-3 py-2 bg-black text-white rounded">Aggiungi variante</button>
            </div>
        </form>

        {{-- Lista varianti --}}
        <div class="overflow-x-auto">
            <table class="min-w-full border rounded">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2 text-left">Nome</th>
                        <th class="p-2 text-left">Volume (ml)</th>
                        <th class="p-2 text-left">Prezzo</th>
                        <th class="p-2 text-left">Disponibile</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($product->variants as $v)
                    <tr class="border-t">
                        <td class="p-2">
                            <form
                                method="POST"
                                action="{{ route('staff.products.variants.update', [$product, $v]) }}"
                                class="variantUpdateForm flex gap-2 items-center"
                                novalidate
                            >
                                @csrf @method('PUT')
                                <input name="name" class="border rounded p-2" value="{{ $v->name }}" required>
                        </td>
                        <td class="p-2">
                                <input type="number" min="0" name="volume_ml" class="border rounded p-2 w-28" value="{{ $v->volume_ml }}">
                        </td>
                        <td class="p-2">
                                <input type="number" step="0.01" min="0" name="price" class="border rounded p-2 w-32" value="{{ number_format($v->price,2,'.','') }}" required>
                        </td>
                        <td class="p-2">
                                <input type="checkbox" name="is_available" value="1" @checked($v->is_available)>
                        </td>
                        <td class="p-2 text-right space-x-2">
                                <button type="submit" class="px-2 py-1 border rounded">Salva</button>
                            </form>

                            <form method="POST" action="{{ route('staff.products.variants.destroy', [$product, $v]) }}" class="inline" onsubmit="return confirm('Eliminare la variante {{ $v->name }}?');">
                                @csrf @method('DELETE')
                                <button class="px-2 py-1 border rounded text-red-600">Elimina</button>
                            </form>

                            <form method="POST" action="{{ route('staff.products.variants.toggle', [$product, $v]) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="px-2 py-1 border rounded text-xs {{ $v->is_available ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-800' }}">
                                    {{ $v->is_available ? 'On' : 'Off' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-4 text-gray-500">Nessuna variante.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    function removeError(el) {
        if (!el) return;
        el.classList.remove('border-red-500');
        const err = el.parentElement?.querySelector('.js-error');
        if (err) err.remove();
    }

    function setError(el, msg) {
        if (!el) return;
        removeError(el);
        el.classList.add('border-red-500');
        const p = document.createElement('p');
        p.className = 'js-error text-xs text-red-600 mt-1';
        p.textContent = msg;
        el.parentElement.appendChild(p);
    }

    function validateTextMin(el, min, msg) {
        if (!el) return true;
        const v = (el.value ?? '').trim();
        if (v.length < min) {
            setError(el, msg);
            return false;
        }
        removeError(el);
        return true;
    }

    function validateSelectRequired(el, msg) {
        if (!el) return true;
        if (!el.value) {
            setError(el, msg);
            return false;
        }
        removeError(el);
        return true;
    }

    function validatePrice(el) {
        if (!el) return true;
        const raw = (el.value ?? '').toString().trim().replace(',', '.');
        const num = Number(raw);

        if (raw === '' || Number.isNaN(num) || num < 0) {
            setError(el, 'Inserisci un prezzo valido (numero ≥ 0)');
            return false;
        }

        if (!/^\d+(\.\d{1,2})?$/.test(raw)) {
            setError(el, 'Sono consentiti massimo 2 decimali');
            return false;
        }

        removeError(el);
        return true;
    }

    function validateImage(el) {
        if (!el) return true;
        if (!el.files || el.files.length === 0) {
            removeError(el);
            return true;
        }

        const file = el.files[0];
        const maxSize = 2 * 1024 * 1024; // 2MB

        if (!file.type || !file.type.startsWith('image/')) {
            setError(el, 'Il file deve essere un’immagine');
            return false;
        }

        if (file.size > maxSize) {
            setError(el, 'Dimensione massima 2MB');
            return false;
        }

        removeError(el);
        return true;
    }

    function focusFirstBad(pairs) {
        const first = pairs.find(([ok]) => !ok);
        if (first && first[1]) first[1].focus();
    }

    // =========================
    // 1) FORM PRODOTTO (EDIT)
    // =========================
    const productForm = document.getElementById('productEditForm');
    if (productForm) {
        const name = productForm.querySelector('input[name="name"]');
        const category = productForm.querySelector('select[name="category_id"]');
        const price = productForm.querySelector('input[name="price"]');
        const image = productForm.querySelector('input[name="image"]');

        name?.addEventListener('input', () => validateTextMin(name, 2, 'Il nome deve avere almeno 2 caratteri'));
        name?.addEventListener('blur',  () => validateTextMin(name, 2, 'Il nome deve avere almeno 2 caratteri'));

        category?.addEventListener('change', () => validateSelectRequired(category, 'Seleziona una categoria'));

        price?.addEventListener('input', () => validatePrice(price));
        price?.addEventListener('blur',  () => validatePrice(price));

        image?.addEventListener('change', () => validateImage(image));

        productForm.addEventListener('submit', (e) => {
            const okName = validateTextMin(name, 2, 'Il nome deve avere almeno 2 caratteri');
            const okCat  = validateSelectRequired(category, 'Seleziona una categoria');
            const okPrice = validatePrice(price);
            const okImg  = validateImage(image);

            if (!(okName && okCat && okPrice && okImg)) {
                e.preventDefault();
                focusFirstBad([[okName, name],[okCat, category],[okPrice, price],[okImg, image]]);
            }
        });
    }

    // =========================
    // 2) FORM CREA VARIANTE
    // =========================
    const variantCreate = document.getElementById('variantCreateForm');
    if (variantCreate) {
        const vName = variantCreate.querySelector('input[name="name"]');
        const vPrice = variantCreate.querySelector('input[name="price"]');

        vName?.addEventListener('input', () => validateTextMin(vName, 2, 'Il nome variante deve avere almeno 2 caratteri'));
        vName?.addEventListener('blur',  () => validateTextMin(vName, 2, 'Il nome variante deve avere almeno 2 caratteri'));

        vPrice?.addEventListener('input', () => validatePrice(vPrice));
        vPrice?.addEventListener('blur',  () => validatePrice(vPrice));

        variantCreate.addEventListener('submit', (e) => {
            const ok1 = validateTextMin(vName, 2, 'Il nome variante deve avere almeno 2 caratteri');
            const ok2 = validatePrice(vPrice);

            if (!(ok1 && ok2)) {
                e.preventDefault();
                focusFirstBad([[ok1, vName],[ok2, vPrice]]);
            }
        });
    }

    // =========================
    // 3) FORM UPDATE VARIANTI
    // =========================
    document.querySelectorAll('form.variantUpdateForm').forEach((f) => {
        const vName = f.querySelector('input[name="name"]');
        const vPrice = f.querySelector('input[name="price"]');

        vName?.addEventListener('input', () => validateTextMin(vName, 2, 'Il nome variante deve avere almeno 2 caratteri'));
        vName?.addEventListener('blur',  () => validateTextMin(vName, 2, 'Il nome variante deve avere almeno 2 caratteri'));

        vPrice?.addEventListener('input', () => validatePrice(vPrice));
        vPrice?.addEventListener('blur',  () => validatePrice(vPrice));

        f.addEventListener('submit', (e) => {
            const ok1 = validateTextMin(vName, 2, 'Il nome variante deve avere almeno 2 caratteri');
            const ok2 = validatePrice(vPrice);

            if (!(ok1 && ok2)) {
                e.preventDefault();
                focusFirstBad([[ok1, vName],[ok2, vPrice]]);
            }
        });
    });

});
</script>
@endsection
