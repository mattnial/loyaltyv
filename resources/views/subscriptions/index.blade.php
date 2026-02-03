<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Suscriptores - ISP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">📡 Gestión de Clientes</h1>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Cliente
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Plan / IP
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subscriptions as $sub)
                    <tr>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <div class="flex items-center">
                                <div class="ml-3">
                                    <p class="text-gray-900 whitespace-no-wrap font-bold">
                                        {{ $sub->customer->full_name }}
                                    </p>
                                    <p class="text-gray-600 whitespace-no-wrap text-xs">
                                        {{ $sub->customer->identification }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <p class="text-gray-900 whitespace-no-wrap">{{ $sub->plan->name }}</p>
                            <p class="text-gray-500 text-xs">{{ $sub->service_ip }}</p>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <span class="relative inline-block px-3 py-1 font-semibold leading-tight 
                                {{ $sub->status == 'active' ? 'text-green-900' : 'text-red-900' }}">
                                <span aria-hidden="true" class="absolute inset-0 opacity-50 rounded-full 
                                    {{ $sub->status == 'active' ? 'bg-green-200' : 'bg-red-200' }}"></span>
                                <span class="relative">
                                    {{ $sub->status == 'active' ? 'ACTIVO' : 'CORTADO' }}
                                </span>
                            </span>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <a href="/subscriptions/toggle/{{ $sub->id }}" 
                               class="inline-block px-4 py-2 text-white font-bold rounded hover:shadow-lg transition duration-200
                               {{ $sub->status == 'active' ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }}">
                                {{ $sub->status == 'active' ? '✂️ Cortar' : '⚡ Reconectar' }}
                            </a>
                            <a href="/billing/invoices/{{ $sub->customer_id }}" 
                               class="inline-block ml-2 px-3 py-2 bg-blue-500 text-white font-bold rounded hover:bg-blue-600 text-xs">
                               📂 Facturas
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>