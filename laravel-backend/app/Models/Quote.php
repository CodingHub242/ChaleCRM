<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_number',
        'contact_id',
        'company_id',
        'deal_id',
        'subject',
        'status',
        'expiration_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total',
        'currency',
        'terms',
        'owner_id',
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'subtotal' => 'float',
        'tax_amount' => 'float',
        'discount_amount' => 'float',
        'total' => 'float',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function items()
    {
        return $this->hasMany(QuoteItem::class);
    }
}

class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'product_id',
        'name',
        'description',
        'quantity',
        'unit_price',
        'discount',
        'amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'float',
        'discount' => 'float',
        'amount' => 'float',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
