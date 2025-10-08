import React, { useState, useEffect, useRef } from 'react';
// Importamos ReactDOM de forma simple 
import ReactDOM from 'react-dom/client'; 
import ProductCard from './ProductCard'; // ¡MEJORA! Importamos el nuevo componente

// Configuración del API de Gemini
const GEMINI_API_URL = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-05-20:generateContent";
// 🛑 PASO CRÍTICO: Debes reemplazar el valor "" con tu clave API de Gemini. 
const API_KEY = "AIzaSyAq80iSgJn0_KEXlI2WDgknApownsyBfnk"; 

// --- Componente principal del Chatbot ---
const Chatbot = ({ menuJson, onAddToCart }) => {
    const [isOpen, setIsOpen] = useState(false); 
    const [messages, setMessages] = useState([
        // CAMBIO 1: Mensaje inicial actualizado para usar la nueva estructura de 'parts'
        { role: 'model', parts: [{ type: 'text', content: '¡Hola! Soy el Coronel Sanders. ¿Qué se te antoja ordenar hoy o qué quieres saber sobre nuestro menú?' }] }
    ]);
    const [input, setInput] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    // Contador de reintentos para el backoff exponencial
    const [retryCount, setRetryCount] = useState(0);

    const messagesEndRef = useRef(null);
    const MAX_RETRIES = 5;

    // Scroll automático al final de los mensajes
    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
    };

    useEffect(scrollToBottom, [messages]);

    /**
     * Procesa la respuesta del modelo, buscando acciones de "agregar al carrito".
     * Esta función permite al Agente "activar" la funcionalidad de la tienda.
     * @param {string} responseText - Texto de respuesta crudo de Gemini.
     * @returns {object} Un objeto 'part' para el estado de los mensajes.
     */
    const handleModelAction = (responseText) => {
        // MEJORA 2.0: Hacemos la detección de JSON más robusta.
        // Buscamos un bloque JSON que empiece con `[` y termine con `]`.
        const jsonMatch = responseText.match(/(\[.*\])/s);

        try {
            if (jsonMatch && jsonMatch[1]) {
                const data = JSON.parse(jsonMatch[1]);
                // Si es un array de productos, es una sugerencia.
                if (Array.isArray(data) && data.length > 0 && data[0].id && data[0].name) {
                    // Opcional: Mostrar también el texto que venía antes del JSON.
                    const precedingText = responseText.substring(0, jsonMatch.index).trim();
                    if (precedingText) {
                        setMessages(prev => [...prev, { role: 'model', parts: [{ type: 'text', content: precedingText }] }]);
                    }
                    return { type: 'product_suggestion', products: data };
                }
            }
        } catch (e) {
            console.error("Error al parsear JSON de sugerencia, se mostrará como texto.", e);
        }

        // Si no hay JSON de productos, buscamos la acción de añadir.
        const addCartMatch = responseText.match(/AGREGAR_A_CARRITO:([^|]+)\|(\d+)/);
        if (addCartMatch && onAddToCart) {
            const productName = addCartMatch[1].trim();
            const quantity = parseInt(addCartMatch[2], 10);
            
            onAddToCart(productName, quantity);

            const confirmationText = `¡Claro! He añadido ${quantity} ${productName} a tu carrito.`;
            return { type: 'text', content: confirmationText };
        }

        // Si no es ni sugerencia de producto ni acción, es un mensaje de texto simple.
        return { type: 'text', content: responseText };
    };

    /**
     * Llama al API de Gemini con exponencial backoff para generar una respuesta.
     */
    const callGeminiAPI = async (chatHistory) => {
        try {
            // Transformar el objeto de menú en una cadena de texto legible para la IA
            const menuData = JSON.parse(menuJson || '{}');
            
            // Generar la cadena de menú incluso si menuData no es un objeto perfecto, previniendo errores de .map
            const menuString = Object.entries(menuData).map(([category, items]) => {
                const itemsArray = Array.isArray(items) ? items : []; 
                const itemsList = itemsArray.map(item => `${item.name} ($${item.price})`).join(', ');
                return `Categoría: ${category}\nItems: ${itemsList}`;
            }).join('\n---\n');
            
            // MEJORA: Instrucción del sistema para devolver JSON o una acción.
            const systemInstruction = `Eres el amable y legendario Coronel Sanders, el fundador de KFC. Tu trabajo es ayudar al usuario. Tienes dos modos de respuesta:

1.  **Modo Sugerencia (cuando el usuario pregunta por productos, ej: "qué hamburguesas tienes?"):**
    - Busca en el MENÚ ACTUAL los productos que coincidan.
    - Responde **ÚNICAMENTE** con un JSON array que contenga los objetos de los productos encontrados. No añadas texto adicional.
    - Ejemplo de respuesta si el usuario pregunta por "pollo": [{"id":1,"name":"Pieza de Pollo","price":"2.50",...}, {"id":2,"name":"Tenders","price":"5.00",...}]

2.  **Modo Acción (cuando el usuario pide explícitamente añadir algo, ej: "quiero un Mega Box"):**
    - Responde amablemente y añade **AL FINAL** del mensaje la etiqueta de acción.
    - FORMATO DE ACCIÓN: AGREGAR_A_CARRITO:Nombre_Producto|Cantidad
    - Ejemplo de respuesta si piden "quiero 2 piezas de pollo": "¡Claro! 2 piezas de pollo en camino. AGREGAR_A_CARRITO:Pieza de Pollo|2"

Si no puedes cumplir la petición, simplemente responde con texto normal.
MENÚ ACTUAL:
${menuString}`;

            const payload = {
                contents: chatHistory,
                systemInstruction: {
                    parts: [{ text: systemInstruction }]
                },
            };

            // Implementación de Backoff Exponencial
            const delay = Math.pow(2, retryCount) * 1000 + Math.random() * 500; // 1s, 2s, 4s, ...
            if (retryCount > 0) {
                await new Promise(resolve => setTimeout(resolve, delay));
            }

            const response = await fetch(`${GEMINI_API_URL}?key=${API_KEY}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                if ((response.status === 403 || response.status === 401) && API_KEY === "") {
                    throw new Error("ERROR 403/401: La clave API es inválida o no se está inyectando. Intenta insertar tu propia clave.");
                }

                if (response.status === 429 && retryCount < MAX_RETRIES) {
                    console.log(`Rate limit exceeded. Retrying (${retryCount + 1}/${MAX_RETRIES}) in ${delay / 1000}s...`);
                    setRetryCount(prev => prev + 1);
                    return callGeminiAPI(chatHistory); 
                }
                throw new Error(`API call failed with status: ${response.status}`);
            }

            const result = await response.json();
            const text = result.candidates?.[0]?.content?.parts?.[0]?.text || "Lo siento, no pude generar una respuesta. Intenta de nuevo.";
            setRetryCount(0); // Reiniciar contador de reintentos en éxito
            return text;

        } catch (error) {
            console.error('Error calling Gemini API:', error);
            
            if (error.message.includes("ERROR 403/401")) {
                return "🚨 Fallo de Conexión: La clave API de Gemini no está configurada. Por favor, pégala en el archivo Chatbot.jsx.";
            }

            if (retryCount >= MAX_RETRIES) {
                setRetryCount(0); // Resetear para la siguiente entrada
                return "Hubo un error de conexión persistente con nuestro asistente. Por favor, intenta más tarde.";
            }
            
            // Reintento en caso de otros errores de red
            if (retryCount < MAX_RETRIES) {
                 setRetryCount(prev => prev + 1);
                 return callGeminiAPI(chatHistory);
            }
            return "Ocurrió un error inesperado al procesar la solicitud.";
        }
    };


    /**
     * Procesa el mensaje del usuario y llama al API.
     */
    const handleSend = async (e) => {
        e.preventDefault();
        if (!input.trim() || isLoading) return;

        const newUserMessage = { role: 'user', parts: [{ type: 'text', content: input }] };
        const newMessages = [...messages, newUserMessage];
        setMessages(newMessages);
        setInput('');
        setIsLoading(true);

        // Transformar mensajes a formato del API (solo el texto)
        const chatHistory = newMessages.map(msg => ({
            role: msg.role === 'model' ? 'model' : 'user',
            parts: [{ text: msg.parts.find(p => p.type === 'text')?.content || '' }] // Solo pasamos el texto
        }));

        // 1. Llamar al API de Gemini
        const rawResponseText = await callGeminiAPI(chatHistory);
        
        // 2. Procesar la respuesta para buscar la acción de agregar al carrito
        const finalResponsePart = handleModelAction(rawResponseText);

        // Actualizar la interfaz con la respuesta del modelo
        if (finalResponsePart) {
            setMessages(prev => [...prev, { role: 'model', parts: [finalResponsePart] }]);
        }

        setIsLoading(false);
    };

    // --- Renderizado de la Interfaz ---

    // Estilos Tailwind para un diseño simple y moderno
    const colors = {
        primary: 'bg-red-600',
        secondary: 'bg-gray-200',
        textPrimary: 'text-white',
        textSecondary: 'text-gray-800',
    };

    const ChatBubble = ({ message }) => (
        <div className={`flex ${message.role === 'user' ? 'justify-end' : 'justify-start'}`}>
            <div className={`max-w-xs md:max-w-md lg:max-w-lg my-1 ${message.role === 'user' ? 'px-4 py-2' : ''} rounded-xl shadow-md ${
                message.role === 'user'
                    ? `${colors.primary} ${colors.textPrimary} rounded-br-none`
                    : 'bg-transparent' // El contenedor de la burbuja del bot es transparente
            }`}>
                {message.parts.map((part, i) => {
                    if (part.type === 'product_suggestion') {
                        return (
                            <div key={i} className="w-full">
                                {part.products.map(product => (
                                    <ProductCard key={product.id} product={product} onAddToCart={onAddToCart} />
                                ))}
                            </div>
                        );
                    }
                    // Default to text
                    return <div key={i} className={`inline-block ${message.role === 'model' ? 'bg-gray-200 text-gray-800 rounded-xl rounded-tl-none px-4 py-2' : ''}`}>{part.content}</div>;
                })}
            </div>
        </div>
    );

    // Ícono del Coronel (manteniendo el SVG anterior)
    const ColonelIcon = () => (
        <svg className="w-6 h-6 mr-2 text-white fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <title>Coronel Sanders</title>
            {/* Sombrero - Simple forma redondeada */}
            <path d="M12 2L12 6M10 6L14 6L14 8L10 8L10 6Z" fill="#FFFFFF"/>
            {/* Cabeza - Simple forma de circulo */}
            <circle cx="12" cy="14" r="5" fill="#F8E7C5"/>
            {/* Barba / Pelo blanco - Se asegura que la barba blanca sea visible en el fondo blanco */}
            <path d="M12 9c-2.76 0-5 2.24-5 5v3h10v-3c0-2.76-2.24-5-5-5z" fill="#FFFFFF"/>
            {/* Ojos - puntos simples */}
            <circle cx="10.5" cy="13" r="0.5" fill="#333333"/>
            <circle cx="13.5" cy="13" r="0.5" fill="#333333"/>
            {/* Pajarita - Pequeña forma roja que simula una pajarita de pollo */}
            <path d="M12 18l-1-2h2l-1 2Z" fill="#BB0000"/>
        </svg>
    );

    return (
        <>
            {/* Botón Flotante para ABRIR/CERRAR */}
            <button
                onClick={() => setIsOpen(!isOpen)}
                style={{
                    position: 'fixed',
                    bottom: '24px', 
                    right: '24px', 
                    zIndex: 9999
                }}
                className={`w-14 h-14 rounded-full shadow-2xl 
                ${colors.primary} hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-300 transition duration-300 ease-in-out`} 
            >
                {/* Ícono dinámico: Chat (cerrado) o Minimizar (abierto) */}
                <svg className={`w-8 h-8 mx-auto ${colors.textPrimary}`} fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    {isOpen ? (
                        // Icono de flecha hacia abajo o minimizar cuando está abierto
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                    ) : (
                        // Icono de chat/mensaje cuando está cerrado
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0zM12 18v-6"></path>
                    )}
                </svg>
            </button>

            {/* Ventana del Chatbot */}
            <div
                style={{
                    position: 'fixed',
                    bottom: '96px', // Posición original
                    right: '24px', 
                    zIndex: 9999,
                    width: '360px', 
                    maxWidth: '85vw', 
                    maxHeight: '50vh', 
                }}
                className={`bg-white dark:bg-gray-700 rounded-xl shadow-2xl flex flex-col transition-all duration-300 transform 
                ${isOpen ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0 pointer-events-none'}`}
            >
                {/* Encabezado con Ícono y Botón de Cierre */}
                <div className={`p-4 rounded-t-xl ${colors.primary} flex items-center justify-between shadow-md`}>
                    
                    {/* Contenedor del Título y el Coronel */}
                    <div className="flex items-center">
                        <ColonelIcon />
                        {/* CAMBIO 3: Título visible actualizado */}
                        <h3 className={`font-bold text-lg ${colors.textPrimary}`}>Coronel Sanders</h3>
                    </div>

                    {/* Botón de Cierre/Minimizar (Usando el Icono de X simple) */}
                    <button 
                        onClick={() => setIsOpen(false)}
                        className={`p-1 rounded-full hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300 transition duration-150`}
                        title="Cerrar Chat"
                    >
                        {/* Icono de X simple para cerrar */}
                        <svg className={`w-6 h-6 ${colors.textPrimary}`} fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {/* Cuerpo del Mensaje (flex-1 y overflow-y-auto aseguran el scroll dentro del límite de altura) */}
                <div className="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                    {messages.map((msg, index) => (
                        <ChatBubble key={index} message={msg} />
                    ))}
                    {isLoading && (
                        <div className="flex justify-start">
                            <div className={`px-4 py-2 my-1 rounded-xl shadow-md ${colors.secondary} ${colors.textSecondary} rounded-tl-none`}>
                                <div className="animate-pulse">...escribiendo</div>
                            </div>
                        </div>
                    )}
                    <div ref={messagesEndRef} />
                </div>

                {/* Pie de Página / Entrada de Texto */}
                <form onSubmit={handleSend} className="p-4 border-t border-gray-200 dark:border-gray-600">
                    <div className="flex">
                        <input
                            type="text"
                            value={input}
                            onChange={(e) => setInput(e.target.value)}
                            placeholder="Escribe tu mensaje..."
                            className="flex-1 p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            disabled={isLoading}
                        />
                        <button
                            type="submit"
                            className={`px-4 rounded-r-lg font-bold text-white transition duration-300 ${colors.primary} hover:bg-red-700 disabled:opacity-60`}
                            disabled={isLoading}
                        >
                            Enviar
                        </button>
                    </div>
                </form>
            </div>
        </>
    );
};

// --- Montar el Componente ---
const menuElement = document.getElementById('chatbot-root');

if (menuElement) {
    try {
        const menuJson = menuElement.getAttribute('data-menu-items');
        
        // Montar el componente React
        const root = ReactDOM.createRoot(menuElement);
        
        // MEJORA: Implementamos la lógica real para añadir al carrito desde el chatbot.
        const handleAddToCart = async (productIdentifier, quantity) => {
            console.log(`Intentando añadir: ${productIdentifier}, Cantidad: ${quantity}`);
            const menuData = JSON.parse(menuJson || '{}');
            let itemToAdd = null;

            // Buscamos el item por ID o por nombre
            for (const category in menuData) {
                const found = menuData[category].find(item => 
                    item.id === productIdentifier || item.name.toLowerCase() === String(productIdentifier).toLowerCase()
                );
                if (found) {
                    itemToAdd = found;
                    break;
                }
            }

            if (!itemToAdd) {
                console.error(`Producto "${productIdentifier}" no encontrado en el menú.`);
                // Opcional: podrías mostrar un mensaje de error en el chat.
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            await fetch(`/cart/add/${itemToAdd.id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });
            // Disparamos el evento para que el contador del carrito se actualice.
            window.dispatchEvent(new CustomEvent('cart-updated'));
        };

        root.render(<Chatbot 
            menuJson={menuJson} 
            onAddToCart={handleAddToCart} 
        />);
        
        console.log("Chatbot de React montado exitosamente con UI actualizada.");
    } catch (error) {
        console.error("Error al montar el componente Chatbot:", error);
    }
} else {
    console.warn("Elemento 'chatbot-root' NO encontrado. Asegúrate de que esté en tu archivo Blade.");
}

export default Chatbot;

