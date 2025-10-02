<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // En database/migrations/*_create_menu_items_table.php

    public function up(): void
    {
    Schema::create('menu_items', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique();
        $table->text('description')->nullable();
        $table->decimal('price', 8, 2); // Precio con 2 decimales
        $table->string('category')->default('Pollo'); // Ejemplo: Pollo, Bebidas, Postres
        $table->string('image_url')->nullable(); // Para guardar la URL o path de la imagen
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
