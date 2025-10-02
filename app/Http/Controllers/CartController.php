<?php

namespace App\Http\Controllers;

use App\Models\MenuItem; // Importamos el modelo del menú
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException; // Importar esto si no existe

class CartController extends Controller
{
    // Muestra el contenido del carrito (Ruta: /cart)
    public function index()
    {
        // Obtener el array del carrito de la sesión. Si no existe, devuelve un array vacío.
        $cart = session()->get('cart', []);
        
        // Carga la vista del carrito
        return view('cart.index', compact('cart'));
    }

    // Añade un ítem al carrito (Ruta POST: /cart/add/{item})
    public function add(Request $request, MenuItem $item)
    {
        // 1. Obtener el carrito de la sesión
        $cart = session()->get('cart', []);
        $itemId = $item->id;

        if (isset($cart[$itemId])) {
            // Si el ítem ya existe, incrementamos la cantidad
            $cart[$itemId]['quantity']++;
        } else {
            // Si es un ítem nuevo, lo añadimos
            $cart[$itemId] = [
                "id" => $item->id,
                "name" => $item->name,
                "price" => $item->price,
                "quantity" => 1,
            ];
        }

        // 2. Guardar el carrito actualizado de nuevo en la sesión
        session()->put('cart', $cart);

        // 3. Redirigir de vuelta al menú con un mensaje
        return redirect()->back()->with('success', '¡Producto añadido al carrito!');
    }
    
    // Actualiza la cantidad de un ítem en el carrito (Ruta PATCH: /cart/update/{item})
    public function update(Request $request, MenuItem $item)
    {
        try {
            // Validamos que sea un número >= 1
            $request->validate(['quantity' => 'required|integer|min:1']);
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', 'La cantidad debe ser un número entero mayor a cero.');
        }


        $cart = session()->get('cart', []);
        $itemId = $item->id;

        if (isset($cart[$itemId])) {
            // Actualizamos la cantidad con el valor enviado desde el formulario
            $cart[$itemId]['quantity'] = $request->quantity;
            session()->put('cart', $cart);
            
            return redirect()->back()->with('success', 'Cantidad actualizada correctamente.');
        }

        return redirect()->back()->with('error', 'El producto no se encontró en el carrito.');
    }

    // Elimina un ítem del carrito (Ruta DELETE: /cart/remove/{item})
    public function remove(MenuItem $item)
    {
        $cart = session()->get('cart');
        $itemId = $item->id;

        if (isset($cart[$itemId])) {
            // Eliminamos el ítem del array del carrito
            unset($cart[$itemId]);
            
            // Guardamos el carrito de nuevo en la sesión
            session()->put('cart', $cart);
        }

        return redirect()->back()->with('success', 'Producto eliminado del carrito.');
    }
}