<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Puntos Manuales - VilcanetAdmin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 font-sans">

    @include('admin.partials.nav')

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h2 class="text-3xl font-bold leading-7 text-gray-900 sm:truncate">
                    👐 Asignación Manual de Puntos
                </h2>
                <p class="text-gray-500 mt-1 text-sm">Usa esto para dar puntos por vueltos, compensaciones o regalos especiales.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow flex items-center">
                <i class="fas fa-check-circle mr-3 text-xl"></i>
                <p class="font-bold">{{ session('success') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
                    <div class="px-6 py-4 bg-indigo-600 border-b border-indigo-700 flex items-center">
                        <i class="fas fa-hand-holding-heart text-white text-xl mr-3"></i>
                        <h3 class="text-lg font-bold text-white">Dar Puntos</h3>
                    </div>
                    
                    <form action="{{ route('admin.loyalty.manual.store') }}" method="POST" class="p-6 space-y-6">
                        @csrf
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Seleccionar Cliente</label>
                            <select name="customer_id" id="customer-select" class="w-full border-gray-300 rounded-md shadow-sm h-10" required>
                                <option value="">Buscar por nombre o cédula...</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->first_name }} {{ $c->last_name }} ({{ $c->identification }}) - Saldo: {{ $c->points }} pts
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Cantidad de Puntos</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-plus text-green-500"></i>
                                </div>
                                <input type="number" name="points" class="pl-10 block w-full border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 h-10" placeholder="Ej: 50" required min="1">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Motivo / Descripción</label>
                            <textarea name="description" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2" placeholder="Ej: Vuelto factura #1045 por falta de monedas" required></textarea>
                            <p class="text-xs text-gray-400 mt-1">Esto aparecerá en el historial del cliente.</p>
                        </div>

                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition transform hover:scale-105">
                            <i class="fas fa-save mr-2 mt-0.5"></i> Asignar Puntos
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200 h-full">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-history text-gray-500 text-xl mr-3"></i>
                            <h3 class="text-lg font-bold text-gray-700">Últimos Movimientos Manuales</h3>
                        </div>
                    </div>
                    <div class="p-0 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Puntos</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Autor</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                    // Traemos los últimos 10 movimientos manuales (donde user_id NO es null)
                                    $recent = \App\Models\PointHistory::whereNotNull('user_id')->with(['customer', 'user'])->latest()->take(10)->get();
                                @endphp

                                @forelse($recent as $h)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $h->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $h->customer->first_name }} {{ $h->customer->last_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            +{{ $h->points }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 truncate max-w-xs">
                                        {{ $h->description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <i class="fas fa-user-circle mr-1"></i> {{ $h->author_name }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                                        No hay movimientos manuales recientes.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#customer-select').select2({
                placeholder: "Buscar cliente...",
                allowClear: true
            });
        });
    </script>
</body>
</html>