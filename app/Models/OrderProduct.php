<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderProduct extends Model
{
    use HasFactory;

    protected $table = 'order_products';

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($orderProduct) {
            // Auto-calculate total price if not set
            if (empty($orderProduct->total_price)) {
                $orderProduct->total_price = $orderProduct->quantity * $orderProduct->unit_price;
            }
        });

        static::updating(function ($orderProduct) {
            // Auto-recalculate total price when quantity or unit price changes
            $orderProduct->total_price = $orderProduct->quantity * $orderProduct->unit_price;
        });
    }

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
