<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Administrativo - Vilcanet</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-2xl w-full max-w-sm border-t-4 border-red-600">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">🔒 Staff Only</h2>
            <p class="text-sm text-gray-500">Acceso restringido a personal</p>
        </div>

        <form action="{{ route('admin.login.submit') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-1 text-sm">Email Corporativo</label>
                <input type="email" name="email" class="w-full p-2 border rounded focus:ring-2 focus:ring-red-500 outline-none" required>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-1 text-sm">Contraseña</label>
                <input type="password" name="password" class="w-full p-2 border rounded focus:ring-2 focus:ring-red-500 outline-none" required>
            </div>

            <button type="submit" class="w-full bg-gray-800 text-white font-bold py-2 rounded hover:bg-black transition">
                INGRESAR AL SISTEMA
            </button>
        </form>

        @if($errors->any())
            <div class="mt-4 p-2 bg-red-100 text-red-700 text-xs rounded text-center">
                {{ $errors->first() }}
            </div>
        @endif
    </div>
</body>
</html>