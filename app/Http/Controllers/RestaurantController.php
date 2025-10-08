<?php

namespace App\Http\Controllers;

use App\Models\Restaurant; // 1. Importar el modelo Restaurant
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    /**
     * Muestra la página con el mapa de restaurantes.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // 2. Obtener todos los restaurantes de la base de datos
        $restaurants = Restaurant::all();
        
        // 3. Pasar la colección de restaurantes a la vista
        return view('restaurants.index', compact('restaurants'));
    }
}