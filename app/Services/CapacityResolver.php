<?php

namespace App\Services;

use App\Models\KitchenCapacityOverride;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class CapacityResolver
{
    /**
     * Ritorna la capacità massima per una categoria (slug) in una data ora
     * Priorità: override orario → default per categoria → fallback hardcoded
     */
    public function forHourGroup(Carbon $pickupAt, string $categorySlug): int
    {
        // fallback di sicurezza se manca config o tabelle non migrate
        $defaultFallback = 30;

        // safety: evita errori in fase di deploy/migrate
        if (!Schema::hasTable('settings') || !Schema::hasTable('kitchen_capacity_overrides')) {
            return $defaultFallback;
        }

        // normalizzo sempre a inizio ora (coerente con checkout)
        $hourStart = $pickupAt->copy()->startOfHour();

        // 1) override esplicito per ora + categoria
        $ovr = KitchenCapacityOverride::where('hour_start', $hourStart)
            ->where('category_slug', $categorySlug) // slug categoria
            ->first();

        if ($ovr) {
            return (int) $ovr->capacity;
        }

        // 2) default per categoria (Setting)
        $key = "kitchen.capacity.default.{$categorySlug}";
        $default = Setting::where('key', $key)->value('value');

        // 3) fallback finale
        return $default !== null ? (int) $default : $defaultFallback;
    }
}
