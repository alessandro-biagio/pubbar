<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;

class CartController extends Controller
{
    /**
     * Mostra il carrello salvato in sessione + totale (price * qty)
     */
    public function index(Request $request)
    {
        // cart = array indicizzato per key "productId-variantId"
        $cart = session()->get('cart', []);

        return view('cart', [
            'cart'  => $cart,
            'total' => $this->cartTotal($cart),
        ]);
    }

    /**
     * Aggiunge un prodotto (con eventuale variante) al carrello in sessione
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'qty'        => 'required|integer|min:1',
        ]);

        $cart = session()->get('cart', []);

        // se il carrello era vuoto, invalido eventuale checkout precedente
        if (empty($cart)) {
            session()->forget(['checkout.prefill', 'cart_source_order_id']);
        }

        // recupero i dati reali dal DB (no trust su request)
        $product = Product::findOrFail($request->product_id);
        $variant = $request->variant_id ? ProductVariant::findOrFail($request->variant_id) : null;

        // key unica per distinguere stesso prodotto con varianti diverse
        $key = $product->id . '-' . ($variant ? $variant->id : '0');

        if (isset($cart[$key])) {
            $cart[$key]['qty'] += (int) $request->qty;
        } else {
            $cart[$key] = [
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'name'       => $product->name . ($variant ? ' - ' . $variant->name : ''),
                'price'      => $variant?->price ?? $product->price,
                'qty'        => (int) $request->qty,
            ];
        }

        session()->put('cart', $cart);

        return redirect()->route('home')->with('success', 'Prodotto aggiunto al carrello!');
    }

    /**
     * Rimuove una riga dal carrello (via key) e ritorna JSON per aggiornare UI via fetch
     */
    public function remove(Request $request)
    {
        $request->validate([
            'key' => 'required',
        ]);

        $cart = session()->get('cart', []);
        $removed = false;

        if (isset($cart[$request->key])) {
            unset($cart[$request->key]);
            session()->put('cart', $cart);
            $removed = true;
        }

        return response()->json([
            'success'   => $removed,
            'cartTotal' => $this->cartTotal($cart),
            'cartCount' => $this->cartCount($cart),
        ]);
    }

    /**
     * Aggiorna la quantità di una riga e ritorna totali riga + carrello (AJAX)
     */
    public function update(Request $request)
    {
        $request->validate([
            'key' => 'required',
            'qty' => 'required|integer|min:1',
        ]);

        $cart = session()->get('cart', []);
        $key  = $request->key;

        if (!isset($cart[$key])) {
            return response()->json([
                'success'   => false,
                'message'   => 'Riga non trovata nel carrello.',
                'cartTotal' => $this->cartTotal($cart),
                'cartCount' => $this->cartCount($cart),
            ], 404);
        }

        $cart[$key]['qty'] = (int) $request->qty;
        session()->put('cart', $cart);

        return response()->json([
            'success'   => true,
            'itemTotal' => $cart[$key]['price'] * $cart[$key]['qty'],
            'cartTotal' => $this->cartTotal($cart),
            'cartCount' => $this->cartCount($cart),
        ]);
    }

    /**
     * Totale carrello: somma price*qty
     */
    private function cartTotal(array $cart): float
    {
        return (float) array_sum(array_map(
            fn ($item) => (float) $item['price'] * (int) $item['qty'],
            $cart
        ));
    }

    /**
     * Numero badge: somma qty
     */
    private function cartCount(array $cart): int
    {
        return (int) array_sum(array_map(
            fn ($item) => (int) ($item['qty'] ?? 0),
            $cart
        ));
    }
}
