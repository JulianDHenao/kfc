<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // database/seeders/RestaurantSeeder.php
public function run(): void
    {

            \App\Models\Restaurant::create([
            'name' => 'KFC - Mall Plaza',
            'address' => 'Carrera 14, Av. Kevin Ángel #55D – 251, Manizales, Caldas',
            'city' => 'Manizales',
            'state' => 'Caldas',
            'phone_number' => '606-555-0107',
            'latitude' => 5.066051863472174,
            'longitude' => -75.4903415131553,   
        ]);

        \App\Models\Restaurant::create([
            'name' => 'KFC - Fundadores',
            'address' => 'Cl. 33b #20-03, Centro',
            'city' => 'Manizales',
            'state' => 'Caldas',
            'phone_number' => '606-555-0108',
            'latitude' => 5.069170806216724,
            'longitude' => -75.5099448855809,
        ]);

        \App\Models\Restaurant::create([
            'name' => 'KFC - Titán Plaza',
            'address' => 'Cra. 72 #80-94, Engativá',
            'city' => 'Bogotá',
            'state' => 'Bogotá D.C.',
            'phone_number' => '601-555-0101',
            'latitude' => 4.6925,
            'longitude' => -74.0886,
        ]);

        \App\Models\Restaurant::create([
            'name' => 'KFC - Parque Lleras',
            'address' => 'Cra. 38 #9A-26, El Poblado',
            'city' => 'Medellín',
            'state' => 'Antioquia',
            'phone_number' => '604-555-0102',
            'latitude' => 6.2096,
            'longitude' => -75.5673,
        ]);

        \App\Models\Restaurant::create([
            'name' => 'KFC - Chipichape',
            'address' => 'Cl. 38 Nte. #6N-35, Santa Monica',
            'city' => 'Cali',
            'state' => 'Valle del Cauca',
            'phone_number' => '602-555-0103',
            'latitude' => 3.4775,
            'longitude' => -76.5302,
        ]);

        \App\Models\Restaurant::create([
            'name' => 'KFC - Buenavista',
            'address' => 'Cl. 98 #52-115, Riomar',
            'city' => 'Barranquilla',
            'state' => 'Atlántico',
            'phone_number' => '605-555-0104',
            'latitude' => 11.0115,
            'longitude' => -74.8239,
        ]);

        \App\Models\Restaurant::create([
            'name' => 'KFC - Cacique El Centro',
            'address' => 'Tv. 93 #34-99, Cañaveral',
            'city' => 'Bucaramanga',
            'state' => 'Santander',
            'phone_number' => '607-555-0105',
            'latitude' => 7.1018,
            'longitude' => -73.1095,
        ]);

        \App\Models\Restaurant::create([
            'name' => 'KFC - Bocagrande',
            'address' => 'Av. San Martín #4-1, Bocagrande',
            'city' => 'Cartagena',
            'state' => 'Bolívar',
            'phone_number' => '605-555-0106',
            'latitude' => 10.4095,
            'longitude' => -75.5535,
        ]);

;
    }
}
