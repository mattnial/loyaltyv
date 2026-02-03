<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bandeja de Pagos - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100">

    <nav class="bg-gray-900 text-white p-4 shadow-lg">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-6">
                <span class="font-bold text-xl text-green-500">Vilcanet<span class="text-white">Billing</span></span>
                
                <div class="space-x-4 text-sm font-medium">
                    @if(Auth::user()->isTechnical())
                    <a href="{{ route('admin.tickets') }}" class="text-gray-400 hover:text-white transition">
                        🎫 Tickets
                    </a>
                    @endif

                    <a href="{{ route('admin.billing.index') }}" class="text-white border-b-2 border-green-500 pb-1">
                        💰 Pagos
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <span class="text-xs text-gray-400">Hola, {{ Auth::user()->name }} ({{ Auth::user()->role }})</span>
                <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                    @csrf
                    <button class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold py-1 px-3 rounded transition">
                        Salir 🚪
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">💰 Validación de Pagos</h1>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($payments as $pay)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
                <div class="h-48 bg-gray-200 relative group cursor-pointer" onclick="window.open('{{ asset('storage/' . $pay->proof_image_path) }}', '_blank')">
                    <img src="{{ asset('storage/' . $pay->proof_image_path) }}" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition flex items-center justify-center">
                        <span class="text-white opacity-0 group-hover:opacity-100 font-bold">🔍 Ver Foto</span>
                    </div>
                </div>

                <div class="p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="font-bold text-lg text-gray-800">{{ $pay->customer->first_name }} {{ $pay->customer->last_name }}</h3>
                            <p class="text-xs text-gray-500">CI: {{ $pay->customer->identification }}</p>
                        </div>
                        <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2 py-1 rounded">Pendiente</span>
                    </div>

                    <div class="space-y-1 text-sm mb-4">
                        <p><strong>Factura:</strong> {{ $pay->invoice_number }}</p>
                        <p><strong>Monto:</strong> <span class="text-green-600 font-bold text-lg">${{ $pay->amount }}</span></p>
                        <p class="text-xs text-gray-400">{{ $pay->created_at->diffForHumans() }}</p>
                    </div>

                    <div class="flex gap-2" x-data="{ rejectOpen: false }">
                        <form action="{{ route('admin.billing.approve', $pay->id) }}" method="POST" class="flex-1">
                            @csrf
                            <button class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded font-bold shadow">
                                Aprobar
                            </button>
                        </form>

                        <button @click="rejectOpen = !rejectOpen" class="bg-red-100 hover:bg-red-200 text-red-700 px-3 rounded font-bold border border-red-300">
                            Rechazar
                        </button>

                        <div x-show="rejectOpen" class="absolute bg-white border shadow-xl p-4 rounded w-64 mt-10 z-10 left-4" @click.away="rejectOpen = false" style="display:none;">
                            <form action="{{ route('admin.billing.reject', $pay->id) }}" method="POST">
                                @csrf
                                <label class="block text-xs font-bold mb-1">Motivo:</label>
                                <textarea name="note" rows="2" class="w-full border p-1 text-sm mb-2" required placeholder="Ej: Foto ilegible"></textarea>
                                <button class="w-full bg-red-600 text-white text-xs py-1 rounded">Confirmar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-20 bg-white rounded-lg shadow">
                <p class="text-gray-400 text-xl">🎉 ¡Bandeja limpia! No hay pagos pendientes.</p>
            </div>
            @endforelse
        </div>
    </div>

</body>
</html>