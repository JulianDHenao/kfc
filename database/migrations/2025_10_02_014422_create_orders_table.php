<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // Clave foránea al usuario que realiza el pedido
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->decimal('total_amount', 10, 2);
            
            // Estado del pedido: pendiente, procesando, enviado, completado, cancelado
            $table->string('status')->default('pendiente'); 
            
            // Datos de envío/entrega
            $table->string('delivery_address');
            $table->string('phone_number')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};