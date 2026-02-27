<x-app-layout>
    <x-slot name="title">404</x-slot>

    @include('errors._card', [
        'code' => 404,
        'title' => 'Pagina non trovata',
        'message' => 'Il contenuto che stai cercando non esiste oppure è stato spostato.',
        'primaryLabel' => 'Torna alla Home',
        'primaryHref' => route('home'),
        'secondaryLabel' => 'Vai al Menù',
        'secondaryHref' => route('home') . '#categorie', // oppure route('categories.index') se ce l’hai
    ])
</x-app-layout>
