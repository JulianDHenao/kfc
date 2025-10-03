<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MenuItemController; 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController; 


Route::get('/', [MenuItemController::class, 'storefront'])->name('storefront');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas protegidas por el middleware 'auth'
Route::middleware('auth')->group(function () {
    // Rutas de Checkout/Pedido
    Route::get('/checkout', [OrderController::class, 'showCheckoutForm'])->name('checkout.show'); 
    Route::post('/checkout', [OrderController::class, 'processOrder'])->name('checkout.process');
    Route::get('/order/{order}', [OrderController::class, 'showOrder'])->name('order.show');
    
    // Rutas de Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de Gestión del Menú (CRUD)
    Route::resource('menu', MenuItemController::class); 
});

// Rutas de Carrito (No requieren autenticación)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{item}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/update/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{item}', [CartController::class, 'remove'])->name('cart.remove');

// Mueve el archivo de autenticación al final, siguiendo el estándar de Laravel.
require __DIR__.'/auth.php'; 

// Importa el Menu de la base de datos si es necesario.
// use App\Models\MenuItem; 
// Si no lo importas, usa el mock $menuItems definido abajo.

Route::get('/chat', function () {
    // ----------------------------------------------------
    // INICIO: Lógica para pasar datos (Mock de datos)
    // ----------------------------------------------------
    
    // Si quieres usar datos reales, descomenta y ajusta:
    // $menuItems = MenuItem::all();
    // $groupedItems = $menuItems->groupBy('category');
    
    // Si usas el mock para simplificar (RECOMENDADO PARA DEPURAR):
    $groupedItems = collect([
        (object)['category' => 'Combos', 'items' => [
            (object)['name' => 'Mega Box', 'price' => 12.99],
            (object)['name' => 'Dúo Crispy', 'price' => 7.50],
        ]],
        (object)['category' => 'Postres', 'items' => [
            (object)['name' => 'Sundae Vainilla', 'price' => 3.00],
        ]]
    ]);

    // ----------------------------------------------------
    // FIN: Lógica para pasar datos
    // ----------------------------------------------------

    // Asegúrate de que el nombre de la vista sea el mismo que el nombre del archivo: 'chat'
    return view('chat', ['groupedItems' => $groupedItems]); 
});
