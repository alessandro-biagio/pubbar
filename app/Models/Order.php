<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    // stati ordine (stringhe per semplicità / compatibilità DB)
    public const STATUS_PENDING   = 'pending';
    public const STATUS_PAID      = 'paid';
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_READY     = 'ready';
    public const STATUS_CANCELLED = 'cancelled';

    public const ALL_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PAID,
        self::STATUS_PREPARING,
        self::STATUS_READY,
        self::STATUS_CANCELLED,
    ];

    /**
     * Macchina a stati minimale (staff)
     * NB: pending non va in paid qui perché lo gestisce il pagamento (PayPal capture)
     */
    public const ALLOWED_TRANSITIONS = [
        self::STATUS_PENDING   => [self::STATUS_CANCELLED],
        self::STATUS_PAID      => [self::STATUS_PREPARING, self::STATUS_CANCELLED],
        self::STATUS_PREPARING => [self::STATUS_READY, self::STATUS_CANCELLED],
        self::STATUS_READY     => [],
        self::STATUS_CANCELLED => [],
    ];

    // campi scrivibili da flow checkout/pagamento/staff
    protected $fillable = [
        'user_id','code','status','pickup_at','expires_at','total',
        'customer_name','customer_phone','notes',
        'payment_provider','payment_status','payment_session_id','payment_intent_id',
    ];

    protected $casts = [
        'pickup_at'  => 'datetime',
        'expires_at' => 'datetime',
        // 'total' => 'decimal:2', // se vuoi cast numerico sempre coerente
    ];

    // default lato model (se non arriva status)
    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    // === Eventi ===
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            // codice ordine human-friendly (PB-YYYYMMDD-XXXX)
            if (empty($order->code)) {
                $prefix = 'PB-' . now('Europe/Rome')->format('Ymd') . '-';
                $order->code = $prefix . Str::upper(Str::random(4));
            }

            // safety: status mai null
            if (empty($order->status)) {
                $order->status = self::STATUS_PENDING;
            }
        });
    }

    // === Relazioni ===
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // === Helper stato ===
    public function canTransitionTo(string $to): bool
    {
        // controllo transizione valida (tabella ALLOWED_TRANSITIONS)
        $from = $this->status;
        $allowed = self::ALLOWED_TRANSITIONS[$from] ?? [];

        return in_array($to, $allowed, true);
    }

    public function isFinal(): bool
    {
        // stati terminali: non dovrebbero più cambiare
        return in_array($this->status, [self::STATUS_READY, self::STATUS_CANCELLED], true);
    }

    // === Scope utili per dashboard ===
    public function scopeStatus($query, ?string $status)
    {
        // applico filtro solo se lo status è valido
        if ($status && in_array($status, self::ALL_STATUSES, true)) {
            $query->where('status', $status);
        }

        return $query;
    }

    public function scopeRecent($query)
    {
        // ordinamento standard in backoffice
        return $query->orderByDesc('created_at');
    }

    public function scopeSearch($query, ?string $q)
    {
        if (!$q) return $query;

        // ricerca base per staff (id/code/cliente/telefono/note)
        return $query->where(function ($qq) use ($q) {
            $qq->where('id', $q)
               ->orWhere('code', 'like', "%{$q}%")
               ->orWhere('customer_name', 'like', "%{$q}%")
               ->orWhere('customer_phone', 'like', "%{$q}%")
               ->orWhere('notes', 'like', "%{$q}%");
        });
    }
}
