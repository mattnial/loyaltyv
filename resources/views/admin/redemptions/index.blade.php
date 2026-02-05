<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Canjes - VilcanetAdmin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-gray-100 font-sans">

    @include('admin.partials.nav')

    <main class="max-w-7xl mx-auto py-8 px-4">
        
        <h2 class="text-3xl font-bold text-gray-900 mb-6">📦 Gestión de Entregas</h2>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="bg-white rounded-xl shadow-lg border-t-4 border-yellow-400 p-4">
                <h3 class="font-bold text-lg mb-4 flex items-center">
                    <i class="fas fa-clock text-yellow-500 mr-2"></i> Solicitudes Pendientes
                    <span class="ml-auto bg-gray-100 text-xs px-2 py-1 rounded-full">{{ count($pending) }}</span>
                </h3>
                
                <div class="space-y-4">
                    @foreach($pending as $item)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition bg-yellow-50">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-gray-800">{{ $item->customer->first_name }} {{ $item->customer->last_name }}</span>
                            <span class="text-xs text-gray-500">{{ $item->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-3">
                            Quiere: <b class="text-indigo-600">{{ $item->reward->name }}</b> <br>
                            Costo: {{ $item->points_used }} pts
                        </p>
                        
                        <form action="{{ route('admin.redemptions.approve', $item->id) }}" method="POST">
                            @csrf
                            <label class="text-xs font-bold text-gray-500">Asignar Sucursal:</label>
                            <select name="branch" class="w-full mt-1 mb-3 text-sm border-gray-300 rounded" required>
                                <option value="">Selecciona...</option>
                                <option value="Loja">🏢 Loja</option>
                                <option value="Vilcabamba">🌄 Vilcabamba</option>
                                <option value="Palanda">🌿 Palanda</option>
                            </select>
                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 bg-blue-600 text-white text-xs font-bold py-2 rounded hover:bg-blue-700">
                                    Aprobar
                                </button>
                                <button type="button" onclick="rejectItem({{ $item->id }})" class="px-3 bg-red-100 text-red-600 text-xs font-bold rounded hover:bg-red-200">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </form>
                        <form id="reject-form-{{ $item->id }}" action="{{ route('admin.redemptions.reject', $item->id) }}" method="POST" class="hidden mt-2">
                            @csrf
                            <input type="text" name="note" placeholder="Motivo del rechazo..." class="w-full text-xs border rounded p-1 mb-1" required>
                            <button class="w-full bg-red-500 text-white text-xs py-1 rounded">Confirmar Rechazo</button>
                        </form>
                    </div>
                    @endforeach
                    @if(count($pending) == 0) <p class="text-center text-sm text-gray-400 py-4">No hay pendientes</p> @endif
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border-t-4 border-blue-400 p-4">
                <h3 class="font-bold text-lg mb-4 flex items-center">
                    <i class="fas fa-walking text-blue-500 mr-2"></i> Esperando Retiro
                    <span class="ml-auto bg-gray-100 text-xs px-2 py-1 rounded-full">{{ count($approved) }}</span>
                </h3>

                <div class="space-y-4">
                    @foreach($approved as $item)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition bg-blue-50">
                        <div class="mb-2">
                            <span class="font-bold text-gray-800 block">{{ $item->customer->first_name }} {{ $item->customer->last_name }}</span>
                            <span class="text-xs bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full">Retira en: {{ $item->pickup_branch }}</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-3">Premio: <b>{{ $item->reward->name }}</b></p>
                        
                        <form action="{{ route('admin.redemptions.complete', $item->id) }}" method="POST" enctype="multipart/form-data" class="bg-white p-2 rounded border border-blue-100">
                            @csrf
                            <label class="text-xs font-bold text-gray-500 block mb-1">📸 Foto de Entrega (Prueba)</label>
                            <input type="file" name="proof_photo" class="w-full text-xs text-gray-500 file:py-1 file:px-2 file:rounded-full file:border-0 file:bg-blue-100 file:text-blue-700 mb-2" required>
                            <button type="submit" class="w-full bg-green-600 text-white text-xs font-bold py-2 rounded hover:bg-green-700">
                                Confirmar Entrega
                            </button>
                        </form>
                    </div>
                    @endforeach
                    @if(count($approved) == 0) <p class="text-center text-sm text-gray-400 py-4">Nadie esperando retirar</p> @endif
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border-t-4 border-green-400 p-4">
                <h3 class="font-bold text-lg mb-4 flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i> Entregados (Últimos)
                </h3>

                <div class="space-y-4">
                    @foreach($completed as $item)
                    <div class="border border-gray-200 rounded-lg p-3 opacity-75 hover:opacity-100 transition flex gap-3">
                        <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0 cursor-pointer" onclick="window.open('{{ asset('storage/'.$item->proof_photo_path) }}', '_blank')">
                            @if($item->proof_photo_path)
                                <img src="{{ asset('storage/'.$item->proof_photo_path) }}" class="w-full h-full object-cover">
                            @else
                                <div class="flex items-center justify-center h-full"><i class="fas fa-image text-gray-300"></i></div>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800">{{ $item->customer->first_name }}</p>
                            <p class="text-xs text-gray-500">Retiró: {{ $item->reward->name }}</p>
                            <p class="text-xs text-green-600 font-bold mt-1">
                                <i class="fas fa-map-marker-alt"></i> {{ $item->pickup_branch }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </main>

    <script>
        function rejectItem(id) {
            document.getElementById('reject-form-' + id).classList.toggle('hidden');
        }
    </script>
</body>
</html>