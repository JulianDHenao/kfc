<?php

namespace App\Http\Controllers;

use App\Models\MenuItem; // <-- ¡Añadir el modelo!
use Illuminate\Http\Request; // <-- ¡Añadir el request!

class MenuItemController extends Controller
{
    // Muestra la vista principal de la tienda/menú (Ruta: /)
    public function storefront()
    {
        // Trae todos los elementos del menú y los agrupa por categoría
        $items = MenuItem::all()->groupBy('category');
        
        return view('storefront.index', compact('items'));
    }

    // Muestra la lista de productos para administración (Ruta: /menu)
    public function index()
    {
        $items = MenuItem::paginate(15);
        return view('menu.index', compact('items'));
    }
    
    // Aquí irán los demás métodos CRUD (create, store, show, edit, update, destroy)
    // ...
}