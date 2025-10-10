<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KFC - ¡Pide tu pollo frito!</title>

    {{-- Token CSRF para peticiones seguras --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Cargamos CSS y JS de la aplicación --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 antialiased">
    @include('layouts.navigation')

    {{-- INICIO: Contenedor de Notificaciones --}}
    @if (session('success'))
        <div id="notification" class="fixed top-20 right-5 bg-green-500 text-white py-2 px-4 rounded-lg shadow-lg transition-opacity duration-300">
            {{ session('success') }}
        </div>
    @endif
    {{-- FIN: Contenedor de Notificaciones --}}


    {{-- INICIO: Banner Slider con Alpine.js --}}
    <section
        x-data="{
            slides: [
                '{{ asset('images/banner1.png') }}',
                '{{ asset('images/banner2.jpeg') }}',
                '{{ asset('images/banner3.jpg') }}',
                '{{ asset('images/banner4.jpg') }}',
                '{{ asset('images/banner5.jpeg') }}',
                
            ],
            activeSlide: 0,
            loop() {
                setInterval(() => {
                    this.activeSlide = (this.activeSlide + 1) % this.slides.length
                }, 5000)
            }
        }"
        x-init="loop"
        class="relative w-full h-96 md:h-[40rem] overflow-hidden mb-12"
    >
        <!-- Contenedor de las imágenes -->
        <template x-for="(slide, index) in slides" :key="index">
            <div
                x-show="activeSlide === index"
                class="absolute inset-0 transition-opacity duration-1000 ease-in-out"
                x-transition:enter="opacity-0"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="opacity-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                <img :src="slide" class="w-full h-full object-cover" alt="Banner Promocional">
            </div>
        </template>
    </section>
    {{-- FIN: Banner Slider --}}

    <main class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {{-- Título Principal --}}
        <header class="text-center mb-12">
            <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl md:text-6xl">
                <span class="block">Nuestro Delicioso</span>
                <span class="block text-red-600">Menú</span>
            </h1>
            <p class="mt-3 max-w-md mx-auto text-base text-gray-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                ¡Para chuparse los dedos! Descubre nuestras recetas secretas y combos irresistibles.
            </p>
        </header>

        {{-- Bucle para mostrar las categorías y productos --}}
        @foreach ($groupedItems as $category => $items)
            <section class="mb-16">
                <h2 class="text-3xl font-bold border-b-4 border-red-600 pb-2 mb-8">{{ $category }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                    @foreach ($items as $item)
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col">
                            <img src="{{ $item->image_url ? asset($item->image_url) : 'https://via.placeholder.com/400x300.png/f8f8f8/cccccc?text=KFC' }}" alt="{{ $item->name }}" class="w-full h-56 object-cover">
                            <div class="p-6 flex flex-col flex-grow">
                                <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ $item->name }}</h3>
                                <p class="text-gray-600 text-sm mb-4 flex-grow">{{ $item->description }}</p>
                                <div class="flex justify-between items-center mt-auto">
                                    <span class="text-2xl font-bold text-gray-900">${{ number_format($item->price, 2) }}</span>
                                    <form class="add-to-cart-form">
                                        @csrf
                                        <input type="hidden" name="item_id" value="{{ $item->id }}">
                                        <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors">Añadir</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </main>

    {{-- INICIO: INTEGRACIÓN DEL AGENTE CONVERSACIONAL (Chatbot) --}}
    {{-- Contenedor donde React montará el componente.
        Recibe $groupedItems de la ruta que definiste en web.php --}}
    <div id="chatbot-root"
        data-menu-items="{{ json_encode($groupedItems) }}">
    </div>
    {{-- FIN: INTEGRACIÓN DEL AGENTE CONVERSACIONAL --}}

    {{-- INICIO: Script para añadir al carrito y notificaciones --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Lógica para las notificaciones
            const notification = document.getElementById('notification');
            if (notification) {
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 500);
                }, 3000); // La notificación desaparece después de 3 segundos
            }

            // Lógica para los formularios "Añadir al carrito"
            const forms = document.querySelectorAll('.add-to-cart-form');
            forms.forEach(form => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault(); // Evita la recarga de la página

                    const itemId = this.querySelector('input[name="item_id"]').value;
                    const csrfToken = this.querySelector('input[name="_token"]').value;

                    fetch(`/cart/add/${itemId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Disparamos un evento personalizado para que otros scripts (como el del contador) reaccionen
                        window.dispatchEvent(new CustomEvent('cart-updated'));
                        // Aquí podrías mostrar una notificación de éxito más moderna si quisieras
                        console.log(data.success);
                    })
                    .catch(error => console.error('Error:', error));
                });
            });
        });
    </script>
    {{-- FIN: Script --}}
</body>
</html>