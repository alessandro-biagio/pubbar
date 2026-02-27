<x-app-layout>
    <x-slot name="title">403</x-slot>

    @include('errors._card', [
        'code' => 403,
        'title' => 'Pagina non autorizzata',
        'message' => 'Nessuna autorizzazione per accedere alla pagina.',
        'primaryLabel' => 'Torna alla Home',
        'primaryHref' => route('home'),
        'secondaryLabel' => 'Vai al Menù',
        'secondaryHref' => route('home') . '#categorie', // oppure route('categories.index') se ce l’hai
    ])
</x-app-layout>
