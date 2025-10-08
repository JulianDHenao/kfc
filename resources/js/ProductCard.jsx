import React from 'react';

const ProductCard = ({ product, onAddToCart }) => {
    const handleAddClick = () => {
        // MEJORA: Llamamos a onAddToCart con el ID del producto para una integración más robusta.
        onAddToCart(product.id, 1);
    };

    return (
        <div className="bg-white border border-gray-200 rounded-lg shadow-md p-3 my-2 flex items-center space-x-3">
            <img src={product.image_url || 'https://via.placeholder.com/100'} alt={product.name} className="w-16 h-16 object-cover rounded" />
            <div className="flex-1">
                <h4 className="font-bold text-sm text-gray-800">{product.name}</h4>
                <p className="text-lg font-extrabold text-red-600">${parseFloat(product.price).toFixed(2)}</p>
            </div>
            <button
                onClick={handleAddClick}
                className="bg-green-500 text-white px-3 py-1 rounded-full font-semibold hover:bg-green-600 transition text-sm"
                aria-label={`Añadir ${product.name} al carrito`}
            >
                Añadir
            </button>
        </div>
    );
};

export default ProductCard;