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
     * @returns {Array<object>} Un array de 'parts' para el estado de los mensajes.
     */
    const processModelResponse = (responseText) => {
        // MEJORA 4.0: Detección de JSON aún más robusta.
        // Busca un bloque JSON, opcionalmente envuelto en ```json ... ```
        // Esto soluciona el problema de que la IA devuelva el JSON como un bloque de código.
        const jsonMatch = responseText.match(/```json\s*(\[.*\])\s*```|(\[.*\])/s);

        try {
            const jsonString = jsonMatch ? (jsonMatch[1] || jsonMatch[2]) : null;
            if (jsonString) {
                const data = JSON.parse(jsonString);
                // Si es un array de productos, es una sugerencia.
                if (Array.isArray(data) && data.length > 0 && data[0].id && data[0].name) {
                    const precedingText = responseText.substring(0, jsonMatch.index).trim();
                    const parts = [];
                    if (precedingText) {
                        parts.push({ type: 'text', content: precedingText });
                    }
                    parts.push({ type: 'product_suggestion', products: data });
                    return parts;
                }
            }
        } catch (e) {
            console.error("Error al parsear JSON de sugerencia, se mostrará como texto.", e);
        }

        const addCartMatch = responseText.match(/AGREGAR_A_CARRITO:([^|]+)\|(\d+)/);
        if (addCartMatch && onAddToCart) {
            const productName = addCartMatch[1].trim();
            const quantity = parseInt(addCartMatch[2], 10);
            
            onAddToCart(productName, quantity); // Llama a la función para añadir al carrito

            const confirmationText = `¡Claro! He añadido ${quantity} ${productName} a tu carrito.`;
            return [{ type: 'text', content: confirmationText }];
        }

        return [{ type: 'text', content: responseText }];
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
                // MEJORA 4.0: Pasamos el objeto completo a la IA para que tenga todos los datos.
                // Usamos JSON.stringify para asegurar que la IA reciba un formato que pueda replicar.
                // Esto soluciona el problema de las tarjetas sin imagen o sin ID.
                const itemsList = itemsArray.map(item => JSON.stringify(item)).join(', ');
                return `Categoría: ${category}\nItems: ${itemsList}`;
            }).join('\n---\n');
            
            // MEJORA: Instrucción del sistema para devolver JSON o una acción.
            const systemInstruction = `Eres el amable y legendario Coronel Sanders, el fundador de KFC. Tu trabajo es ayudar al usuario. Tienes dos modos de respuesta:

1.  **Modo Sugerencia (cuando el usuario pregunta por productos, ej: "qué hamburguesas tienes?"):**
    - Busca en el MENÚ ACTUAL los productos que coincidan.
    - Responde **ÚNICAMENTE** con un JSON array que contenga los objetos de los productos encontrados. No añadas texto adicional.
    - El JSON debe ser idéntico al del MENÚ ACTUAL, incluyendo id, name, price, image_url, etc.

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

        const userMessageContent = input;
        setInput('');
        setIsLoading(true);

        // Usamos la forma funcional de setState para evitar condiciones de carrera.
        setMessages(prevMessages => {
            const newUserMessage = { role: 'user', parts: [{ type: 'text', content: userMessageContent }] };
            const updatedMessages = [...prevMessages, newUserMessage];

            // Transformar el historial actualizado a formato del API
            const chatHistory = updatedMessages.map(msg => ({
                role: msg.role === 'model' ? 'model' : 'user',
                parts: [{ text: msg.parts.find(p => p.type === 'text')?.content || '' }]
            }));

            // Llamar a la API dentro de esta función de actualización de estado
            callGeminiAPI(chatHistory).then(rawResponseText => {
                const responseParts = processModelResponse(rawResponseText);
                if (responseParts && responseParts.length > 0) {
                    const newModelMessages = responseParts.map(part => ({ role: 'model', parts: [part] }));
                    setMessages(prev => [...prev, ...newModelMessages]);
                }
                setIsLoading(false);
            });

            return updatedMessages; // Devolvemos el estado actualizado con el mensaje del usuario
        });
    };

    // --- Renderizado de la Interfaz ---

    // Componente para el avatar, ahora usa una imagen real
    const ColonelAvatar = ({ className }) => (
        <img src="/images/colonel-avatar.png" alt="Coronel Sanders" className={className} />
    );

    const ChatBubble = ({ message }) => {
        const isUser = message.role === 'user';
        const bubbleClass = isUser ? 'chat-bubble user' : 'chat-bubble model';

        return (
            <div className={`chat-bubble-wrapper ${isUser ? 'user' : 'model'}`}>
                {!isUser && <div className="avatar"><ColonelAvatar className="colonel-avatar-icon" /></div>}
                <div className={bubbleClass}>
                    {message.parts.map((part, i) => {
                        if (part.type === 'product_suggestion') {
                            return (
                                <div key={i} className="product-suggestion-container">
                                    {part.products.map(product => (
                                        <ProductCard key={product.id} product={product} onAddToCart={onAddToCart} />
                                    ))}
                                </div>
                            );
                        }
                        return <div key={i}>{part.content}</div>;
                    })}
                </div>
            </div>
        );
    };

    const TypingIndicator = () => (
        <div className="chat-bubble-wrapper model">
            <div className="avatar"><ColonelAvatar className="colonel-avatar-icon" /></div>
            <div className="chat-bubble model typing-indicator">
                <span className="typing-dot" style={{ animationDelay: '0s' }}></span>
                <span className="typing-dot" style={{ animationDelay: '0.2s' }}></span>
                <span className="typing-dot" style={{ animationDelay: '0.4s' }}></span>
            </div>
        </div>
    );

    return (
        <>
            {/* Botón Flotante para ABRIR/CERRAR */}
            <button onClick={() => setIsOpen(!isOpen)} id="chatbot-toggle" title="Hablar con el Coronel">
                {isOpen ? (
                    // Icono de X cuando el chat está abierto
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style={{width: '32px', height: '32px', color: 'white'}}>
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                ) : (
                    // Imagen del Coronel cuando el chat está cerrado
                    <img src="/images/colonel-fab.png" alt="Abrir chat" />
                )}
            </button>

            {/* Ventana del Chatbot */}
            <div id="chatbot-window" className={isOpen ? 'open' : ''}>
                {/* Encabezado con Ícono y Botón de Cierre */}
                <div id="chatbot-header">
                    <div className="header-content">
                        <ColonelAvatar className="colonel-header-icon" />
                        <h3>Coronel Sanders</h3>
                    </div>
                    <button onClick={() => setIsOpen(false)} title="Cerrar Chat" className="close-button">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                {/* Cuerpo del Mensaje (flex-1 y overflow-y-auto aseguran el scroll dentro del límite de altura) */}
                <div id="chatbot-messages">
                    {messages.map((msg, index) => (
                        <ChatBubble key={index} message={msg} />
                    ))}
                    {isLoading && <TypingIndicator />}
                    <div ref={messagesEndRef} />
                </div>

                {/* Pie de Página / Entrada de Texto */}
                <form onSubmit={handleSend} id="chatbot-form">
                    <input
                        type="text"
                        value={input}
                        onChange={(e) => setInput(e.target.value)}
                        placeholder="Escribe tu mensaje..."
                        disabled={isLoading}
                    />
                    <button type="submit" disabled={isLoading}>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path></svg>
                    </button>
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
