<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class MenuItemController extends Controller
{
    /**
     * Muestra la página principal de la tienda (Storefront).
     * * Este es el método que tu ruta principal (web.php) está tratando de llamar.
     */
    public function storefront()
    {
        // 1. Obtener todos los items del menú
        $menuItems = MenuItem::all();

        // 2. Agrupar los items por categoría
        // El resultado es una Colección de Colecciones (groupedItems)
        $groupedItems = $menuItems->groupBy('category');

        // 3. Devolver la vista 'storefront' y pasar los items agrupados
        // Pasamos la variable $groupedItems a la vista.
        return view('storefront', compact('groupedItems'));
    }

    /**
     * Muestra una lista de todos los elementos del menú (para gestión).
     */
    public function index()
    {
        $menuItems = MenuItem::all();
        return view('menu.index', compact('menuItems'));
    }

    /**
     * Muestra el formulario para crear un nuevo elemento del menú.
     */
    public function create()
    {
        return view('menu.create');
    }

    /**
     * Almacena un nuevo elemento del menú en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:255',
            'image_url' => 'nullable|string|max:255', // En un entorno real, manejarías la subida de archivos
        ]);

        MenuItem::create($request->all());

        return Redirect::route('menu.index')->with('success', 'Producto creado con éxito.');
    }

    /**
     * Muestra el formulario para editar un elemento específico del menú.
     */
    public function edit(MenuItem $menuItem)
    {
        return view('menu.edit', compact('menuItem'));
    }

    /**
     * Actualiza el elemento del menú especificado en el almacenamiento.
     */
    public function update(Request $request, MenuItem $menuItem)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:255',
            'image_url' => 'nullable|string|max:255',
        ]);

        $menuItem->update($request->all());

        return Redirect::route('menu.index')->with('success', 'Producto actualizado con éxito.');
    }

    /**
     * Elimina el elemento del menú especificado del almacenamiento.
     */
    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();
        return Redirect::route('menu.index')->with('success', 'Producto eliminado con éxito.');
    }
}
