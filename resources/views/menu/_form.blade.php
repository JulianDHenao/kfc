{{-- Manejo de errores de validación --}}
@if ($errors->any())
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Campo Nombre --}}
<div class="mb-4">
    <label for="name" class="block text-sm font-medium text-gray-700">Nombre del Producto</label>
    <input type="text" name="name" id="name" value="{{ old('name', $item->name) }}" required
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
</div>

{{-- Campo Descripción --}}
<div class="mb-4">
    <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
    <textarea name="description" id="description" rows="3" required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $item->description) }}</textarea>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Campo Precio --}}
    <div class="mb-4">
        <label for="price" class="block text-sm font-medium text-gray-700">Precio</label>
        <div class="relative mt-1 rounded-md shadow-sm">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <span class="text-gray-500 sm:text-sm">$</span>
            </div>
            <input type="number" name="price" id="price" value="{{ old('price', $item->price) }}" required step="0.01" min="0"
                   class="block w-full rounded-md border-gray-300 pl-7 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="0.00">
        </div>
    </div>

    {{-- Campo Categoría --}}
    <div class="mb-4">
        <label for="category" class="block text-sm font-medium text-gray-700">Categoría</label>
        <input type="text" name="category" id="category" value="{{ old('category', $item->category) }}" required
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Ej: Hamburguesas, Pollo, Bebidas">
    </div>
</div>

{{-- Campo URL de Imagen --}}
<div class="mb-6">
    <label for="image_url" class="block text-sm font-medium text-gray-700">URL de la Imagen (Opcional)</label>
    <input type="text" name="image_url" id="image_url" value="{{ old('image_url', $item->image_url) }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="https://ejemplo.com/imagen.jpg">
</div>

{{-- Botones de Acción --}}
<div class="flex items-center justify-end">
    <a href="{{ route('menu.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
        Cancelar
    </a>
    <button type="submit" class="px-4 py-2 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
        {{ $buttonText }}
    </button>
</div>