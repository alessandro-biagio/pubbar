<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    /**
     * Crea una variante per un prodotto (es. taglia/litri/prezzo)
     */
    public function store(Request $request, Product $product)
    {
        // validazione base (price accetta 0-2 decimali)
        $data = $request->validate([
            'name'         => ['required','string','max:100'],
            'volume_ml'    => ['nullable','integer','min:0'],
            'price'        => ['required','decimal:0,2','min:0'],
            'is_available' => ['sometimes','boolean'],
        ]);

        // checkbox: se non arriva -> false
        $data['is_available'] = (bool) ($data['is_available'] ?? false);

        // accetto "3,50" e salvo sempre "3.50"
        $data['price'] = $this->normalizeDecimal($data['price']);

        $product->variants()->create($data);

        return back()->with('success', 'Variante aggiunta.');
    }

    /**
     * Aggiorna una variante (guard: deve appartenere al product in route)
     */
    public function update(Request $request, Product $product, ProductVariant $variant)
    {
        // evita update su variante di un altro prodotto (route tampering)
        abort_unless($variant->product_id === $product->id, 404);

        $data = $request->validate([
            'name'         => ['required','string','max:100'],
            'volume_ml'    => ['nullable','integer','min:0'],
            'price'        => ['required','decimal:0,2','min:0'],
            'is_available' => ['sometimes','boolean'],
        ]);

        $data['is_available'] = (bool) ($data['is_available'] ?? false);
        $data['price']        = $this->normalizeDecimal($data['price']);

        $variant->update($data);

        return back()->with('success', 'Variante aggiornata.');
    }

    /**
     * Elimina variante (guard: product<->variant coerenti)
     */
    public function destroy(Product $product, ProductVariant $variant)
    {
        abort_unless($variant->product_id === $product->id, 404);

        $variant->delete();

        return back()->with('success', 'Variante eliminata.');
    }

    /**
     * Toggle disponibilità (quick action in backoffice)
     */
    public function toggle(Product $product, ProductVariant $variant)
    {
        abort_unless($variant->product_id === $product->id, 404);

        $variant->update(['is_available' => !$variant->is_available]);

        return back()->with('success', 'Disponibilità variante aggiornata.');
    }

    /**
     * Normalizza prezzi tipo "3,5" / "3.5" -> "3.50"
     */
    private function normalizeDecimal(string|float|int $value): string
    {
        $s = is_string($value) ? $value : (string) $value;
        $s = str_replace(',', '.', $s);

        return number_format((float) $s, 2, '.', '');
    }
}
