{{-- resources/views/staff/categories/edit.blade.php --}}
@extends('layouts.staff')

@section('content')
<div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white shadow-sm sm:rounded-lg">
        <form
            id="categoryEditForm"
            method="POST"
            action="{{ route('staff.categories.update', $category) }}"
            enctype="multipart/form-data"
            class="p-6 space-y-5"
            novalidate
        >
            @csrf
            @method('PUT')

            <h1 class="text-xl font-semibold mb-2">Modifica Categoria: {{ $category->name }}</h1>

            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif
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
                    value="{{ old('name', $category->name) }}"
                    required
                    class="mt-1 block w-full rounded border-gray-300"
                >
            </div>

            {{-- Lo slug è gestito automaticamente dal Model --}}

            <div>
                <label class="block text-sm text-gray-700">Descrizione</label>
                <textarea
                    name="description"
                    rows="4"
                    class="mt-1 block w-full rounded border-gray-300"
                >{{ old('description', $category->description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm text-gray-700">Immagine</label>
                <input
                    type="file"
                    name="image"
                    accept="image/*"
                    class="mt-1 block w-full border-gray-300 rounded"
                >

                {{-- Anteprima nuova selezione --}}
                <img id="preview" src="" alt="Anteprima immagine" class="hidden h-16 rounded mt-2">

                @if (!empty($category->image_path))
                    <div class="mt-3 flex items-center gap-3" id="current-image-box">
                        <img
                            src="{{ asset('storage/'.$category->image_path) }}"
                            alt="Immagine attuale"
                            class="h-16 rounded border"
                        >
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="remove_image" value="1" class="rounded border-gray-300">
                            Rimuovi immagine attuale
                        </label>
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <input
                    id="is_active"
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked(old('is_active', $category->is_active))
                    class="rounded border-gray-300"
                >
                <label for="is_active" class="text-sm text-gray-700">Attiva</label>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('staff.categories.index') }}" class="px-4 py-2 rounded border text-gray-700">Annulla</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Salva modifiche
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('categoryEditForm');
    if (!form) return;

    const nameInput = form.querySelector('input[name="name"]');
    const imageInput = form.querySelector('input[name="image"]');
    const previewImg = document.getElementById('preview');

    const removeImageCheckbox = form.querySelector('input[name="remove_image"]');
    const currentImageBox = document.getElementById('current-image-box');

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

        // immagine opzionale: se non seleziono file, ok
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

        removeError(imageInput);

        // preview nuova immagine
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

    // UI: se spunto remove_image, nascondo immagine attuale (solo estetica)
    if (removeImageCheckbox && currentImageBox) {
        const syncRemove = () => {
            currentImageBox.classList.toggle('opacity-50', removeImageCheckbox.checked);
        };
        removeImageCheckbox.addEventListener('change', syncRemove);
        syncRemove();
    }

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
