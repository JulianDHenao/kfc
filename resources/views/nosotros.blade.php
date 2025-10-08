<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sobre Nosotros - KFC</title>

    {{-- Token CSRF para peticiones seguras --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Cargamos CSS y JS de la aplicación --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 antialiased">
    @include('layouts.navigation')

    <main class="container mx-auto px-4 py-12">
        <article class="prose lg:prose-xl mx-auto bg-white p-8 rounded-lg shadow-md">
            {{-- Título Principal --}}
            <h1>
                Trafalgar
            </h1>
            <h2>
                Por Benito Pérez Galdós
            </h2>

            {{-- Contenido del Texto --}}
            <p>
                Se me permitirá que antes de referir el gran suceso de que fui testigo, diga algunas palabras sobre mi infancia, explicando por qué extraña manera me llevaron los azares de la vida a presenciar la terrible catástrofe de nuestra marina.
            </p>
            <p>
                Al hablar de mi nacimiento, no imitaré a la mayor parte de los que cuentan hechos de su propia vida, quienes empiezan nombrando su parentela, las más veces noble, siempre hidalga por lo menos, si no se dicen descendientes del mismo Emperador de Trapisonda. Yo, en esta parte, no puedo adornar mi libro con sonoros apellidos; y fuera de mi madre, a quien conocí por poco tiempo, no tengo noticia de ninguno de mis ascendientes, si no es de Adán, cuyo parentesco me parece indiscutible. Doy principio, pues, a mi historia como Pablos, el buscón de Segovia: afortunadamente Dios ha querido que en esto sólo nos parezcamos.
            </p>
            <p>
                Yo nací en Cádiz, y en el famoso barrio de la Viña, que no es hoy, ni menos era entonces, academia de buenas costumbres. La memoria no me da luz alguna sobre mi persona y mis acciones en la niñez, sino desde la edad de seis años; y si recuerdo esta fecha, es porque la asocio a un suceso naval de que oí hablar entonces: el combate del cabo de San Vicente, acaecido en 1797.
            </p>
            {{-- Puedes continuar añadiendo más párrafos del texto aquí --}}
        </article>
    </main>
</body>
</html>