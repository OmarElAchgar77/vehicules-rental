<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand',
        'model',
        'year',
        'registration_number',
        'daily_price',
        'description',
        'image',
        'is_available',
        'seats',
        'fuel_type',
        'transmission'
    ];

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function isAvailableForDates($startDate, $endDate)
    {
        return !$this->rentals()
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();
    }
}