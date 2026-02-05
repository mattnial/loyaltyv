<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Fidelidad - VilcanetAdmin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        .fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* Truco para borrar flechas de inputs numéricos */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }
    </style>
</head>
<body class="bg-gray-100 font-sans">

    @include('admin.partials.nav')

    <main>
        <div class="max-w-7xl mx-auto py-8 sm:px-6 lg:px-8">
            <div class="px-4 sm:px-0">
                
                <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900">
                            ⚙️ Configuración de Puntos
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">Define las reglas automáticas del sistema.</p>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.loyalty.manual') }}" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-2 px-5 rounded-lg shadow-sm transition transform hover:scale-105 flex items-center">
                            <i class="fas fa-hand-holding-heart mr-2 text-indigo-600"></i>
                            Puntos Manuales
                        </a>

                        <button type="submit" form="loyaltyForm" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition transform hover:scale-105 flex items-center">
                            <i class="fas fa-save mr-2"></i> Guardar
                        </button>
                    </div>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow flex items-center" role="alert">
                        <i class="fas fa-check-circle mr-3 text-xl"></i>
                        <div>
                            <p class="font-bold">¡Guardado correctamente!</p>
                            <p class="text-sm">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                <form id="loyaltyForm" action="{{ route('admin.loyalty.update') }}" method="POST">
                    @csrf
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        
                        <div class="lg:col-span-2 space-y-6">
                            
                            <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">
                                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center">
                                    <div class="bg-blue-100 p-2 rounded-lg text-blue-600 mr-3">
                                        <i class="fas fa-coins"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-800">Pagos y Racha</h3>
                                </div>
                                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="col-span-1 md:col-span-2">
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Puntos por Mensualidad</label>
                                        <div class="relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-star text-gray-400"></i>
                                            </div>
                                            <input type="number" name="points_per_payment" value="{{ $settings->points_per_payment }}" class="pl-10 block w-full border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 h-10 border px-3" placeholder="100">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">pts</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Inicio Racha (Día)</label>
                                        <input type="number" name="payment_start_day" value="{{ $settings->payment_start_day }}" class="block w-full border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 h-10 border px-3">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Fin Racha (Día)</label>
                                        <input type="number" name="payment_end_day" value="{{ $settings->payment_end_day }}" class="block w-full border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 h-10 border px-3">
                                    </div>
                                    
                                    <div class="col-span-1 md:col-span-2 bg-blue-50 text-blue-800 text-xs p-3 rounded-lg flex items-start border border-blue-100">
                                        <i class="fas fa-info-circle mt-0.5 mr-2"></i>
                                        <span>Si el cliente paga entre estos días, su contador de "Racha" aumenta +1 mes.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">
                                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center">
                                    <div class="bg-purple-100 p-2 rounded-lg text-purple-600 mr-3">
                                        <i class="fas fa-gift"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-800">Promociones Automáticas</h3>
                                </div>
                                <div class="p-6 space-y-6">
                                    
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                        <div class="flex items-center mb-3">
                                            <i class="fas fa-bolt text-yellow-500 mr-2"></i>
                                            <span class="font-bold text-yellow-800 text-sm">Ventana de Puntos Dobles</span>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm text-gray-600">Del día</span>
                                            <input type="number" name="double_points_start" value="{{ $settings->double_points_start }}" class="w-20 text-center border-gray-300 rounded-md h-9 border focus:ring-yellow-500 focus:border-yellow-500 bg-white">
                                            <span class="text-sm text-gray-600">al día</span>
                                            <input type="number" name="double_points_end" value="{{ $settings->double_points_end }}" class="w-20 text-center border-gray-300 rounded-md h-9 border focus:ring-yellow-500 focus:border-yellow-500 bg-white">
                                            <span class="text-sm text-gray-600">de cada mes.</span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">🎂 Cumpleaños</label>
                                            <div class="relative">
                                                <input type="number" name="points_birthday" value="{{ $settings->points_birthday }}" class="block w-full border-gray-300 rounded-md h-9 border px-2 text-right pr-8 focus:ring-purple-500 focus:border-purple-500">
                                                <span class="absolute right-2 top-2 text-xs text-gray-400">pts</span>
                                            </div>
                                        </div>
                                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">🤝 Aniversario</label>
                                            <div class="relative">
                                                <input type="number" name="points_anniversary" value="{{ $settings->points_anniversary }}" class="block w-full border-gray-300 rounded-md h-9 border px-2 text-right pr-8 focus:ring-purple-500 focus:border-purple-500">
                                                <span class="absolute right-2 top-2 text-xs text-gray-400">pts</span>
                                            </div>
                                        </div>
                                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">🎄 Navidad</label>
                                            <div class="relative">
                                                <input type="number" name="points_christmas" value="{{ $settings->points_christmas }}" class="block w-full border-gray-300 rounded-md h-9 border px-2 text-right pr-8 focus:ring-purple-500 focus:border-purple-500">
                                                <span class="absolute right-2 top-2 text-xs text-gray-400">pts</span>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-1">
                            <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200 h-full flex flex-col">
                                
                                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                                    <div class="flex items-center">
                                        <div class="bg-orange-100 p-2 rounded-lg text-orange-600 mr-3">
                                            <i class="fas fa-fire"></i>
                                        </div>
                                        <h3 class="text-lg font-bold text-gray-800">Hitos de Racha</h3>
                                    </div>
                                    <button type="button" onclick="addMilestone()" class="bg-green-100 hover:bg-green-200 text-green-700 px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center border border-green-200">
                                        <i class="fas fa-plus mr-1"></i> Agregar
                                    </button>
                                </div>
                                
                                <div class="p-6 bg-gray-50/30 flex-1">
                                    <p class="text-xs text-gray-500 mb-4 px-1 leading-relaxed">
                                        Premia la constancia. Si un cliente cumple X meses seguidos, recibe un bono único.
                                    </p>
                                    
                                    <div class="flex items-center px-3 mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                        <div class="flex-1">Meses Seguidos</div>
                                        <div class="w-6"></div>
                                        <div class="flex-1">Puntos Bono</div>
                                        <div class="w-8"></div>
                                    </div>

                                    <div id="milestones-container" class="space-y-2">
                                        @foreach($milestones as $index => $ms)
                                        <div class="bg-white p-2 rounded-lg border border-gray-200 shadow-sm flex items-center gap-3 milestone-row group hover:border-indigo-300 transition">
                                            
                                            <div class="flex-1 relative">
                                                <input type="number" name="milestones[{{ $index }}][months]" value="{{ $ms->months_required }}" class="w-full text-sm border-gray-200 rounded focus:ring-indigo-500 focus:border-indigo-500 h-9 pl-3 bg-gray-50 focus:bg-white transition" placeholder="6">
                                                <span class="absolute right-2 top-2.5 text-[10px] text-gray-400 font-bold">M</span>
                                            </div>
                                            
                                            <div class="text-gray-300">
                                                <i class="fas fa-arrow-right text-xs"></i>
                                            </div>

                                            <div class="flex-1 relative">
                                                <input type="number" name="milestones[{{ $index }}][points]" value="{{ $ms->bonus_points }}" class="w-full text-sm border-gray-200 rounded focus:ring-indigo-500 focus:border-indigo-500 h-9 pl-3 bg-gray-50 focus:bg-white font-bold text-indigo-600 transition" placeholder="50">
                                                <span class="absolute right-2 top-2.5 text-[10px] text-gray-400 font-bold">PTS</span>
                                            </div>

                                            <div>
                                                <button type="button" onclick="removeRow(this)" class="w-8 h-8 flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-full transition">
                                                    <i class="fas fa-trash-alt text-sm"></i>
                                                </button>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>

                                    <div id="empty-msg" class="flex flex-col items-center justify-center py-10 text-gray-400 {{ count($milestones) > 0 ? 'hidden' : '' }}">
                                        <i class="fas fa-wind text-3xl mb-2 text-gray-300"></i>
                                        <span class="text-sm">No hay hitos creados</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        function addMilestone() {
            const container = document.getElementById('milestones-container');
            const index = new Date().getTime(); // ID único
            
            const html = `
            <div class="bg-white p-2 rounded-lg border border-gray-200 shadow-sm flex items-center gap-3 milestone-row group hover:border-indigo-300 transition fade-in">
                
                <div class="flex-1 relative">
                    <input type="number" name="milestones[${index}][months]" class="w-full text-sm border-gray-200 rounded focus:ring-indigo-500 focus:border-indigo-500 h-9 pl-3 bg-gray-50 focus:bg-white transition" placeholder="3">
                    <span class="absolute right-2 top-2.5 text-[10px] text-gray-400 font-bold">M</span>
                </div>
                
                <div class="text-gray-300">
                    <i class="fas fa-arrow-right text-xs"></i>
                </div>

                <div class="flex-1 relative">
                    <input type="number" name="milestones[${index}][points]" class="w-full text-sm border-gray-200 rounded focus:ring-indigo-500 focus:border-indigo-500 h-9 pl-3 bg-gray-50 focus:bg-white font-bold text-indigo-600 transition" placeholder="20">
                    <span class="absolute right-2 top-2.5 text-[10px] text-gray-400 font-bold">PTS</span>
                </div>

                <div>
                    <button type="button" onclick="removeRow(this)" class="w-8 h-8 flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-full transition">
                        <i class="fas fa-trash-alt text-sm"></i>
                    </button>
                </div>
            </div>
            `;
            
            container.insertAdjacentHTML('beforeend', html);
            document.getElementById('empty-msg').classList.add('hidden');
        }

        function removeRow(btn) {
            btn.closest('.milestone-row').remove();
            const container = document.getElementById('milestones-container');
            if (container.children.length === 0) {
                document.getElementById('empty-msg').classList.remove('hidden');
            }
        }
    </script>
</body>
</html>