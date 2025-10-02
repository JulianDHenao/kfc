<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // En database/migrations/*_create_order_items_table.php

public function up(): void
{
    Schema::create('order_items', function (Blueprint $table) {
        $table->id();
        
        // Clave foránea al pedido principal
        $table->foreignId('order_id')->constrained()->onDelete('cascade');
        
        // Clave foránea al producto (MenuItem)
        $table->foreignId('menu_item_id')->nullable()->constrained()->onDelete('set null'); 
        
        // Almacenar el nombre y precio en caso de que el producto sea eliminado del menú
        $table->string('name');
        $table->decimal('price', 8, 2); 
        $table->integer('quantity');
        
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
