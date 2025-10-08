<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MenuItemController; 
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController; 
use App\Http\Controllers\RestaurantController;
use App\Models\MenuItem; // <-- MOVIMOS LA LÍNEA AQUÍ
use Illuminate\Support\Facades\Route;


Route::get('/', [MenuItemController::class, 'storefront'])->name('storefront');

Route::get('/restaurantes', [RestaurantController::class, 'index'])->name('restaurants.index');

// Nueva ruta para la página "Nosotros"
Route::get('/nosotros', function () {
    return view('nosotros');
})->name('nosotros'); // <-- Asegúrate de que esta línea exista y esté correcta.

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
Route::post('/cart/add/{item}', [CartController::class, 'add'])->where('item', '[0-9]+')->name('cart.add');
Route::patch('/cart/update/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{item}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');

// Mueve el archivo de autenticación al final, siguiendo el estándar de Laravel.
require __DIR__.'/auth.php'; 

Route::get('/chat', function () {
    // ----------------------------------------------------
    // INICIO: Lógica para pasar datos (Datos reales)
    // ----------------------------------------------------
    
    // Usamos datos reales de la base de datos
    $menuItems = MenuItem::all();
    $groupedItems = $menuItems->groupBy('category');
    
    // ----------------------------------------------------
    // FIN: Lógica para pasar datos
    // ----------------------------------------------------

    // Asegúrate de que el nombre de la vista sea el mismo que el nombre del archivo: 'chat'
    return view('chat', ['groupedItems' => $groupedItems]); 
});
