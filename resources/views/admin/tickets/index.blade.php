<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Soporte Técnico - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

@include('admin.partials.nav')
<div class="container mx-auto mt-6">
       </div>

    <div class="max-w-7xl mx-auto p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">📡 Centro de Soporte</h1>
            <span class="bg-blue-600 text-white px-4 py-2 rounded-full text-sm font-bold">
                {{ $tickets->where('status', 'open')->count() }} Pendientes
            </span>
        </div>

        <div class="grid gap-6">
            @foreach($tickets as $ticket)
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 {{ $ticket->status == 'open' ? 'border-red-500' : ($ticket->status == 'resolved' ? 'border-green-500' : 'border-blue-500') }}">
                
                <form action="{{ route('admin.tickets.update', $ticket->id) }}" method="POST">
                    @csrf
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">{{ $ticket->subject }}</h2>
                            <p class="text-sm text-gray-500 mb-2">
                                Cliente: <span class="font-bold text-blue-600">{{ $ticket->customer->first_name }} {{ $ticket->customer->last_name }}</span> 
                                | IP: {{ $ticket->customer->subscription->service_ip ?? 'N/A' }}
                                | Fecha: {{ $ticket->created_at->diffForHumans() }}
                            </p>
                            <p class="text-gray-700 bg-gray-50 p-3 rounded border border-gray-200">
                                "{{ $ticket->description }}"
                            </p>
                        </div>
                        
                        <div class="flex flex-col gap-2 w-48">
                            <select name="status" class="border p-2 rounded text-sm font-bold">
                                <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>🔴 ABIERTO</option>
                                <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>🔵 REVISANDO</option>
                                <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>🟢 RESUELTO</option>
                                <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>⚫ CERRADO</option>
                            </select>
                            
                            <select name="priority" class="border p-2 rounded text-sm">
                                <option value="low" {{ $ticket->priority == 'low' ? 'selected' : '' }}>Baja</option>
                                <option value="medium" {{ $ticket->priority == 'medium' ? 'selected' : '' }}>Media</option>
                                <option value="high" {{ $ticket->priority == 'high' ? 'selected' : '' }}>Alta</option>
                                <option value="critical" {{ $ticket->priority == 'critical' ? 'selected' : '' }}>CRÍTICA</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Respuesta del Técnico:</label>
                        <div class="flex gap-2">
                            <input type="text" name="admin_response" 
                                   value="{{ $ticket->admin_response }}" 
                                   placeholder="Escribe aquí la solución o respuesta para el cliente..." 
                                   class="flex-1 border p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
                            <button class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-black transition">
                                Guardar
                            </button>
                        </div>
                    </div>
                </form>

            </div>
            @endforeach
        </div>
    </div>

</body>
</html>