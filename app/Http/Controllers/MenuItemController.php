<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    /**
     * Muestra la página principal de la tienda.
     */
    public function storefront()
    {
        $menuItems = MenuItem::all();
        $groupedItems = $menuItems->groupBy('category');
        return view('storefront', compact('groupedItems'));
    }

    /**
     * Muestra la lista de todos los items del menú para gestionarlos.
     * Ruta: GET /menu
     */
    public function index()
    {
        $menuItems = MenuItem::orderBy('category')->orderBy('name')->get();
        return view('menu.index', compact('menuItems'));
    }

    /**
     * Muestra el formulario para crear un nuevo item.
     * Ruta: GET /menu/create
     */
    public function create()
    {
        // Pasamos un item vacío para reutilizar el formulario
        return view('menu.create', ['item' => new MenuItem()]);
    }

    /**
     * Guarda un nuevo item en la base de datos.
     * Ruta: POST /menu
     */
    public function store(Request $request)
    {
        // Solución: Si image_url es una cadena vacía, la convertimos a null antes de validar.
        if ($request->input('image_url') === '') {
            $request->merge(['image_url' => null]);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'image_url' => 'nullable|string|max:255', // Cambiamos 'url' por 'string'
        ]);

        MenuItem::create($validatedData);

        return redirect()->route('menu.index')->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Muestra un item específico (no se usa mucho en CRUDs de admin, pero es parte del resource).
     * Ruta: GET /menu/{menu}
     */
    public function show(MenuItem $menu)
    {
        // Normalmente redirigimos a la edición
        return redirect()->route('menu.edit', $menu);
    }

    /**
     * Muestra el formulario para editar un item existente.
     * Laravel inyecta automáticamente el MenuItem gracias al Route-Model Binding.
     * Ruta: GET /menu/{menu}/edit
     */
    public function edit(MenuItem $menu)
    {
        return view('menu.edit', ['item' => $menu]);
    }

    /**
     * Actualiza un item existente en la base de datos.
     * Ruta: PUT/PATCH /menu/{menu}
     */
    public function update(Request $request, MenuItem $menu)
    {
        // Solución: Si image_url es una cadena vacía, la convertimos a null antes de validar.
        if ($request->input('image_url') === '') {
            $request->merge(['image_url' => null]);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'image_url' => 'nullable|string|max:255', // Cambiamos 'url' por 'string'
        ]);

        $menu->update($validatedData);

        return redirect()->route('menu.index')->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Elimina un item de la base de datos.
     * Ruta: DELETE /menu/{menu}
     */
    public function destroy(MenuItem $menu)
    {
        try {
            $menu->delete();
            return redirect()->route('menu.index')->with('success', 'Producto eliminado exitosamente.');
        } catch (\Exception $e) {
            // Manejo de errores por si el producto está en un pedido, etc.
            return redirect()->route('menu.index')->with('error', 'No se pudo eliminar el producto. Es posible que esté asociado a un pedido.');
        }
    }
}