<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'plane_name',
        'origin',
        'destination',
        'departure_time',
        'price',
        'stock',
    ];

    protected $casts = [
        'departure_time' => 'datetime',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}

