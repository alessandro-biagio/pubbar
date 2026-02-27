<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class AutoCancelExpiredOrders extends Command
{
    // nome comando scheduler
    protected $signature = 'orders:auto-cancel-expired';

    // descrizione per artisan list
    protected $description = 'Imposta cancelled per gli ordini pending scaduti';

    /**
     * Job schedulato:
     * - trova ordini pending con expires_at passato
     * - li porta a cancelled
     */
    public function handle()
    {
        // update diretto (niente eventi/transizioni: è una cleanup tecnica)
        $n = Order::where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'cancelled']);

        // log utile per monitor scheduler
        Log::info("[scheduler] cancelled pending expired: {$n}");

        // output console (visibile se lanciato a mano)
        $this->info("Ordini pending scaduti cancellati: {$n}");

        return self::SUCCESS;
    }
}
