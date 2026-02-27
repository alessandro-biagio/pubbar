<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use App\Services\CapacityResolver;

class CheckoutController extends Controller
{
    // configurazione orari/slot per pick-up
    private string $openAt  = '18:00';
    private string $closeAt = '23:00';
    private int $slotMinutes = 30;

    /**
     * Pagina checkout: carrello + totale + slot disponibili + (eventuale) prefill da modifica ordine
     */
    public function show(Request $request)
    {
        // se arrivo da "Modifica ordine" tengo i campi precompilati (nome/telefono/note)
        if (session()->has('cart_source_order_id')) {
            $prefill = session('checkout.prefill', [
                'customer_name'  => null,
                'customer_phone' => null,
                'notes'          => null,
            ]);
        } else {
            // checkout "nuovo": pulisco eventuali residui di sessione
            session()->forget('checkout.prefill');
            $prefill = [
                'customer_name'  => null,
                'customer_phone' => null,
                'notes'          => null,
            ];
        }

        // niente checkout se il carrello è vuoto
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('success','Il carrello è vuoto.');
        }

        // totale calcolato da sessione (no query)
        $total = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $cart));

        // slot pick-up generati per oggi + domani (config sopra)
        $slots = $this->generateSlots();

        return view('checkout', compact('cart','total','slots'));
    }

    /**
     * Conferma checkout: valida slot + controlla capacità per ora/categoria + crea ordine e items
     */
    public function place(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('success','Il carrello è vuoto.');
        }

        // pickup obbligatorio, gli altri campi sono opzionali
        $request->validate([
            'pickup_at'      => ['required', 'date'],
            'customer_name'  => ['nullable','string','max:100'],
            'customer_phone' => ['nullable','string','max:30'],
            'notes'          => ['nullable','string','max:500'],
        ]);

        $pickupAt = Carbon::parse($request->pickup_at, 'Europe/Rome');

        // 1) anti-manomissione: pickup deve essere uno degli slot generati dal server
        $allowedSlots = collect($this->generateSlots())->pluck('value')->all();
        if (! in_array($pickupAt->toDateTimeString(), $allowedSlots, true)) {
            return back()->withErrors(['pickup_at' => 'Orario non valido o non disponibile.'])->withInput();
        }

        // 2) controllo capacità: raggruppo per ORA e per CATEGORIA (slug)
        $hourStart = $pickupAt->copy()->startOfHour();
        $hourEnd   = $hourStart->copy()->addHour();

        // query base: items di ordini nella stessa ora che consumano capacità
        $baseQuery = OrderItem::query()
            ->join('orders','orders.id','=','order_items.order_id')
            ->join('products','products.id','=','order_items.product_id')
            ->join('categories','categories.id','=','products.category_id')
            ->whereBetween('orders.pickup_at', [$hourStart, $hourEnd])
            ->where(function($q){
                // stati "attivi" + pending non scaduti
                $q->whereIn('orders.status', ['paid','confirmed','preparing','ready'])
                  ->orWhere(function($q2){
                      $q2->where('orders.status', 'pending')
                         ->where('orders.expires_at', '>', now());
                  });
            });

        // quantità già prenotate nell’ora, raggruppate per slug categoria
        $reservedBySlug = (clone $baseQuery)
            ->selectRaw("categories.slug as slug, COALESCE(SUM(order_items.qty),0) as qty")
            ->groupBy('categories.slug')
            ->pluck('qty','slug');

        // quantità nel carrello, raggruppate per slug categoria (1 query per mappare product_id -> slug)
        $cartBySlug = [];
        $productIds = array_unique(array_column($cart, 'product_id'));

        if (!empty($productIds)) {
            $slugsByProduct = DB::table('products')
                ->join('categories','categories.id','=','products.category_id')
                ->whereIn('products.id', $productIds)
                ->pluck('categories.slug','products.id');

            foreach ($cart as $item) {
                $slug = $slugsByProduct[$item['product_id']] ?? null;
                if ($slug === null) continue;
                $cartBySlug[$slug] = ($cartBySlug[$slug] ?? 0) + (int) $item['qty'];
            }
        }

        // per ogni slug coinvolto: reserved + incoming non deve superare la capacità definita dal resolver
        /** @var CapacityResolver $resolver */
        $resolver = app(CapacityResolver::class);

        $allSlugs = array_unique(array_merge(array_keys($reservedBySlug->toArray()), array_keys($cartBySlug)));
        $errorsFor = [];

        foreach ($allSlugs as $slug) {
            $cap = $resolver->forHourGroup($pickupAt, $slug); // capacità per ora + categoria
            $already  = (int) ($reservedBySlug[$slug] ?? 0);
            $incoming = (int) ($cartBySlug[$slug] ?? 0);

            if ($already + $incoming > $cap) {
                $errorsFor[] = ['slug' => $slug, 'limit' => $cap];
            }
        }

        // messaggio unico leggibile lato form
        if (!empty($errorsFor)) {
            $parts = array_map(fn($e) => "• Categoria \"{$e['slug']}\" piena (limite: {$e['limit']})", $errorsFor);
            $msg = "Capacità piena per quest'ora:\n".implode("\n", $parts)."\nScegli un altro orario.";
            return back()->withErrors(['pickup_at' => $msg])->withInput();
        }

        // 3) creazione ordine + righe in transazione (o tutto o niente)
        $total = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $cart));

        DB::transaction(function () use ($request, $pickupAt, $cart, $total) {
            // ordine in pending: verrà pagato/confirmato dopo (expires_at = timeout pagamento)
            $order = Order::create([
                'user_id'        => Auth::id(), // se guest: da gestire a parte
                'status'         => 'pending',
                'pickup_at'      => $pickupAt,
                'total'          => $total,
                'customer_name'  => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'notes'          => $request->notes,
                'expires_at'     => now()->addMinutes(1), // in prod: valore più alto (es. 30')
            ]);

            // snapshot delle righe: salvo nome/prezzo in caso di variazioni future a catalogo
            foreach ($cart as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'name'       => $item['name'],
                    'unit_price' => $item['price'],
                    'qty'        => $item['qty'],
                    'line_total' => $item['price'] * $item['qty'],
                ]);
            }

            // una volta creato l'ordine, carrello svuotato e salvo id per redirect al pagamento
            session()->forget('cart');
            session()->put('last_order_id', $order->id);
        });

        return redirect()->route('payment.show', session('last_order_id'));
    }

    /**
     * Genera gli slot pick-up disponibili per oggi e domani (step fisso)
     */
    private function generateSlots(): array
    {
        $slots = [];
        $now = Carbon::now('Europe/Rome');

        foreach ([0,1] as $addDays) {
            $day = $now->copy()->addDays($addDays)->startOfDay();

            $start = $day->copy()->setTimeFromTimeString($this->openAt);
            $end   = $day->copy()->setTimeFromTimeString($this->closeAt);

            // oggi: parto dall'ora attuale arrotondata allo slot successivo (ma mai prima dell'apertura)
            if ($addDays === 0) {
                $rounded = $now->copy()
                    ->addMinutes($this->slotMinutes - ($now->minute % $this->slotMinutes))
                    ->setSecond(0);
                if ($rounded->lessThan($start)) $rounded = $start;
                $start = $rounded;
            }

            for ($t = $start->copy(); $t->lessThanOrEqualTo($end); $t->addMinutes($this->slotMinutes)) {
                // extra safety: niente slot già passati (oggi)
                if ($t->lessThanOrEqualTo($now) && $addDays === 0) continue;

                $slots[] = [
                    'label' => $t->isoFormat('ddd DD/MM HH:mm'),
                    'value' => $t->toDateTimeString(), // confrontata in place() per bloccare manomissioni
                ];
            }
        }

        return $slots;
    }
}
