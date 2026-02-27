<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Lista prodotti staff con ricerca testuale e filtro categoria
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $categoryId = $request->integer('category_id');

        // query dinamica: filtri applicati solo se presenti
        $products = Product::with('category')
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($categoryId, fn ($qq) => $qq->where('category_id', $categoryId))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        // per select filtro categoria
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('staff.products.index', compact('products', 'categories', 'q', 'categoryId'));
    }

    /**
     * Form creazione prodotto
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('staff.products.create', compact('categories'));
    }

    /**
     * Salvataggio nuovo prodotto
     */
    public function store(ProductRequest $request)
    {
        // dati già validati dal FormRequest
        $data = $request->validated();

        // normalizzo boolean e prezzo (accetto anche virgola)
        $data['is_available'] = (bool) ($data['is_available'] ?? false);
        $data['price'] = $this->normalizeDecimal($data['price']);

        // upload immagine se presente
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()
            ->route('staff.products.index')
            ->with('success', 'Prodotto creato.');
    }

    /**
     * Form modifica prodotto
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('staff.products.edit', compact('product', 'categories'));
    }

    /**
     * Aggiornamento prodotto esistente
     */
    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->validated();

        // normalizzo boolean e prezzo
        $data['is_available'] = (bool) ($data['is_available'] ?? false);
        $data['price'] = $this->normalizeDecimal($data['price']);

        // se cambio immagine, elimino la vecchia
        if ($request->hasFile('image')) {
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()
            ->route('staff.products.index')
            ->with('success', 'Prodotto aggiornato.');
    }

    /**
     * Eliminazione prodotto (con cleanup immagine)
     */
    public function destroy(Product $product)
    {
        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()
            ->route('staff.products.index')
            ->with('success', 'Prodotto eliminato.');
    }

    /**
     * Normalizza prezzi tipo "3,5" / "3.5" -> "3.50"
     */
    private function normalizeDecimal(string|float|int $value): string
    {
        $s = is_string($value) ? $value : (string) $value;
        $s = str_replace(',', '.', $s);
        $f = (float) $s;

        return number_format($f, 2, '.', '');
    }
}
