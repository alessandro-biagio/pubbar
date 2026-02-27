<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\KitchenCapacityOverride;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KitchenCapacityController extends Controller
{
    /**
     * Pagina gestione capacità cucina: default per categoria + override orari (oggi 18-23)
     */
    public function edit()
    {
        $tz = 'Europe/Rome';

        // categorie attive su cui gestisco la capacità (chiavi = slug)
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id','name','slug']);

        // default per categoria (Settings), fallback 30 se non esiste
        $defaults = [];
        foreach ($categories as $c) {
            $defaults[$c->slug] = (int) (Setting::where('key', "kitchen.capacity.default.{$c->slug}")
                ->value('value') ?? 30);
        }

        // griglia oraria: solo oggi dalle 18 alle 23 (startOfHour)
        $baseDay = now($tz)->startOfDay();
        $hours = collect(range(18, 23))
            ->map(fn ($h) => $baseDay->copy()->addHours($h));

        // override esistenti indicizzati per [hour_start][category_slug]
        $overrides = KitchenCapacityOverride::whereIn('hour_start', $hours)->get()
            ->groupBy(fn ($k) => $k->hour_start->toDateTimeString())
            ->map(fn ($g) => $g->keyBy('category_slug'));

        return view('staff.capacity.edit', compact('categories','defaults','hours','overrides'));
    }

    /**
     * Salva i default (per categoria): kitchen.capacity.default.<slug>
     */
    public function updateDefaults(Request $request)
    {
        $request->validate([
            'defaults'   => ['required','array'],
            'defaults.*' => ['nullable','integer','min:1','max:1000'],
        ]);

        // salvo solo i valori presenti (vuoto = non tocco)
        foreach ($request->input('defaults', []) as $slug => $val) {
            if ($val === null || $val === '') continue;

            Setting::updateOrCreate(
                ['key' => "kitchen.capacity.default.{$slug}"],
                ['value' => (string) ((int) $val)]
            );
        }

        return back()->with('success','Default aggiornati.');
    }

    /**
     * Salva override orari: overrides[hour][slug] = cap | "" (vuoto => cancella override e torna al default)
     */
    public function saveOverrides(Request $request)
    {
        $data = $request->input('overrides', []);

        foreach ($data as $hourStart => $groups) {
            // normalizzo sempre a inizio ora (coerenza con query capacità checkout)
            $hour = Carbon::parse($hourStart, 'Europe/Rome')->startOfHour();

            foreach ($groups as $slug => $cap) {
                // campo vuoto = remove override
                if ($cap === null || $cap === '') {
                    KitchenCapacityOverride::where('hour_start', $hour)
                        ->where('category_slug', $slug)
                        ->delete();
                    continue;
                }

                // clamp lato server (UI può sbagliare)
                $cap = (int) $cap;
                if ($cap < 1 || $cap > 1000) continue;

                KitchenCapacityOverride::updateOrCreate(
                    ['hour_start' => $hour, 'category_slug' => $slug],
                    ['capacity'   => $cap]
                );
            }
        }

        return back()->with('success','Override salvati.');
    }
}
