<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount',
        'status',
        'delivery_address',
        'phone_number',
    ];

    // Relación: Una orden tiene muchos ítems (OrderItems)
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    // Relación: Una orden pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
