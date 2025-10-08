<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// database/migrations/YYYY_MM_DD_HHMMSS_create_restaurants_table.php
    public function up(): void
    {
    Schema::create('restaurants', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('address');
        $table->string('city');
        $table->string('state');
        $table->string('phone_number')->nullable();
        $table->decimal('latitude', 10, 7); // Precisión para coordenadas
        $table->decimal('longitude', 10, 7); // Precisión para coordenadas
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
