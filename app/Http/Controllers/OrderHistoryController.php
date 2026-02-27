<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderHistoryController extends Controller
{
    /**
     * Storico ordini dell'utente loggato (con items + prodotto/variante)
     */
    public function index(Request $request)
    {
        // eager load per evitare N+1 in pagina "i miei ordini"
        $orders = Order::with(['items.product','items.variant'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('orders.my', compact('orders'));
    }

    /**
     * "Modifica" ordine: rimette le righe nel carrello, precompila checkout e cancella l'ordine
     */
    public function revertToCart(Request $request, Order $order)
    {
        // allowExpired=true: posso ripristinare anche se è scaduto nel frattempo
        $ok = $this->revertToCartCore($request, $order, true);

        if (!$ok) {
            return back()->with('error', 'Questo ordine non è più ripristinabile nel carrello.');
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Articoli ripristinati nel carrello. Ordine annullato.');
    }

    /**
     * Ripristino "silenzioso" via JS (signed route, POST senza CSRF) -> 204
     */
    public function revertToCartBeacon(Request $request, Order $order)
    {
        $this->revertToCartCore($request, $order, true);
        return response()->noContent();
    }

    /**
     * Core: controlli permessi/stato + rebuild carrello + prefill checkout + elimina ordine
     */
    private function revertToCartCore(Request $request, Order $order, bool $allowExpired = false): bool
    {
        // guard: solo proprietario, solo pending/created, solo non pagato
        $isOwner   = (int) $order->user_id === (int) $request->user()->id;
        $isPending = in_array(strtolower((string) $order->status), ['pending','created'], true);

        // scadenza: di default deve essere futura (ma posso bypassare con allowExpired)
        $notExpired = is_null($order->expires_at) || optional($order->expires_at)->isFuture();

        // non deve risultare pagato (lista volutamente ampia per provider diversi)
        $paidStatuses = ['paid','succeeded','completed'];
        $isUnpaid     = !in_array(strtolower((string) $order->payment_status), $paidStatuses, true);

        if (!($isOwner && $isPending && $isUnpaid && ($allowExpired || $notExpired))) {
            return false;
        }

        // mi porto dietro relazioni per ricostruire name/variant in modo safe
        $order->load(['items.product','items.variant']);

        // riparto pulito: carrello nuovo + prefill nuovo
        session()->forget('cart');
        session()->forget('checkout.prefill');

        $cart = [];

        foreach ($order->items as $it) {
            // prendo id da relazione se c'è, altrimenti fallback su colonne
            $productId = optional($it->product)->id ?? $it->product_id ?? null;
            $variantId = optional($it->variant)->id ?? $it->variant_id ?? null;
            if (!$productId) continue;

            // stessa key del carrello: productId-variantId
            $key   = $productId . '-' . ($variantId ?: '0');
            $name  = optional($it->product)->name ?? $it->product_name ?? 'Prodotto';
            $vname = optional($it->variant)->name ?? $it->variant_name ?? null;

            // uso i prezzi/snapshot salvati nell'ordine (non il catalogo attuale)
            $unit  = (float) ($it->unit_price ?? $it->price ?? 0);
            $qty   = (int)   ($it->quantity ?? $it->qty ?? 1);

            // skip righe "sporche"
            if ($unit <= 0 || $qty <= 0) continue;

            $cart[$key] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'name'       => $vname ? ($name . ' - ' . $vname) : $name,
                'price'      => $unit,
                'qty'        => $qty,
            ];
        }

        session()->put('cart', $cart);

        // prefill checkout: riparto con i dati dell'ordine precedente
        session()->put('checkout.prefill', [
            'customer_name'  => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'notes'          => $order->notes,
        ]);

        // marker: checkout sa che arrivo da "modifica ordine"
        session()->put('cart_source_order_id', $order->id);

        // elimino ordine e righe in transazione (no stati "ibridi")
        DB::transaction(function () use ($order) {
            // se hai soft delete, qui cerco di fare forceDelete quando disponibile
            if (method_exists($order->items(), 'forceDelete')) {
                $order->items()->forceDelete();
            } else {
                $order->items()->delete();
            }

            if (method_exists($order, 'forceDelete')) {
                $order->forceDelete();
            } else {
                $order->delete();
            }
        });

        return true;
    }
}
