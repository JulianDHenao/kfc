import './bootstrap';

import Alpine from 'alpinejs';
import './Chatbot.jsx';

window.Alpine = Alpine;

Alpine.start();

// INICIO: Lógica para actualizar el contador del carrito
function updateCartCount() {
    fetch('/cart/count')
        .then(response => response.json())
        .then(data => {
            const cartCountElement = document.getElementById('cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = data.count;
            }
        })
        .catch(error => console.error('Error fetching cart count:', error));
}

// Escuchar el evento personalizado para actualizar el contador
window.addEventListener('cart-updated', updateCartCount);
// FIN: Lógica para actualizar el contador del carrito
