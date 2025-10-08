<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'address',
        'city',
        'state',
        'phone_number',
        'latitude',
        'longitude',
    ];
}
