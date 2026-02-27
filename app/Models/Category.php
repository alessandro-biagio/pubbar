<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use App\Models\KitchenCapacityOverride;

class Category extends Model
{
    // campi gestibili da form staff
    protected $fillable = ['name','slug','description','image_path','is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relazione: categoria -> prodotti
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Hook model:
     * - slug univoco su create/update
     * - tiene allineati Setting + Override se cambia lo slug
     */
    protected static function booted()
    {
        // create: genera slug se non presente (o normalizza quello passato)
        static::creating(function (Category $cat) {
            if (empty($cat->slug)) {
                $cat->slug = static::uniqueSlug(Str::slug($cat->name));
            } else {
                $cat->slug = static::uniqueSlug(Str::slug($cat->slug));
            }
        });

        // update: se cambia name, rigenera lo slug (coerenza SEO/URL)
        static::updating(function (Category $cat) {
            if ($cat->isDirty('name')) {
                $cat->slug = static::uniqueSlug(Str::slug($cat->name), $cat->id);
            }
        });

        // dopo create: creo sempre una capacity default per questa categoria
        static::created(function (Category $cat) {
            Setting::updateOrCreate(
                ['key' => "kitchen.capacity.default.{$cat->slug}"],
                ['value' => '30']
            );
        });

        // dopo update: se lo slug cambia, sposto le chiavi legate alla capacity
        static::updated(function (Category $cat) {
            if ($cat->wasChanged('slug')) {
                $oldSlug = $cat->getOriginal('slug');
                $newSlug = $cat->slug;

                DB::transaction(function () use ($oldSlug, $newSlug) {
                    // migrazione default capacity (Setting) dal vecchio slug al nuovo
                    $oldKey = "kitchen.capacity.default.{$oldSlug}";
                    $newKey = "kitchen.capacity.default.{$newSlug}";
                    $oldVal = Setting::where('key', $oldKey)->value('value');

                    if ($oldVal !== null) {
                        Setting::updateOrCreate(['key' => $newKey], ['value' => $oldVal]);
                        // pulizia: elimino la vecchia chiave
                        Setting::where('key', $oldKey)->delete();
                    } else {
                        // fallback: se non c'era, imposto 30
                        Setting::updateOrCreate(['key' => $newKey], ['value' => '30']);
                    }

                    // riallineo override orari: slug vecchio -> slug nuovo
                    // NB: qui il campo si chiama "group" (in altri punti usi "category_slug")
                    KitchenCapacityOverride::where('category_slug', $oldSlug)
                        ->update(['category_slug' => $newSlug]);
                });
            }
        });
    }

    /**
     * Slug univoco: se esiste già, aggiunge suffissi -2, -3, ...
     */
    protected static function uniqueSlug(string $baseSlug, int $ignoreId = null): string
    {
        $slug = $baseSlug ?: 'categoria';
        $i = 1;

        // loop fino a slug libero (ignorando l'id corrente in update)
        while (static::query()
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $i++;
            $slug = $baseSlug . '-' . $i;
        }

        return $slug;
    }
}
