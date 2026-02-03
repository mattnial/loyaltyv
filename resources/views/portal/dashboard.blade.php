<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - Vilcanet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 font-sans text-gray-800" 
      x-data="{ tab: 'facturas', paymentModalOpen: false, selectedInvoice: '', payAmount: '' }">

    <nav class="bg-white shadow-md px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center">
            <span class="text-2xl font-extrabold text-blue-700 tracking-tight">Vilcanet</span>
            <div class="hidden md:block ml-4 px-3 py-1 bg-blue-50 text-blue-800 rounded-full text-xs font-semibold">
                Cliente Verificado
            </div>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm hidden md:block text-gray-600">Hola, <b>{{ $customer->first_name }}</b></span>
            <form action="{{ route('portal.logout') }}" method="POST">
                @csrf
                <button class="bg-red-500 hover:bg-red-600 text-white text-sm px-4 py-2 rounded transition">
                    Salir
                </button>
            </form>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto mt-8 px-4 grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Mi Servicio</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Plan Contratado:</span>
                        <span class="font-medium">{{ $customer->subscription->plan->name ?? 'Estándar' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Estado Actual:</span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold text-white 
                            {{ $customer->status == 'active' ? 'bg-green-500 shadow-green-200 shadow-md' : 'bg-red-500' }}">
                            {{ strtoupper($customer->status == 'active' ? 'ACTIVO' : 'SUSPENDIDO') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <h3 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wide">Seguridad</h3>
                <form action="/portal/password" method="POST">
                    @csrf
                    <div class="space-y-3">
                        <input type="password" name="password" placeholder="Nueva contraseña" class="w-full border border-gray-300 p-2 rounded text-sm focus:ring-2 focus:ring-blue-200 outline-none" required>
                        <input type="password" name="password_confirmation" placeholder="Confirmar contraseña" class="w-full border border-gray-300 p-2 rounded text-sm focus:ring-2 focus:ring-blue-200 outline-none" required>
                        <button class="w-full bg-slate-800 hover:bg-slate-900 text-white py-2 rounded text-sm font-medium transition">
                            Actualizar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded mb-6 shadow-sm flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex border-b border-gray-200 mb-6 bg-white rounded-t-lg shadow-sm overflow-hidden">
                <button @click="tab = 'facturas'" 
                    :class="{ 'bg-blue-50 text-blue-700 border-b-2 border-blue-600': tab === 'facturas', 'text-gray-500 hover:text-gray-700': tab !== 'facturas' }" 
                    class="flex-1 py-4 text-center font-medium transition-colors duration-200 flex justify-center items-center gap-2">
                    📄 Mis Facturas
                </button>
                <button @click="tab = 'soporte'" 
                    :class="{ 'bg-blue-50 text-blue-700 border-b-2 border-blue-600': tab === 'soporte', 'text-gray-500 hover:text-gray-700': tab !== 'soporte' }" 
                    class="flex-1 py-4 text-center font-medium transition-colors duration-200 flex justify-center items-center gap-2">
                    🛠️ Soporte Técnico
                </button>
            </div>

            <div x-show="tab === 'facturas'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-b-lg shadow-sm overflow-hidden border border-gray-100">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                            <tr>
                                <th class="px-6 py-4">Emisión</th>
                                <th class="px-6 py-4">Estado</th>
                                <th class="px-6 py-4 text-right">Total</th>
                                <th class="px-6 py-4 text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse($invoices as $inv)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-gray-600">{{ $inv['fecha'] }}</td>
                                <td class="px-6 py-4">
                                    @php $pagado = str_contains(strtoupper($inv['estado']), 'PAGADO'); @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-bold 
                                        {{ $pagado ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $inv['estado'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-800">{{ $inv['monto'] }}</td>
                                <td class="px-6 py-4 text-center flex justify-center gap-2">
                                    @if($inv['pdf_id'])
                                        <a href="{{ route('billing.pdf.download', $inv['pdf_id']) }}" 
                                           target="_blank" 
                                           class="text-red-600 hover:text-red-800 border border-red-200 bg-red-50 px-2 py-1 rounded text-xs font-bold flex items-center gap-1">
                                            📄 PDF
                                        </a>
                                    @endif

                                    {{-- @if(!$pagado) --}}
                                        <button 
                                            @click="paymentModalOpen = true; selectedInvoice = '{{ $inv['numero'] ?? $inv['fecha'] }}'; payAmount = '{{ $inv['monto'] }}'"
                                            class="text-green-600 hover:text-green-800 border border-green-200 bg-green-50 px-2 py-1 rounded text-xs font-bold flex items-center gap-1 cursor-pointer">
                                            💸 YA PAGUÉ
                                        </button>
                                    {{-- @endif --}}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-400">
                                    <p>No se encontraron facturas recientes.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="tab === 'soporte'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                
                <div class="bg-blue-50 p-6 rounded-lg border border-blue-100 mb-8">
                    <h3 class="text-blue-800 font-bold mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Reportar una falla
                    </h3>
                    <form action="/portal/ticket" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                            <div>
                                <label class="block text-xs font-bold text-blue-800 mb-1">Tipo de Problema</label>
                                <select name="subject" class="w-full border border-blue-200 p-2 rounded text-sm focus:ring-2 focus:ring-blue-300 outline-none bg-white">
                                    <option>Sin Internet</option>
                                    <option>Internet Lento</option>
                                    <option>Cortes Intermitentes</option>
                                    <option>Solicitud de Cambio de Clave WiFi</option>
                                    <option>Otro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-blue-800 mb-1">Prioridad (Tu percepción)</label>
                                <select disabled class="w-full border border-gray-200 p-2 rounded text-sm bg-gray-100 text-gray-500">
                                    <option>Normal (Se atenderá en orden)</option>
                                </select>
                            </div>
                        </div>
                        <label class="block text-xs font-bold text-blue-800 mb-1">Descripción Detallada</label>
                        <textarea name="description" rows="3" class="w-full border border-blue-200 p-3 rounded text-sm focus:ring-2 focus:ring-blue-300 outline-none resize-none mb-3" placeholder="Ej: Desde ayer en la noche la luz PON del router está parpadeando..."></textarea>
                        
                        <div class="text-right">
                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded font-bold shadow-md transition transform hover:scale-105">
                                Enviar Reporte
                            </button>
                        </div>
                    </form>
                </div>

                <h3 class="font-bold text-gray-700 mb-4 pl-1 border-l-4 border-gray-300">&nbsp;Historial de Reportes</h3>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                            <tr>
                                <th class="px-6 py-3">Asunto</th>
                                <th class="px-6 py-3">Fecha</th>
                                <th class="px-6 py-3">Estado</th>
                                <th class="px-6 py-3">Respuesta Técnico</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse($tickets as $t)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-800">{{ $t->subject }}</td>
                                <td class="px-6 py-4 text-gray-500 text-xs">{{ $t->created_at->format('d/m/Y h:i A') }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $colors = [
                                            'open' => 'bg-yellow-100 text-yellow-800',
                                            'in_progress' => 'bg-blue-100 text-blue-800',
                                            'resolved' => 'bg-green-100 text-green-800',
                                            'closed' => 'bg-gray-100 text-gray-800'
                                        ];
                                        $labels = [
                                            'open' => 'ABIERTO',
                                            'in_progress' => 'REVISANDO',
                                            'resolved' => 'RESUELTO',
                                            'closed' => 'CERRADO'
                                        ];
                                    @endphp
                                    <span class="px-2 py-1 rounded text-xs font-bold {{ $colors[$t->status] }}">
                                        {{ $labels[$t->status] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 italic border-l border-gray-100 bg-gray-50">
                                    {{ $t->admin_response ?? 'Esperando revisión...' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-400 text-sm">
                                    No has enviado ningún reporte todavía.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
    
    <div class="mt-12 py-6 bg-white text-center border-t border-gray-200">
        <p class="text-gray-400 text-xs">© {{ date('Y') }} Vilcanet ISP. Todos los derechos reservados.</p>
    </div>

    <div x-show="paymentModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="paymentModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="paymentModalOpen = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="paymentModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                
                <form action="{{ route('portal.payment.report') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Reportar Transferencia</h3>
                                <div class="mt-2 space-y-3">
                                    <p class="text-sm text-gray-500">Sube la foto de tu comprobante para la factura <span x-text="selectedInvoice" class="font-bold"></span>.</p>
                                    
                                    <input type="hidden" name="invoice_number" x-model="selectedInvoice">
                                    
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700">Monto ($)</label>
                                        <input type="text" name="amount" x-model="payAmount" class="w-full border p-2 rounded focus:ring-2 focus:ring-green-500 outline-none">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700">Foto del Comprobante</label>
                                        <input type="file" name="proof_image" accept="image/*" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Enviar Comprobante
                        </button>
                        <button type="button" @click="paymentModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>