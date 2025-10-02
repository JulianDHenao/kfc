<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tu Carrito de Compras') }} @if(!empty($cart)) ({{ count($cart) }} ítems) @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Mensaje de Éxito --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if(empty($cart))
                {{-- Carrito Vacío --}}
                <div class="bg-white p-10 shadow-xl rounded-lg text-center border-t-4 border-red-500">
                    <p class="text-2xl font-bold text-gray-800">Tu carrito está vacío 😟</p>
                    <p class="text-lg text-gray-600 mt-2">¡Añade tu primer producto de pollo!</p>
                    <a href="{{ url('/') }}" class="mt-6 inline-block bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition shadow-md">
                        Volver al Menú
                    </a>
                </div>
            @else
                @php $total = 0; @endphp
                <div class="bg-white shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="hidden md:grid grid-cols-6 font-bold border-b-2 pb-3 mb-4 text-gray-600 uppercase">
                            <div class="col-span-2">Producto</div>
                            <div>Precio</div>
                            <div>Cantidad</div>
                            <div>Subtotal</div>
                            <div></div> {{-- Columna para Eliminar --}}
                        </div>

                        {{-- Bucle para recorrer los ítems del carrito --}}
@foreach ($cart as $id => $details)
    @php $subtotal = $details['price'] * $details['quantity']; @endphp
    @php $total += $subtotal; @endphp

    <div class="grid grid-cols-6 items-center border-b py-4">
        {{-- Nombre del Producto (columna 1) --}}
        <div class="col-span-2 font-semibold text-gray-900">
            {{ $details['name'] }}
        </div>
        {{-- Precio (columna 2) --}}
        <div class="text-gray-600">${{ number_format($details['price'], 2) }}</div>
        
        {{-- Cantidad (Formulario para Actualizar - columna 3) --}}
        <div>
            <form action="{{ route('cart.update', $id) }}" method="POST" class="flex items-center space-x-2">
                @csrf
                @method('PATCH') {{-- Usa el método PATCH para actualizar --}}
                
                <input 
                    type="number" 
                    name="quantity" 
                    value="{{ $details['quantity'] }}" 
                    min="1" 
                    class="w-16 text-center border rounded-md focus:ring-red-500 focus:border-red-500"
                    onchange="this.form.submit()"> {{-- IMPORTANTE: Envía el form al cambiar el valor --}}
            </form>
        </div>

        {{-- Subtotal (columna 4) --}}
        <div class="font-bold text-lg text-red-600">${{ number_format($subtotal, 2) }}</div>

        {{-- Botón de Eliminación (Formulario DELETE - columna 5) --}}
        <div>
            <form action="{{ route('cart.remove', $id) }}" method="POST">
                @csrf
                @method('DELETE') {{-- Usa el método DELETE para eliminar --}}
                <button 
                    type="submit" 
                    class="text-gray-400 hover:text-red-500 transition text-2xl leading-none"
                    title="Eliminar producto">
                    &times; {{-- Símbolo de "x" --}}
                </button>
            </form>
        </div>
    </div>
@endforeach

                        <div class="mt-8 pt-4 border-t-4 border-dashed border-gray-300 flex justify-end items-center flex-col md:flex-row">
                            <div class="flex items-center">
                                <span class="text-2xl font-bold text-gray-900 mr-4">Total:</span>
                                <span class="text-4xl font-extrabold text-red-700">${{ number_format($total, 2) }}</span>
                            </div>
                            
                            <a href="{{ route('checkout.show') }}" class="mt-4 md:mt-0 md:ml-6 bg-green-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-green-700 transition shadow-lg">
    Proceder a Pagar
</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>