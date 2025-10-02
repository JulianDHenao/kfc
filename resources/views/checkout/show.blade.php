<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Finalizar Pedido (Checkout)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @php $cart = session('cart', []); $total = 0; @endphp
            
            @if(empty($cart))
                {{-- Redirigir si el carrito está vacío --}}
                <script>window.location.href = "{{ route('cart.index') }}";</script>
            @endif

            <div class="bg-white shadow-xl sm:rounded-lg p-8 border-t-4 border-red-500">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">Detalles de Entrega y Pago</h3>

                <form method="POST" action="{{ route('checkout.process') }}">
                    @csrf
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        
                        {{-- Dirección de Entrega --}}
                        <div>
                            <label for="delivery_address" class="block text-sm font-medium text-gray-700">Dirección de Entrega</label>
                            <input type="text" name="delivery_address" id="delivery_address" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" 
                                value="{{ old('delivery_address') }}" required autofocus>
                            @error('delivery_address') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Número de Teléfono --}}
                        <div>
                            <label for="phone_number" class="block text-sm font-medium text-gray-700">Teléfono</label>
                            <input type="text" name="phone_number" id="phone_number" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" 
                                value="{{ old('phone_number') }}" required>
                            @error('phone_number') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <h4 class="text-xl font-semibold text-gray-800 mt-8 mb-4 border-t pt-4">Resumen del Pedido</h4>

                    {{-- Listado de Items y Cálculo del Total --}}
                    <div class="space-y-2 mb-6">
                        @foreach ($cart as $id => $details)
                            @php $subtotal = $details['price'] * $details['quantity']; @endphp
                            @php $total += $subtotal; @endphp
                            <div class="flex justify-between text-gray-600 text-sm">
                                <span>{{ $details['quantity'] }} x {{ $details['name'] }}</span>
                                <span>${{ number_format($subtotal, 2) }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Total Final --}}
                    <div class="flex justify-between items-center pt-4 border-t-2 border-red-500">
                        <span class="text-2xl font-bold text-gray-900">Total a Pagar:</span>
                        <span class="text-3xl font-extrabold text-red-700">${{ number_format($total, 2) }}</span>
                    </div>

                    <p class="text-sm text-gray-500 mt-4">Al hacer clic en "Confirmar Pedido", se procesará tu compra. El pago se realiza contra entrega.</p>
                    
                    {{-- Botón de Confirmar --}}
                    <div class="flex justify-end mt-6">
                        <button type="submit" 
                            class="bg-green-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700 transition shadow-lg">
                            Confirmar Pedido y Generar Factura
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>