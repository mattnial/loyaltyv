<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Publicidad App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-gray-100 font-sans">

    @include('admin.partials.nav')

    <div class="max-w-4xl mx-auto py-10 px-4">
        
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-800">
                📢 Publicidad en App (Popups)
            </h2>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h3 class="text-lg font-semibold mb-4 border-b pb-2">Subir Nuevo Anuncio</h3>
            <form action="{{ route('admin.popups.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Título (Opcional)</label>
                        <input type="text" name="title" class="w-full border p-2 rounded focus:outline-none focus:border-indigo-500" placeholder="Ej: ¡Promo Fibra Óptica!">
                        <p class="text-xs text-gray-500 mt-1">Aparecerá debajo de la imagen.</p>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Imagen</label>
                        <input type="file" name="image" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-xs text-gray-500 mt-1">Recomendado: Formato vertical o cuadrado (JPG, PNG).</p>
                    </div>
                </div>
                <div class="mt-4 text-right">
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded font-bold hover:bg-indigo-700 transition">
                        <i class="fas fa-cloud-upload-alt mr-2"></i> Subir Publicidad
                    </button>
                </div>
            </form>
        </div>

        <h3 class="text-xl font-bold mb-4 text-gray-700">Anuncios Existentes</h3>
        
        @if($popups->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($popups as $p)
                <div class="bg-white rounded-lg shadow overflow-hidden relative group hover:shadow-lg transition">
                    <div class="h-48 overflow-hidden bg-gray-200">
                        <img src="{{ asset('storage/'.$p->image_path) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    </div>
                    
                    <div class="p-4">
                        <h4 class="font-bold text-lg truncate">{{ $p->title ?? 'Sin título' }}</h4>
                        <div class="flex items-center justify-between mt-2">
                            <span class="px-2 py-1 text-xs rounded-full font-bold {{ $p->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $p->is_active ? '🟢 Activo' : '🔴 Inactivo' }}
                            </span>
                            <span class="text-xs text-gray-400">{{ $p->created_at->format('d/m/Y') }}</span>
                        </div>
                        
                        <div class="mt-4 flex gap-2 border-t pt-3">
                            <form action="{{ route('admin.popups.toggle', $p->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button class="w-full text-xs bg-blue-50 text-blue-600 py-2 rounded hover:bg-blue-100 font-semibold">
                                    {{ $p->is_active ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.popups.destroy', $p->id) }}" method="POST" class="flex-1" onsubmit="return confirm('¿Estás seguro de eliminar esta imagen?');">
                                @csrf
                                <button class="w-full text-xs bg-red-50 text-red-600 py-2 rounded hover:bg-red-100 font-semibold">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-10 bg-white rounded shadow">
                <p class="text-gray-500 text-lg">No hay publicidad cargada.</p>
                <p class="text-gray-400 text-sm">Sube una imagen para empezar.</p>
            </div>
        @endif
    </div>

</body>
</html>