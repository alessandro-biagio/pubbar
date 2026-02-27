<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use Illuminate\Support\Facades\URL;

class PaymentPaypalController extends Controller
{
    /**
     * Base URL PayPal (sandbox/live) in base a config
     */
    private function apiBase(): string
    {
        return config('services.paypal.mode', 'sandbox') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * OAuth client_credentials: token da usare per chiamate API PayPal
     */
    private function getAccessToken(): string
    {
        $resp = Http::asForm()
            ->withBasicAuth(config('services.paypal.client_id'), config('services.paypal.secret'))
            ->post($this->apiBase().'/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        // se PayPal non risponde ok, per ora stoppo tutto (errore di config o API)
        if (!$resp->successful()) {
            abort(500, 'PayPal auth failed');
        }

        return (string) $resp->json('access_token');
    }

    /**
     * Guard semplice: l'ordine deve appartenere all'utente loggato
     */
    private function authorizeOwner(Order $order): void
    {
        abort_unless(Auth::id() === (int) $order->user_id, 403);
    }

    /**
     * Un ordine è pagabile solo se è ancora "attivo" e non pagato
     */
    private function isPayable(Order $order): bool
    {
        $status    = strtolower((string) $order->status);
        $payStatus = strtolower((string) $order->payment_status);

        $pendingOrCreated = in_array($status, ['pending', 'created'], true);
        $notExpired       = is_null($order->expires_at) || $order->expires_at->isFuture();
        $unpaid           = !in_array($payStatus, ['paid','succeeded','completed'], true);

        return $pendingOrCreated && $notExpired && $unpaid;
    }

    /**
     * Pagina pagamento: recap ordine + render PayPal button + beacon "auto revert" (signed)
     */
    public function show(Order $order)
    {
        $this->authorizeOwner($order);

        // se è già pagato, mostro pagina successo (niente pulsanti)
        if ($order->payment_status === 'paid') {
            $order->load(['items.product','items.variant']);
            return view('payment_success', compact('order'));
        }

        // recap in pagina di pagamento
        $order->load(['items.product','items.variant']);

        return view('payment_paypal', [
            'order'           => $order,
            'paypalClientId'  => config('services.paypal.client_id'),
            'currency'        => config('services.paypal.currency', 'EUR'),

            // endpoint signed per "auto-ripristino" se l'utente molla la pagina (no CSRF)
            'revertBeaconUrl' => URL::temporarySignedRoute(
                'orders.revert_beacon',
                now()->addMinutes(30),
                ['order' => $order->id]
            ),
        ]);
    }

    /**
     * Crea l'ordine PayPal (AJAX): ritorna paypalOrderId da usare poi in capture
     */
    public function createOrder(Request $request, Order $order)
    {
        $this->authorizeOwner($order);

        // blocco server: ordine scaduto/non pagabile => 409 (gestito lato JS)
        if (!$this->isPayable($order)) {
            return response()->json(['error' => 'Ordine scaduto o non più pagabile.'], 409);
        }

        // se già pagato (edge case), evito doppie chiamate
        if (strtolower((string) $order->payment_status) === 'paid') {
            return response()->json(['alreadyPaid' => true], 200);
        }

        $accessToken = $this->getAccessToken();

        // amount formattato con 2 decimali (PayPal lo vuole così)
        $amount   = number_format((float) $order->total, 2, '.', '');
        $currency = config('services.paypal.currency', 'EUR');

        // reference_id = id ordine interno -> lo recupero poi in capture
        $body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => (string) $order->id,
                'amount' => [
                    'currency_code' => $currency,
                    'value' => $amount,
                ],
            ]],
            'application_context' => [
                'brand_name'          => config('app.name', 'PubBar'),
                'shipping_preference' => 'NO_SHIPPING',
                'user_action'         => 'PAY_NOW',
            ],
        ];

        $resp = Http::withToken($accessToken)
            ->post($this->apiBase().'/v2/checkout/orders', $body);

        if (!$resp->successful()) {
            Log::error('PayPal create failed', ['status' => $resp->status(), 'body' => $resp->json()]);
            return response()->json(['error' => 'paypal_create_failed'], 500);
        }

        $paypalOrderId = (string) $resp->json('id');

        // salvo il riferimento PayPal sul nostro ordine
        $order->update([
            'payment_provider'   => 'paypal',
            'payment_session_id' => $paypalOrderId,
        ]);

        return response()->json(['id' => $paypalOrderId]);
    }

    /**
     * Capture (AJAX): verifica ordine PayPal -> trova il nostro order_id -> cattura -> aggiorna stato
     */
    public function captureOrder(Request $request)
    {
        $data = $request->validate([
            'orderID' => 'required|string',
        ]);

        $paypalOrderId = $data['orderID'];
        $accessToken   = $this->getAccessToken();

        // prima leggo i dettagli PayPal per riprendere reference_id (id ordine interno)
        $detailsResp = Http::withToken($accessToken)
            ->get($this->apiBase()."/v2/checkout/orders/{$paypalOrderId}");

        if (!$detailsResp->successful()) {
            Log::error('PayPal details failed', ['status' => $detailsResp->status(), 'body' => $detailsResp->json()]);
            session()->flash('fail_message', 'Impossibile recuperare i dettagli da PayPal.');

            return response()->json([
                'status'   => 'failed',
                'redirect' => route('payment.failed', ['order' => 0]),
            ], 500);
        }

        // reference_id settato in createOrder() -> è il nostro Order id
        $referenceId = data_get($detailsResp->json(), 'purchase_units.0.reference_id');
        $order = Order::findOrFail((int) $referenceId);

        $this->authorizeOwner($order);

        // se nel frattempo è scaduto/non pagabile, stoppo e mando 409 (UX coerente)
        if (!$this->isPayable($order)) {
            session()->flash('fail_message', 'Ordine scaduto o non più pagabile.');
            return response()->json([
                'status'   => 'not_payable',
                'redirect' => route('payment.failed', $order),
            ], 409);
        }

        // chiamata di capture vera e propria (body vuoto richiesto)
        $captureResp = Http::withToken($accessToken)
            ->withBody('{}', 'application/json')
            ->post($this->apiBase()."/v2/checkout/orders/{$paypalOrderId}/capture");

        if (!$captureResp->successful()) {
            Log::error('PayPal capture failed', [
                'status' => $captureResp->status(),
                'body'   => $captureResp->json(),
            ]);

            session()->flash('fail_message', 'Errore durante la cattura del pagamento su PayPal.');
            return response()->json([
                'status'   => 'failed',
                'redirect' => route('payment.failed', $order),
            ], 500);
        }

        // se COMPLETED -> pago e porto ordine in stato "paid"
        $status    = (string) $captureResp->json('status');
        $captureId = data_get($captureResp->json(), 'purchase_units.0.payments.captures.0.id');

        if ($status === 'COMPLETED' && $captureId) {
            $order->update([
                'payment_status'     => 'paid',
                'status'             => 'paid',
                'payment_intent_id'  => $captureId,
                'payment_session_id' => $paypalOrderId,
            ]);

            return response()->json([
                'status'   => 'paid',
                'redirect' => route('payment.show', $order),
            ]);
        }

        // fallback: capture ok ma non "completed"
        session()->flash('fail_message', 'Il pagamento non è stato completato.');
        return response()->json([
            'status'   => 'not_completed',
            'redirect' => route('payment.failed', $order),
        ], 422);
    }
}
