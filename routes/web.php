<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MenuItemController; 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;

Route::get('/', [MenuItemController::class, 'storefront'])->name('storefront'); // <-- Ruta Pública de la Tienda

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas de administración protegidas por el middleware 'auth'
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Rutas de Gestión del Menú (CRUD)
    Route::resource('menu', MenuItemController::class); 
});

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{item}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/update/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{item}', [CartController::class, 'remove'])->name('cart.remove');

require __DIR__.'/auth.php';