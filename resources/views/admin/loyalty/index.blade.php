<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Fidelidad - VilcanetAdmin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-gray-100">

    <nav class="bg-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-white font-bold text-xl">Vilcanet<span class="text-red-500">Admin</span></span>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <a href="{{ route('admin.tickets.index') }}" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-ticket-alt mr-1"></i> Tickets
                            </a>
                            
                            <a href="{{ route('admin.billing.index') }}" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-file-invoice-dollar mr-1"></i> Pagos
                            </a>

                            <a href="#" class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-star mr-1 text-yellow-400"></i> Fidelidad
                            </a>
                        </div>
                    </div>
                </div>
                <div class="ml-4 flex items-center md:ml-6">
                    <span class="text-gray-300 text-sm mr-4">Hola, Admin</span>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            
            <div class="px-4 py-6 sm:px-0">
                
                <div class="md:flex md:items-center md:justify-between mb-6">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                            ⚙️ Configuración del Sistema de Puntos
                        </h2>
                    </div>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                        <p class="font-bold">¡Guardado!</p>
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                <form action="{{ route('admin.loyalty.update') }}" method="POST">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                            <div class="px-4 py-5 sm:px-6 border-b border-gray-200 bg-gray-50">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    <i class="fas fa-coins text-blue-500 mr-2"></i> Puntos Base
                                </h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Puntos por Mensualidad</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <input type="number" name="points_per_payment" value="{{ $settings->points_per_payment }}" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-3 pr-12 sm:text-sm border-gray-300 rounded-md h-10 border px-2">
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">Pts</span>
                                        </div>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500">Puntos otorgados al pagar una factura normal.</p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Inicio Racha (Día)</label>
                                        <input type="number" name="payment_start_day" value="{{ $settings->payment_start_day }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md h-10 border px-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Fin Racha (Día)</label>
                                        <input type="number" name="payment_end_day" value="{{ $settings->payment_end_day }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md h-10 border px-2">
                                    </div>
                                </div>
                                <div class="bg-blue-50 p-3 rounded-md text-sm text-blue-700">
                                    <i class="fas fa-info-circle"></i> Si pagan entre estos días, su racha aumenta.
                                </div>
                            </div>
                        </div>

                        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                            <div class="px-4 py-5 sm:px-6 border-b border-gray-200 bg-gray-50">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    <i class="fas fa-gift text-purple-500 mr-2"></i> Promociones y Regalos
                                </h3>
                            </div>
                            <div class="p-6 space-y-6">
                                
                                <div class="bg-yellow-50 p-4 rounded-md border border-yellow-200">
                                    <label class="block text-sm font-bold text-yellow-800 mb-2">⭐ Días de Puntos Dobles</label>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-600">Del día</span>
                                        <input type="number" name="double_points_start" value="{{ $settings->double_points_start }}" class="w-16 text-center border-gray-300 rounded-md border h-8">
                                        <span class="text-sm text-gray-600">al día</span>
                                        <input type="number" name="double_points_end" value="{{ $settings->double_points_end }}" class="w-16 text-center border-gray-300 rounded-md border h-8">
                                    </div>
                                </div>

                                <div class="space-y-4 border-t pt-4">
                                    <h4 class="font-medium text-gray-900">Regalos Automáticos</h4>
                                    
                                    <div class="flex items-center justify-between">
                                        <label class="text-sm text-gray-600">🎂 Cumpleaños</label>
                                        <input type="number" name="points_birthday" value="{{ $settings->points_birthday }}" class="w-24 text-right border-gray-300 rounded-md border h-8 px-2">
                                    </div>
                                    
                                    <div class="flex items-center justify-between">
                                        <label class="text-sm text-gray-600">🤝 Aniversario Contrato</label>
                                        <input type="number" name="points_anniversary" value="{{ $settings->points_anniversary }}" class="w-24 text-right border-gray-300 rounded-md border h-8 px-2">
                                    </div>
                                    
                                    <div class="flex items-center justify-between">
                                        <label class="text-sm text-gray-600">🎄 Navidad</label>
                                        <input type="number" name="points_christmas" value="{{ $settings->points_christmas }}" class="w-24 text-right border-gray-300 rounded-md border h-8 px-2">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Guardar Cambios
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </main>
</body>
</html>