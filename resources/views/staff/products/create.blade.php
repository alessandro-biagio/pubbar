@extends('layouts.staff')

@section('content')
<div class="container mx-auto p-4 space-y-6">
    <h1 class="text-2xl font-semibold">Nuovo prodotto</h1>

    <form
        id="productForm"
        method="POST"
        action="{{ route('staff.products.store') }}"
        enctype="multipart/form-data"
        class="space-y-4 max-w-2xl"
        novalidate
    >
        @csrf

        @include('staff.products.partials.form', ['product'=>null])

        <button type="submit" class="px-3 py-2 bg-black text-white rounded">Crea</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('productForm');
    if (!form) return;

    const nameInput = form.querySelector('input[name="name"]');
    const categorySelect = form.querySelector('select[name="category_id"]');
    const priceInput = form.querySelector('input[name="price"]');
    const imageInput = form.querySelector('input[name="image"]');

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
        const v = nameInput.value.trim();
        if (v.length < 2) {
            setError(nameInput, 'Il nome deve avere almeno 2 caratteri');
            return false;
        }
        removeError(nameInput);
        return true;
    }

    function validateCategory() {
        if (!categorySelect) return true;
        if (!categorySelect.value) {
            setError(categorySelect, 'Seleziona una categoria');
            return false;
        }
        removeError(categorySelect);
        return true;
    }

    function validatePrice() {
        if (!priceInput) return true;

        const raw = (priceInput.value ?? '').toString().trim().replace(',', '.');
        const num = Number(raw);

        if (raw === '' || Number.isNaN(num) || num < 0) {
            setError(priceInput, 'Inserisci un prezzo valido (numero ≥ 0)');
            return false;
        }

        if (!/^\d+(\.\d{1,2})?$/.test(raw)) {
            setError(priceInput, 'Sono consentiti massimo 2 decimali');
            return false;
        }

        removeError(priceInput);
        return true;
    }

    function validateImage() {
        if (!imageInput) return true;

        if (!imageInput.files || imageInput.files.length === 0) {
            removeError(imageInput);
            return true;
        }

        const file = imageInput.files[0];
        const maxSize = 2 * 1024 * 1024; // 2MB

        if (!file.type || !file.type.startsWith('image/')) {
            setError(imageInput, 'Il file deve essere un’immagine');
            return false;
        }

        if (file.size > maxSize) {
            setError(imageInput, 'Dimensione massima 2MB');
            return false;
        }

        removeError(imageInput);
        return true;
    }

    function validateAll() {
        const okName = validateName();
        const okCat = validateCategory();
        const okPrice = validatePrice();
        const okImg = validateImage();

        // focus sul primo errore (senza popup browser)
        const firstBad = [
            [okName, nameInput],
            [okCat, categorySelect],
            [okPrice, priceInput],
            [okImg, imageInput]
        ].find(([ok]) => !ok);

        if (firstBad && firstBad[1]) {
            firstBad[1].focus();
        }

        return okName && okCat && okPrice && okImg;
    }

    // live
    nameInput?.addEventListener('input', validateName);
    nameInput?.addEventListener('blur', validateName);

    categorySelect?.addEventListener('change', validateCategory);

    priceInput?.addEventListener('input', validatePrice);
    priceInput?.addEventListener('blur', validatePrice);

    imageInput?.addEventListener('change', validateImage);

    // submit
    form.addEventListener('submit', (e) => {
        if (!validateAll()) {
            e.preventDefault();
        }
    });
});
</script>
@endsection
