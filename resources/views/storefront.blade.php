<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>KFC Portal - Tu comida rápida favorita</title>
        
        {{-- ¡CORRECCIÓN CLAVE! Cargamos CSS y JS de la aplicación en una sola directiva --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100 antialiased relative">
        @include('layouts.navigation')

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mx-auto max-w-7xl mt-4" role="alert">
                <strong class="font-bold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mx-auto max-w-7xl mt-4" role="alert">
                <strong class="font-bold">¡Error!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <header class="bg-red-600 py-16 text-center shadow-lg">
            <h1 class="text-6xl font-extrabold text-white">El Sabor de KFC en un Clic</h1>
            <p class="mt-2 text-xl text-white">¡Pide ahora y disfruta!</p>
        </header>

        <main class="container mx-auto px-4 py-12">
            @foreach ($groupedItems as $category => $menuItems)
                <h2 class="text-3xl font-bold mb-6 text-gray-800 border-b-2 border-red-500 pb-2">{{ $category }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-10">
                    @foreach ($menuItems as $item)
                        <div class="bg-white rounded-lg shadow-xl overflow-hidden transform transition duration-300 hover:scale-[1.03]">
                            <img src="{{ $item->image_url ?? 'https://via.placeholder.com/400x300?text=KFC' }}" alt="{{ $item->name }}" class="w-full h-48 object-cover">
                            <div class="p-4">
                                <h3 class="font-bold text-xl mb-2 text-gray-900">{{ $item->name }}</h3>
                                <p class="text-gray-600 text-sm mb-3 h-12 overflow-hidden">{{ $item->description }}</p>
                                <div class="flex justify-between items-center">
                                    <span class="text-2xl font-extrabold text-red-600">${{ number_format($item->price, 2) }}</span>
                                    
                                    <form action="{{ route('cart.add', $item->id) }}" method="POST">
                                        @csrf 
                                        <button 
                                            type="submit" 
                                            class="bg-green-500 text-white px-4 py-2 rounded-full font-semibold hover:bg-green-600 transition">
                                            Añadir
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </main>
        
        {{-- INICIO: INTEGRACIÓN DEL AGENTE CONVERSACIONAL (Chatbot) --}}
        {{-- Contenedor donde React montará el componente. --}}
        <div id="chatbot-root" 
            data-menu-items="{{ json_encode($groupedItems) }}">
        </div>
        {{-- FIN: INTEGRACIÓN DEL AGENTE CONVERSACIONAL --}}

    </body>
</html>
