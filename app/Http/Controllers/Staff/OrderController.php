<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * Lista ordini staff con filtri (status + ricerca libera)
     */
    public function index(Request $request)
    {
        $q      = trim((string) $request->input('q', ''));
        $status = $request->filled('status') ? $request->input('status') : null;

        // query base + eager load user per tabella
        $ordersQuery = Order::query()->with('user');

        // filtro stato (tab / select)
        if ($status) {
            $ordersQuery->where('status', $status);
        }

        // ricerca "smart": id, nome/telefono, utente/email, date
        if ($q !== '') {
            $ordersQuery->where(function ($w) use ($q) {
                // match rapido per "#123" o "123"
                if (preg_match('/^\#?(\d+)$/', $q, $m)) {
                    $w->orWhere('id', (int) $m[1]);
                }

                // ricerca testuale (spazi -> wildcard)
                $like = '%'.str_replace(' ', '%', $q).'%';
                $w->orWhere('customer_name', 'like', $like)
                  ->orWhere('customer_phone', 'like', $like)
                  ->orWhereHas('user', fn($u) => $u->where('name','like',$like)->orWhere('email','like',$like));

                // se q sembra una data, filtro su created_at/pickup_at
                try {
                    $date = \Carbon\Carbon::createFromFormat('d/m/Y', $q)->toDateString();
                } catch (\Throwable $e) {
                    try {
                        $date = \Carbon\Carbon::parse($q)->toDateString();
                    } catch (\Throwable $e2) {
                        $date = null;
                    }
                }

                if ($date) {
                    $w->orWhereDate('created_at', $date)
                      ->orWhereDate('pickup_at', $date);
                }
            });
        }

        // lista paginata mantenendo i filtri in query string
        $orders = $ordersQuery->latest()->paginate(20)->withQueryString();

        // conteggi per badge/tab (per status)
        $counts = Order::selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c','status')
            ->toArray();

        return view('staff.orders.index', [
            'orders'      => $orders,
            'q'           => $q,
            'status'      => $status,
            'allStatuses' => Order::ALL_STATUSES,
            'counts'      => $counts,
        ]);
    }

    /**
     * Aggiorna lo stato ordine (con guard su transizioni valide)
     */
    public function updateStatus(Request $request, Order $order)
    {
        // status ammessi solo quelli definiti in Order::ALL_STATUSES
        $data = $request->validate([
            'status' => ['required', Rule::in(Order::ALL_STATUSES)],
        ]);

        $to = $data['status'];

        // blocco transizioni "strane" (logica nel model)
        if (!$order->canTransitionTo($to)) {
            return back()->with('error', "Transizione non consentita da '{$order->status}' a '{$to}'.");
        }

        $order->update(['status' => $to]);

        // TODO: notifiche realtime / eventi (OrderStatusUpdated)
        return back()->with('success', "Ordine #{$order->id} aggiornato a '{$to}'.");
    }

    public function modal(Request $request, Order $order)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $order->load([
            'user',
            'items.product',
            'items.variant',
        ]);

        return view('staff.orders._modal', compact('order'));

    }
}