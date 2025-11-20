<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run()
    {
        Vehicle::create([
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2022,
            'registration_number' => 'AB-123-CD',
            'daily_price' => 45.00,
            'description' => 'Voiture familiale économique et fiable',
            'seats' => 5,
            'fuel_type' => 'essence',
            'transmission' => 'automatique',
            'is_available' => true,
        ]);

        Vehicle::create([
            'brand' => 'Renault',
            'model' => 'Clio',
            'year' => 2023,
            'registration_number' => 'EF-456-GH',
            'daily_price' => 35.00,
            'description' => 'Compacte et facile à conduire en ville',
            'seats' => 5,
            'fuel_type' => 'diesel',
            'transmission' => 'manuelle',
            'is_available' => true,
        ]);

        Vehicle::create([
            'brand' => 'BMW',
            'model' => 'Serie 3',
            'year' => 2023,
            'registration_number' => 'IJ-789-KL',
            'daily_price' => 75.00,
            'description' => 'Berline premium avec tous les équipements',
            'seats' => 5,
            'fuel_type' => 'essence',
            'transmission' => 'automatique',
            'is_available' => true,
        ]);
    }
}