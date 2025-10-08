import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';

const CartCounter = ({ initialCount }) => {
    const [count, setCount] = useState(initialCount);

    const updateCount = async () => {
        try {
            const response = await fetch('/api/cart/count');
            const data = await response.json();
            setCount(data.count);
        } catch (error) {
            console.error('Error fetching cart count:', error);
        }
    };

    useEffect(() => {
        // Escucha el evento personalizado 'cart-updated'
        window.addEventListener('cart-updated', updateCount);

        // Limpia el listener cuando el componente se desmonta
        return () => {
            window.removeEventListener('cart-updated', updateCount);
        };
    }, []);

    if (count === 0) return null;

    return (
        <span className="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
            {count}
        </span>
    );
};

const counterElement = document.getElementById('cart-counter-root');
if (counterElement) {
    const initialCount = parseInt(counterElement.getAttribute('data-initial-count'), 10) || 0;
    const root = ReactDOM.createRoot(counterElement);
    root.render(<CartCounter initialCount={initialCount} />);
}