<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class CatalogController extends Controller
{
    /**
     * Homepage: lista categorie attive (card/griglia)
     */
    public function home()
    {
        // prendo solo i campi che mi servono in view
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id','name','slug','description','image_path']);

        return view('home', compact('categories'));
    }

    /**
     * Pagina categoria (slug): prodotti disponibili + varianti disponibili
     */
    public function category(string $slug)
    {
        // slug valido + categoria attiva
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // prodotti della categoria, filtrati e con eager load delle varianti disponibili
        $products = $category->products()
            ->where('is_available', true)
            ->with(['variants' => function ($q) {
                // in lista mostro solo varianti ordinandole per prezzo (min->max)
                $q->where('is_available', true)->orderBy('price');
            }])
            ->orderBy('name')
            ->get();

        return view('category', compact('category', 'products'));
    }

    /**
     * Pagina prodotto (slug): dettaglio prodotto + varianti + categoria
     */
    public function product(string $slug)
    {
        // prodotto disponibile, con varianti disponibili e relazione category già pronta per breadcrumb/link
        $product = Product::where('slug', $slug)
            ->where('is_available', true)
            ->with([
                'variants' => function ($q) {
                    $q->where('is_available', true)->orderBy('price');
                },
                'category'
            ])
            ->firstOrFail();

        return view('product', compact('product'));
    }
}
