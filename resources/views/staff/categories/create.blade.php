{{-- resources/views/staff/categories/create.blade.php --}}
@extends('layouts.staff')

@section('content')
<div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white shadow-sm sm:rounded-lg">
        {{-- enctype obbligatorio per upload file --}}
        <form
            id="categoryCreateForm"
            method="POST"
            action="{{ route('staff.categories.store') }}"
            enctype="multipart/form-data"
            class="p-6 space-y-5"
            novalidate
        >
            @csrf

            <h1 class="text-xl font-semibold mb-2">Nuova Categoria</h1>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label class="block text-sm text-gray-700">Nome *</label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    class="mt-1 block w-full rounded border-gray-300"
                >
            </div>

            {{-- NESSUN campo "slug": viene generato automaticamente dal modello --}}

            <div>
                <label class="block text-sm text-gray-700">Descrizione</label>
                <textarea
                    name="description"
                    rows="4"
                    class="mt-1 block w-full rounded border-gray-300"
                >{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-sm text-gray-700">Immagine</label>
                <input
                    type="file"
                    name="image"
                    accept="image/*"
                    class="mt-1 block w-full border-gray-300 rounded"
                >
                {{-- In create non abbiamo $category: mostro solo anteprima client-side se selezionata --}}
                <img id="preview" src="" alt="Anteprima immagine" class="hidden h-16 rounded mt-2">
            </div>

            <div class="flex items-center gap-2">
                <input id="is_active" type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300">
                <label for="is_active" class="text-sm text-gray-700">Attiva</label>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('staff.categories.index') }}" class="px-4 py-2 rounded border text-gray-700">Annulla</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Crea categoria
                </button>
            </div>

            <p class="text-xs text-gray-500">
                Verrà creata di default la capacità oraria (30).
            </p>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('categoryCreateForm');
    if (!form) return;

    const nameInput = form.querySelector('input[name="name"]');
    const imageInput = form.querySelector('input[name="image"]');
    const previewImg = document.getElementById('preview');

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

    function validateName() {
        if (!nameInput) return true;
        const v = (nameInput.value ?? '').trim();
        if (v.length < 2) {
            setError(nameInput, 'Il nome deve avere almeno 2 caratteri');
            return false;
        }
        removeError(nameInput);
        return true;
    }

    function validateImage() {
        if (!imageInput) return true;

        if (!imageInput.files || imageInput.files.length === 0) {
            removeError(imageInput);
            if (previewImg) {
                previewImg.src = '';
                previewImg.classList.add('hidden');
            }
            return true;
        }

        const file = imageInput.files[0];
        const maxSize = 2 * 1024 * 1024; // 2MB

        if (!file.type || !file.type.startsWith('image/')) {
            setError(imageInput, 'Il file deve essere un’immagine');
            if (previewImg) {
                previewImg.src = '';
                previewImg.classList.add('hidden');
            }
            return false;
        }

        if (file.size > maxSize) {
            setError(imageInput, 'Dimensione massima 2MB');
            if (previewImg) {
                previewImg.src = '';
                previewImg.classList.add('hidden');
            }
            return false;
        }

        // ok
        removeError(imageInput);

        // preview
        if (previewImg) {
            const r = new FileReader();
            r.onload = (e) => {
                previewImg.src = e.target.result;
                previewImg.classList.remove('hidden');
            };
            r.readAsDataURL(file);
        }

        return true;
    }

    function focusFirstBad(pairs) {
        const first = pairs.find(([ok]) => !ok);
        if (first && first[1]) first[1].focus();
    }

    // live
    nameInput?.addEventListener('input', validateName);
    nameInput?.addEventListener('blur', validateName);

    imageInput?.addEventListener('change', validateImage);

    // submit
    form.addEventListener('submit', (e) => {
        const ok1 = validateName();
        const ok2 = validateImage();

        if (!(ok1 && ok2)) {
            e.preventDefault();
            focusFirstBad([[ok1, nameInput], [ok2, imageInput]]);
        }
    });
});
</script>
@endsection
