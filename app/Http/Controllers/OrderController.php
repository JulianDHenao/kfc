<?php

namespace App\Http\Controllers;

use App\Models\Order; 
use App\Models\OrderItem; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    // 1. Muestra el formulario de checkout (Ruta: checkout.show)
    public function showCheckoutForm()
    {
        if (!session('cart') || count(session('cart')) === 0) {
            return redirect()->route('cart.index')->with('error', 'Tu carrito está vacío.');
        }
        
        return view('checkout.show');
    }

    // 2. Procesa el pedido, guarda en BD y vacía el carrito (Ruta: checkout.process)
    public function processOrder(Request $request)
    {
        // 1. Validar los datos de envío
        $request->validate([
            'delivery_address' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
        ]);

        $cart = session('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Tu carrito está vacío. No se puede procesar el pedido.');
        }

        // 2. Calcular el total
        $totalAmount = array_sum(array_map(function ($item) {
            return $item['price'] * $item['quantity'];
        }, $cart));

        // 3. Usar una Transacción para asegurar la integridad de los datos
        try {
            DB::beginTransaction();

            // A. Crear el Encabezado de la Orden
            $order = Order::create([
                'user_id' => Auth::id(),
                'total_amount' => $totalAmount,
                'delivery_address' => $request->delivery_address,
                'phone_number' => $request->phone_number,
                'status' => 'pendiente',
            ]);

            // B. Crear los Ítems de la Orden
            foreach ($cart as $itemId => $details) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $itemId,
                    'name' => $details['name'],
                    'price' => $details['price'],
                    'quantity' => $details['quantity'],
                ]);
            }

            // C. Vaciar el Carrito
            session()->forget('cart');

            DB::commit();

            // 4. Redirigir a la vista de la factura/confirmación
            return redirect()->route('order.show', $order->id)
                ->with('success', '¡Tu pedido ha sido realizado con éxito! ID: ' . $order->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al procesar el pedido. Inténtalo de nuevo.');
        }
    }
    
    // 3. Muestra los detalles de una orden específica (La factura)
    public function showOrder(Order $order)
    {
        // Asegurarse de que el usuario solo pueda ver sus propias órdenes
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Acceso Denegado.');
        }
        
        $order->load('items');
        
        return view('orders.show', compact('order'));
    }
}