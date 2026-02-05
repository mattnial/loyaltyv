<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Premios - VilcanetAdmin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-gray-100 font-sans">

    @include('admin.partials.nav')

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">🎁 Catálogo de Premios</h2>
                <p class="text-sm text-gray-500 mt-1">Sube premios, marca los destacados y notifica a tus clientes.</p>
            </div>
            <button onclick="document.getElementById('modal-create').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg shadow-lg transition transform hover:scale-105 flex items-center">
                <i class="fas fa-plus mr-2"></i> Nuevo Premio
            </button>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow">
                <p class="font-bold">¡Éxito!</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($rewards as $reward)
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 group hover:shadow-xl transition relative">
                
                @if($reward->is_featured)
                <div class="absolute top-2 right-2 bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-1 rounded-full shadow z-10">
                    <i class="fas fa-star"></i> DESTACADO
                </div>
                @endif

                <div class="h-48 bg-gray-100 relative">
                    @if($reward->image_path)
                        <img src="{{ asset('storage/' . $reward->image_path) }}" class="w-full h-full object-cover">
                    @else
                        <div class="flex items-center justify-center h-full text-gray-300">
                            <i class="fas fa-image text-4xl"></i>
                        </div>
                    @endif
                </div>

                <div class="p-4">
                    <h3 class="font-bold text-gray-800 text-lg mb-1">{{ $reward->name }}</h3>
                    <div class="flex justify-between items-center text-sm text-gray-600 mb-3">
                        <span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded font-bold">{{ $reward->points_cost }} Pts</span>
                        <span>Stock: {{ $reward->stock }}</span>
                    </div>
                    
                    <div class="flex justify-between border-t pt-3">
                        <form action="{{ route('admin.rewards.toggle', $reward->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs font-bold {{ $reward->is_featured ? 'text-yellow-600' : 'text-gray-400 hover:text-yellow-500' }}">
                                <i class="fas fa-star"></i> {{ $reward->is_featured ? 'Quitar Destacado' : 'Destacar' }}
                            </button>
                        </form>

                        <form action="{{ route('admin.rewards.destroy', $reward->id) }}" method="POST" onsubmit="return confirm('¿Borrar este premio?');">
                            @csrf
                            <button type="submit" class="text-xs font-bold text-red-400 hover:text-red-600">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-4 text-center py-12 text-gray-400">
                <i class="fas fa-gift text-4xl mb-3"></i>
                <p>No hay premios cargados aún.</p>
            </div>
            @endforelse
        </div>
    </main>

    <div id="modal-create" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">🎁 Subir Nuevo Premio</h3>
                    <button onclick="document.getElementById('modal-create').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form action="{{ route('admin.rewards.store') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700">Nombre del Premio</label>
                        <input type="text" name="name" class="mt-1 block w-full border-gray-300 rounded-md h-10 px-3 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ej: Router Wi-Fi 6" required>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700">Costo (Puntos)</label>
                            <input type="number" name="points_cost" class="mt-1 block w-full border-gray-300 rounded-md h-10 px-3" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700">Stock Inicial</label>
                            <input type="number" name="stock" class="mt-1 block w-full border-gray-300 rounded-md h-10 px-3" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700">Foto del Premio</label>
                        <input type="file" name="image" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>

                    <div class="space-y-2 bg-gray-50 p-3 rounded">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="is_featured" class="rounded text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Marcar como <b>Destacado</b> (Sale primero)</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="notify_users" class="rounded text-green-600 focus:ring-green-500">
                            <span class="text-sm text-gray-700">📢 <b>Notificar a todos</b> (Correo y App)</span>
                        </label>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow-lg w-full">
                            Subir Premio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>