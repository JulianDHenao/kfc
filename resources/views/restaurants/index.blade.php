<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nuestros Restaurantes - KFC</title>

    {{-- Token CSRF para peticiones seguras --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Cargamos CSS y JS de la aplicación --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- INICIO: Estilos y Scripts para el Mapa (Leaflet.js) --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>
    {{-- FIN: Estilos y Scripts para el Mapa --}}

    <style>
        /* Damos una altura fija a los contenedores del mapa */
        .map-container {
            height: 300px;
        }
    </style>
</head>
<body class="bg-gray-100 antialiased">
    @include('layouts.navigation')

    <main class="container mx-auto px-4 py-12">
        <header class="text-center mb-12">
            <h1 class="text-4xl font-extrabold text-gray-800 dark:text-white">Nuestros Restaurantes</h1>
            <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">Encuentra tu KFC más cercano.</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse ($restaurants as $restaurant)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    {{-- Contenedor del mapa --}}
                    <div id="map-{{ $restaurant->id }}" class="map-container w-full"></div>
                    
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $restaurant->name }}</h3>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">{{ $restaurant->address }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ $restaurant->city }}, {{ $restaurant->state }}</p>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 text-xl">No hay restaurantes para mostrar en este momento.</p>
                </div>
            @endforelse
        </div>
    </main>

    {{-- Script para inicializar los mapas --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Convertimos los datos de PHP a un objeto JavaScript
            const restaurants = @json($restaurants);

            restaurants.forEach(restaurant => {
                // Verificamos que tengamos latitud y longitud
                if (restaurant.latitude && restaurant.longitude) {
                    // Inicializamos el mapa en el div correspondiente
                    const map = L.map(`map-${restaurant.id}`).setView([restaurant.latitude, restaurant.longitude], 15);

                    // Añadimos la capa de tiles de OpenStreetMap (alternativa gratuita a Google Maps)
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

                    // Añadimos un marcador en la ubicación del restaurante
                    L.marker([restaurant.latitude, restaurant.longitude]).addTo(map)
                        .bindPopup(`<b>${restaurant.name}</b><br>${restaurant.address}`).openPopup();
                }
            });
        });
    </script>
</body>
</html>