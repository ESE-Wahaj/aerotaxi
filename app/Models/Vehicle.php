<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price',
        'short_base',
        'short_per_mile',
        'long_base',
        'long_per_mile',
        'passengers',
        'suitcases',
        'hand_luggage_note',
        'image',
        'description',
        'car_model',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
