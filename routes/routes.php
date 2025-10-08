<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController; // Asegúrate de importar el controlador

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// MEJORA: Nueva ruta para obtener la cantidad de items en el carrito
Route::get('/cart/count', [CartController::class, 'count'])->name('api.cart.count');



Route::get('/restaurantes', [RestaurantController::class, 'index'])->name('restaurants.index');

// Nueva ruta para la página "Nosotros"
Route::get('/nosotros', function () {
    return view('nosotros');
})->name('nosotros');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

