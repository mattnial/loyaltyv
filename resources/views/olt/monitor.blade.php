<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Monitor OLT - ISP Ecuador</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">

    <div class="max-w-2xl mx-auto bg-white shadow-xl rounded-lg overflow-hidden">
        
        <div class="bg-blue-600 p-4 text-white flex justify-between items-center">
            <h1 class="text-xl font-bold">📡 Monitor de Señal en Tiempo Real</h1>
            <span class="bg-blue-800 text-xs py-1 px-2 rounded">OLT ID: {{ $olt_id }}</span>
        </div>

        <div class="p-6">
            <div class="flex items-center mb-6">
                <div class="w-16 h-16 rounded-full flex items-center justify-center text-2xl
                    {{ $data['status'] == 'online' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                    {{ $data['status'] == 'online' ? '✅' : '❌' }}
                </div>
                <div class="ml-4">
                    <h2 class="text-gray-800 font-bold text-lg">ONU Index: {{ $onu }}</h2>
                    <p class="text-sm text-gray-500">Última actualización: {{ $data['timestamp'] }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                
                <div class="bg-gray-50 p-4 rounded border-l-4 {{ $data['rx_power'] > -25 ? 'border-green-500' : 'border-red-500' }}">
                    <p class="text-xs text-gray-500 uppercase">Potencia RX (Luz)</p>
                    <p class="text-2xl font-mono font-bold text-gray-800">{{ $data['rx_power'] }} dBm</p>
                    <p class="text-xs mt-1 {{ $data['rx_power'] > -25 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $data['rx_power'] > -25 ? 'Niveles Óptimos' : 'Señal Crítica' }}
                    </p>
                </div>

                <div class="bg-gray-50 p-4 rounded border-l-4 border-blue-400">
                    <p class="text-xs text-gray-500 uppercase">Distancia Aprox</p>
                    <p class="text-2xl font-mono font-bold text-gray-800">{{ $data['distance'] }} m</p>
                    <p class="text-xs mt-1 text-blue-600">Fibra Óptica</p>
                </div>

            </div>

            <div class="mt-6">
                <details>
                    <summary class="cursor-pointer text-blue-600 text-sm font-semibold hover:underline">
                        Ver respuesta Raw de Huawei
                    </summary>
                    <pre class="bg-gray-900 text-green-400 p-4 rounded mt-2 text-xs overflow-x-auto">
{{ $data['raw_output'] }}
                    </pre>
                </details>
            </div>
        </div>
    </div>

</body>
</html>