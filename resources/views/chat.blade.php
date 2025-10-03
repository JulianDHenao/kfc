<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KFC Chatbot - Modo Depuración</title>
    {{-- Carga Tailwind CSS --}}
    @vite('resources/css/app.css')
</head>
{{-- Usamos h-screen y relative para asegurar que la posición fixed funcione correctamente --}}
<body class="bg-gray-200 antialiased relative h-screen w-screen">
    <div class="p-6 text-center">
        <h1 class="text-3xl font-bold text-red-600">Página de Depuración de Chatbot</h1>
        <p class="text-gray-600">Si el componente se monta correctamente, el botón amarillo y la ventana de chat deberían aparecer en la esquina inferior derecha.</p>
        <p class="text-sm mt-2">Revisa la consola para ver el mensaje: "Chatbot Componente Iniciado (Final)...."</p>
    </div>

    {{-- INICIO: INTEGRACIÓN DEL AGENTE CONVERSACIONAL (Chatbot) --}}
    {{-- Contenedor donde React montará el componente. 
        Recibe $groupedItems de la ruta que definiste en web.php --}}
    <div id="chatbot-root" 
        data-menu-items="{{ json_encode($groupedItems) }}">
    </div>

    {{-- 2. Cargar el script de React compilado --}}
    @vite('resources/js/Chatbot.jsx')
    {{-- FIN: INTEGRACIÓN DEL AGENTE CONVERSACIONAL --}}
</body>
</html>
