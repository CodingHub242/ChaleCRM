<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'unit_price',
        'currency',
        'unit',
        'quantity',
        'owner_id',
        'active',
    ];

    protected $casts = [
        'unit_price' => 'float',
        'quantity' => 'integer',
        'active' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
