<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>KFC Portal - Tu comida rápida favorita</title>

    {{-- ¡CORRECCIÓN CRÍTICA! Añadimos el Token CSRF para que React pueda hacer peticiones POST seguras --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

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

    {{-- INICIO: Banner con carrusel automático --}}
    <header id="banner-carousel" class="relative w-full overflow-hidden shadow-lg" style="height: 50vh; min-height: 350px; max-height: 450px;">
        {{-- Contenedor de las diapositivas (el que se moverá) --}}
        <div id="slider-track" class="flex h-full transition-transform duration-1000 ease-in-out">
            
            {{-- Diapositiva 1 --}}
            <div class="w-full flex-shrink-0 relative">
                <img src="{{ asset('images/banner2.jpeg') }}" alt="Promoción 1" class="w-full h-full object-cover">
                <div class="absolute inset-0  flex flex-col justify-center items-center text-center text-white p-4">
                    <h1 class="text-4xl md:text-6xl font-extrabold"></h1>
                    <p class="mt-2 text-xl"></p>
                </div>
            </div>

            {{-- Diapositiva 2 --}}
            <div class="w-full flex-shrink-0 relative">
                <img src="{{ asset('images/banner1.png') }}" alt="Promoción 2" class="w-full h-full object-cover">
                <div class="absolute inset-0   flex flex-col justify-center items-center text-center text-white p-4">
                    <h1 class="text-4xl md:text-6xl font-extrabold"></h1>
                    <p class="mt-2 text-xl"></p>
                </div>
            </div>

        </div>
    </header>
    {{-- FIN: Banner con carrusel automático --}}

    <main class="container mx-auto px-4 py-12">
        {{-- Iteramos sobre cada categoría y sus items --}}
        @foreach ($groupedItems as $category => $items)
            <div class="mb-12">
                <h2 class="text-3xl font-extrabold text-gray-800 dark:text-white border-l-4 border-red-600 pl-4 mb-8">
                    {{ $category }}
                </h2>
                {{-- Usamos un grid para un look más profesional --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    {{-- Iteramos sobre cada item dentro de la categoría --}}
                    @foreach ($items as $item)
                        {{-- Tarjeta de Producto --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden flex flex-col">
                            <img src="{{ $item->image_url ? asset($item->image_url) : 'https://via.placeholder.com/400x300?text=KFC' }}" alt="{{ $item->name }}" class="w-full h-48 object-cover">
                            <div class="p-6 flex flex-col flex-grow">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $item->name }}</h3>
                                <p class="text-gray-600 dark:text-gray-400 mt-2 flex-grow">{{ $item->description }}</p>
                                <div class="mt-4 flex justify-between items-center">
                                    <p class="text-2xl font-extrabold text-red-600 dark:text-red-500">${{ number_format($item->price, 2) }}</p>
                                    {{-- MEJORA: Formulario con JS para actualización dinámica --}}
                                    <form action="{{ route('cart.add', $item->id) }}" method="POST" onsubmit="addToCart(event, this)">
                                        @csrf 
                                        <button 
                                            type="submit" 
                                            class="bg-red-600 text-white font-bold py-2 px-4 rounded-full hover:bg-red-700 transition duration-300">
                                            Añadir
                                        </button>
                                    </form>
                                </div>
                            </div>
                         </div>
                     @endforeach
                 </div>
            </div>
        @endforeach
    </main>
    
    {{-- INICIO: INTEGRACIÓN DEL AGENTE CONVERSACIONAL (Chatbot) --}}
    {{-- Contenedor donde React montará el componente. --}}
    <div id="chatbot-root" 
        data-menu-items="{{ json_encode($groupedItems) }}">
    </div>
    {{-- FIN: INTEGRACIÓN DEL AGENTE CONVERSACIONAL --}}

    {{-- MEJORA: Script para manejar el envío del formulario sin recargar la página --}}
    <script>
        async function addToCart(event, form) {
            event.preventDefault(); // Previene la recarga de la página

            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest', // Laravel detecta esto como una petición AJAX
                    'Accept': 'application/json',
                }
            });

            if (response.ok) {
                // Dispara el evento para que el contador se actualice
                window.dispatchEvent(new CustomEvent('cart-updated'));
            } else {
                console.error('Error al añadir el producto desde el formulario.');
            }
        }

        // --- Lógica para el carrusel del banner ---
        document.addEventListener('DOMContentLoaded', function() {
            const track = document.getElementById('slider-track');
            if (!track) return;

            const slides = Array.from(track.children);
            const slideCount = slides.length;
            if (slideCount <= 1) return; // No hacer nada si hay 1 o 0 slides

            // Clonamos el primer slide y lo añadimos al final para un bucle infinito
            const firstClone = slides[0].cloneNode(true);
            track.appendChild(firstClone);

            let currentIndex = 0;
            const slideWidth = slides[0].offsetWidth;

            setInterval(() => {
                currentIndex++;
                track.style.transform = `translateX(-${currentIndex * 100}%)`;

                // Si llegamos al clon, reseteamos al inicio sin animación
                if (currentIndex === slideCount) {
                    setTimeout(() => {
                        track.style.transition = 'none'; // Desactivar transición para el salto
                        currentIndex = 0;
                        track.style.transform = `translateX(0)`;
                        // Forzar un reflow para que la siguiente transición funcione
                        track.offsetHeight; 
                        track.style.transition = 'transform 1s ease-in-out'; // Reactivar transición
                    }, 1000); // Debe coincidir con la duración de la transición CSS
                }
            }, 5000); // Cambia de imagen cada 5 segundos
        });

        // --- Lógica para actualizar el contador del carrito ---
        document.addEventListener('DOMContentLoaded', function() {
            const cartCountElement = document.getElementById('cart-count');

            // Función para actualizar el contador
            const updateCartCount = async () => {
                try {
                    const response = await fetch('{{ route('cart.count') }}'); // Nueva ruta para obtener el conteo
                    const data = await response.json();
                    if (cartCountElement) {
                        cartCountElement.textContent = data.count;
                    }
                } catch (error) {
                    console.error('Error al actualizar el contador del carrito:', error);
                }
            };

            // Escuchamos el evento personalizado que disparamos al añadir un item
            window.addEventListener('cart-updated', updateCartCount);
        });
    </script>
</body>

</html>