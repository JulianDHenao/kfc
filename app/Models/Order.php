<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'total_amount',
        'delivery_address',
        'phone_number',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * Esto le dice a Laravel que trate estos campos como objetos de fecha,
     * permitiendo la conversión automática de zona horaria.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación: Una orden tiene muchos ítems.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relación: Una orden pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

