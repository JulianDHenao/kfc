import React, { useState, useEffect, useRef } from 'react';
// Importamos ReactDOM de forma simple 
import ReactDOM from 'react-dom/client'; 

// Configuración del API de Gemini
const GEMINI_API_URL = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-05-20:generateContent";
// 🛑 PASO CRÍTICO: Debes reemplazar el valor "" con tu clave API de Gemini. 
// Ejemplo: const API_KEY = "AIzaSy...";
const API_KEY = "AIzaSyAq80iSgJn0_KEXlI2WDgknApownsyBfnk"; 

// --- Componente principal del Chatbot ---
const Chatbot = ({ menuJson }) => {
    const [isOpen, setIsOpen] = useState(false); 
    const [messages, setMessages] = useState([
        { role: 'model', text: '¡Hola! Soy tu Agente KFC. ¿Qué se te antoja ordenar hoy o qué quieres saber sobre nuestro menú?' }
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
     * Llama al API de Gemini con exponencial backoff para generar una respuesta.
     */
    const callGeminiAPI = async (chatHistory) => {
        try {
            // Transformar el objeto de menú en una cadena de texto legible para la IA
            const menuData = JSON.parse(menuJson || '{}');
            
            const menuString = Object.entries(menuData).map(([category, items]) => {
                const itemsList = items.map(item => `${item.name} ($${item.price})`).join(', ');
                return `Categoría: ${category}\nItems: ${itemsList}`;
            }).join('\n---\n');

            const systemInstruction = `Eres un amable y entusiasta agente de servicio al cliente para KFC. Tu trabajo es ayudar al usuario a ordenar comida, sugerir productos basados en el menú, y responder preguntas. SOLO sugiere productos que se encuentren en la información de menú proporcionada. Sé conciso y amigable.
            
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
            await new Promise(resolve => setTimeout(resolve, delay));

            const response = await fetch(`${GEMINI_API_URL}?key=${API_KEY}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                // Manejo de errores de autenticación o límites de cuota
                if ((response.status === 403 || response.status === 401) && API_KEY === "") {
                    // Mensaje de error si falla la inyección de clave y no hay una manual
                    throw new Error("ERROR 403/401: La clave API es inválida o no se está inyectando. Intenta insertar tu propia clave.");
                }

                if (response.status === 429 && retryCount < MAX_RETRIES) {
                    // Reintento por límite de tasa
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
                // Si llegamos aquí, el error es porque la clave no está
                return "🚨 Fallo de Conexión: La clave API de Gemini no está configurada. Por favor, pégala en el archivo Chatbot.jsx.";
            }

            // Si falla después de todos los reintentos
            if (retryCount >= MAX_RETRIES) {
                setRetryCount(0); // Resetear para la siguiente entrada
                return "Hubo un error de conexión persistente con nuestro asistente. Por favor, intenta más tarde.";
            }
            
            // Si ocurre un error desconocido antes de alcanzar MAX_RETRIES
            if (retryCount < MAX_RETRIES) {
                 setRetryCount(prev => prev + 1);
                 // Intentar de nuevo, el delay ya se ha calculado en la función
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

        const newUserMessage = { role: 'user', text: input };
        const newMessages = [...messages, newUserMessage];
        setMessages(newMessages);
        setInput('');
        setIsLoading(true);

        // Transformar mensajes a formato del API
        const chatHistory = newMessages.map(msg => ({
            role: msg.role === 'model' ? 'model' : 'user',
            parts: [{ text: msg.text }]
        }));

        // Llamar al API de Gemini
        const responseText = await callGeminiAPI(chatHistory);

        // Actualizar la interfaz con la respuesta del modelo
        if (responseText) {
            setMessages(prev => [...prev, { role: 'model', text: responseText }]);
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
            <div className={`max-w-xs md:max-w-md lg:max-w-lg px-4 py-2 my-1 rounded-xl shadow-md ${
                message.role === 'user'
                    ? `${colors.primary} ${colors.textPrimary} rounded-br-none`
                    : `${colors.secondary} ${colors.textSecondary} rounded-tl-none`
            }`}>
                {message.text}
            </div>
        </div>
    );

    return (
        <>
            {/* Botón Flotante */}
            <button
                onClick={() => setIsOpen(!isOpen)}
                style={{
                    position: 'fixed',
                    bottom: '24px', 
                    right: '24px', 
                    zIndex: 9999
                }}
                className={`w-14 h-14 rounded-full shadow-2xl 
                ${colors.primary} hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-300`} 
            >
                <svg className={`w-8 h-8 mx-auto ${colors.textPrimary}`} fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    {isOpen ? (
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
                    ) : (
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 10h.01M12 10h.01M16 10h.01M12 21.02c-5.523 0-10-4.477-10-10S6.477 1.02 12 1.02s10 4.477 10 10-4.477 10-10 10zM12 21.02V1.02m0 0h.01M21.02 12H1.02m0 0v.01"></path>
                    )}
                </svg>
            </button>

            {/* Ventana del Chatbot */}
            <div
                style={{
                    position: 'fixed',
                    bottom: '96px', 
                    right: '24px', 
                    zIndex: 9999
                }}
                className={`w-80 md:w-96 h-[70vh] max-h-[500px] bg-white dark:bg-gray-700 rounded-xl shadow-2xl flex flex-col transition-all duration-300 transform 
                ${isOpen ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0 pointer-events-none'} 
                
                /* 🛑 CLASES RESPONSIVE AÑADIDAS/MODIFICADAS */
                w-full max-w-sm md:w-96 max-h-[calc(100vh-120px)]`} 
                /* NOTA: `w-full max-w-sm` asegura que en pantallas pequeñas, no exceda 100vw y tenga un límite amigable. */
            >
                {/* Encabezado */}
                <div className={`p-4 rounded-t-xl ${colors.primary} flex items-center justify-between shadow-md`}>
                    <h3 className={`font-bold text-lg ${colors.textPrimary}`}>Agente KFC</h3>
                    <span className={`text-sm font-medium ${colors.textPrimary}`}>En línea</span>
                </div>

                {/* Cuerpo del Mensaje */}
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
        root.render(<Chatbot menuJson={menuJson} />);
        
        console.log("Chatbot de React montado exitosamente.");
    } catch (error) {
        console.error("Error al montar el componente Chatbot:", error);
    }
} else {
    console.warn("Elemento 'chatbot-root' NO encontrado. Asegúrate de que esté en tu archivo Blade.");
}

export default Chatbot;
