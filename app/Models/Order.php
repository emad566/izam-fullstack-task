<?php

namespace App\Models;

use App\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'order_number',
        'user_id',
        'total_amount',
        'status',
        'notes',
        'order_date',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'total_amount' => 'decimal:2',
        'status' => OrderStatus::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(Str::random(8));
            }
            if (empty($order->order_date)) {
                $order->order_date = now();
            }
            if (empty($order->status)) {
                $order->status = OrderStatus::default();
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products')
                    ->withPivot('quantity', 'unit_price', 'total_price')
                    ->withTimestamps();
    }

    // Helper methods
    public function calculateTotal()
    {
        return $this->orderProducts->sum('total_price');
    }

    public function updateTotal()
    {
        $this->update(['total_amount' => $this->calculateTotal()]);
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
