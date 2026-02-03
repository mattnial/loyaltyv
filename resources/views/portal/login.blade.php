<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Clientes - Vilcanet</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-2xl w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-blue-700">Vilcanet</h1>
            <p class="text-gray-500">Portal de Autogestión</p>
        </div>

        <form action="{{ route('portal.login.submit') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Cédula o RUC</label>
                <input type="text" name="identification" class="w-full p-3 border rounded focus:outline-none focus:border-blue-500" required placeholder="Ej: 1104...">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">Contraseña</label>
                <input type="password" name="password" class="w-full p-3 border rounded focus:outline-none focus:border-blue-500" required placeholder="******">
                <p class="text-xs text-gray-400 mt-2 text-center">
                    (Por defecto es tu número de cédula)
                </p>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded hover:bg-blue-700 transition duration-300">
                INGRESAR
            </button>
        </form>

        @if($errors->any())
            <div class="mt-4 p-3 bg-red-100 text-red-700 text-sm rounded text-center">
                {{ $errors->first() }}
            </div>
        @endif
    </div>
</body>
</html>