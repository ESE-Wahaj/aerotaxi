<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'reference',
        'from_location',
        'to_location',
        'depart_date',
        'depart_time',
        'vehicle_id',
        'passenger_name',
        'email',
        'phone',
        'flight_number',
        'note_to_driver',
        'country_code',
        'status',
        'payment_status',
        'payment_id',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'depart_date' => 'date',
            'total_price' => 'decimal:2',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
