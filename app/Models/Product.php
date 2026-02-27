<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    // campi gestibili da CRUD staff
    protected $fillable = [
        'category_id','name','slug','description','price','image_path','is_available'
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'is_available' => 'boolean',
    ];

    /**
     * Relazione: prodotto -> categoria
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relazione: prodotto -> varianti (ordinate per nome)
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->orderBy('name');
    }

    /**
     * Accessor: prezzo minimo disponibile
     * - se ci sono varianti disponibili → min(varianti)
     * - altrimenti → prezzo base prodotto
     */
    protected $appends = ['min_price'];

    public function getMinPriceAttribute()
    {
        // se le varianti sono già caricate, evito query extra
        $min = $this->relationLoaded('variants')
            ? $this->variants->where('is_available', true)->min('price')
            : $this->variants()->where('is_available', true)->min('price');

        return (float) ($min ?? $this->price);
    }

    /**
     * Hook: slug automatico e univoco su create/update
     */
    protected static function booted()
    {
        // create: slug dal nome se non fornito
        static::creating(function ($p) {
            if (empty($p->slug)) {
                $p->slug = static::uniqueSlug($p->name);
            }
        });

        // update: se cambia il nome, aggiorno lo slug
        static::updating(function ($p) {
            if ($p->isDirty('name')) {
                $p->slug = static::uniqueSlug($p->name, $p->id);
            }
        });
    }

    /**
     * Genera slug univoco (aggiunge -1, -2, ...)
     */
    protected static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id','!=',$ignoreId))
            ->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
