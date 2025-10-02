<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Confirmación de Pedido y Factura') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Mensaje de Éxito del Checkout --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <strong class="font-bold">¡Felicidades!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            
            <div class="bg-white shadow-xl sm:rounded-lg p-8 border-t-8 border-red-600">
                <h3 class="text-3xl font-extrabold text-red-600 mb-6">Pedido #{{ $order->id }}</h3>
                <p class="text-gray-600 mb-4">¡Gracias por tu compra! Tu pedido está en estado **{{ strtoupper($order->status) }}**.</p>
                
                
                {{-- Detalles de la Orden --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-b pb-4 mb-6">
                    <div>
                        <p class="font-semibold text-gray-800">Cliente:</p>
                        <p class="text-gray-600">{{ $order->user->name }} ({{ $order->user->email }})</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Fecha del Pedido:</p>
                        <p class="text-gray-600">{{ $order->created_at->format('d/M/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Dirección de Entrega:</p>
                        <p class="text-gray-600">{{ $order->delivery_address }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Teléfono:</p>
                        <p class="text-gray-600">{{ $order->phone_number }}</p>
                    </div>
                </div>

                <h4 class="text-xl font-semibold text-gray-800 mb-4">Productos Ordenados</h4>

                {{-- Listado de Items --}}
                <div class="border rounded-lg overflow-hidden mb-6">
                    @foreach ($order->items as $item)
                        <div class="flex justify-between items-center p-4 @if(!$loop->last) border-b @endif">
                            <span class="text-gray-800">{{ $item->quantity }} x {{ $item->name }}</span>
                            <span class="font-semibold text-gray-800">${{ number_format($item->price * $item->quantity, 2) }}</span>
                        </div>
                    @endforeach
                </div>
                
                {{-- Total Final --}}
                <div class="flex justify-between items-center pt-4 border-t-2 border-red-600">
                    <span class="text-2xl font-bold text-gray-900">Total Pagado:</span>
                    <span class="text-3xl font-extrabold text-red-700">${{ number_format($order->total_amount, 2) }}</span>
                </div>

                {{-- Pie de página y contacto --}}
                <div class="mt-8 pt-4 border-t text-center text-gray-500">
                    <p>Su pedido será procesado en breve. Contáctenos para cualquier duda.</p>
                    <a href="{{ url('/') }}" class="mt-4 inline-block text-blue-500 hover:text-blue-700 font-semibold">
                        Volver al Menú Principal
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>