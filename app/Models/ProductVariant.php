<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id','name','volume_ml','price','is_available'];

    protected $casts = [
        'volume_ml'    => 'integer',
        'price'        => 'decimal:2',
        'is_available' => 'boolean',
    ];

    public function product() { return $this->belongsTo(Product::class); }
}
