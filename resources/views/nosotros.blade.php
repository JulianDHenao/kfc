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
                
            </h1>
            <h2>
                Sobre KFC
            </h2>

            {{-- Contenido del Texto --}}
            <p>
                Nuestro pollo es único, prácticamente nosotros lo inventamos. Si en algún momento hacen un salón de la fama de la comida, nuestro pollo estaría primero.
            </p>
            <h1>Sus Inicios </h1>
            <p>
                
Esta leyenda comenzó en 1930, en una humilde estación de servicio en Corbin, Kentucky. A sus 40 años Harland Sanders o más conocido como el Coronel Sanders comenzó alimentando hambrientos viajeros. Sanders pasó los siguientes nueve años perfeccionando su mezcla de 11 hierbas y especias hasta crear su famosa receta secreta. Como también su técnica de cocción que usamos hasta el día de hoy. En la actualidad existen más de 24.000 restaurantes de KFC alrededor del mundo, con 157 de ellos ubicados en Ecuador. Doy principio, pues, a mi historia como Pablos, el buscón de Segovia: afortunadamente Dios ha querido que en esto sólo nos parezcamos.
            </p>
            <p>
                Yo nací en Cádiz, y en el famoso barrio de la Viña, que no es hoy, ni menos era entonces, academia de buenas costumbres. La memoria no me da luz alguna sobre mi persona y mis acciones en la niñez, sino desde la edad de seis años; y si recuerdo esta fecha, es porque la asocio a un suceso naval de que oí hablar entonces: el combate del cabo de San Vicente, acaecido en 1797.
            </p>
            {{-- Puedes continuar añadiendo más párrafos del texto aquí --}}
        </article>
    </main>
</body>
</html>