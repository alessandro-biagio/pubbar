<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenCapacityOverride extends Model
{
    protected $table = 'kitchen_capacity_overrides';

    protected $fillable = [
        'hour_start',
        'category_slug',
        'capacity',
    ];

    protected $casts = [
        'hour_start' => 'datetime',
        'capacity'   => 'integer',
    ];
}
