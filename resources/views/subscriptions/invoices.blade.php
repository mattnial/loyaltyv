<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Facturas Divus</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white shadow rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4">📂 Facturas de {{ $customer->full_name }}</h2>
        <p class="text-sm text-gray-500 mb-6">Datos sincronizados en tiempo real desde Divusware (Imperium).</p>

        @if(empty($invoices))
            <div class="p-4 bg-yellow-100 text-yellow-700 rounded">
                No se encontraron facturas o no se pudo conectar con Divus.
            </div>
        @else
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 text-sm uppercase">
                        <th class="p-3">Fecha</th>
                        <th class="p-3">Número</th>
                        <th class="p-3">Estado</th>
                        <th class="p-3">Monto</th>
                        <th class="p-3">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $inv)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3">{{ $inv['fecha'] }}</td>
                        <td class="p-3 font-mono text-xs">{{ $inv['numero'] }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs font-bold 
                                {{ strpos($inv['estado'], 'Pagado') !== false ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                                {{ $inv['estado'] }}
                            </span>
                        </td>
                        <td class="p-3 font-bold">{{ $inv['monto'] }}</td>
                        <td class="p-3">
                            @if($inv['pdf_id'])
                                <a href="/billing/divus-pdf/{{ $inv['pdf_id'] }}" target="_blank" 
                                   class="text-blue-600 hover:underline flex items-center">
                                   📄 Ver PDF
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        
        <div class="mt-6">
            <a href="/subscriptions" class="text-gray-600 hover:text-gray-900 underline">← Volver al Panel</a>
        </div>
    </div>
</body>
</html>