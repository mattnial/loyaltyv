<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor de Pagos - Vilcanet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-gray-100 p-6">

    <div class="max-w-6xl mx-auto">
        
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-blue-900">📡 Monitor de Pagos Divusware</h1>
                <p class="text-gray-600">Viendo transacciones en tiempo real</p>
            </div>
            <div class="flex gap-4 items-center">
                <span id="statusIndicator" class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-bold animate-pulse">
                    ● En línea
                </span>
                <button onclick="forceScan()" id="btnScan" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow-lg font-bold transition">
                    <i class="fas fa-sync-alt mr-2"></i> Escanear Ahora
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-4 bg-gray-50 border-b border-gray-200 flex justify-between">
                <h3 class="font-bold text-gray-700">Últimos Pagos Detectados</h3>
                <span class="text-xs text-gray-400">Actualizado: <span id="lastUpdate">--:--:--</span></span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left">Fecha Pago</th>
                            <th class="py-3 px-6 text-left">Cédula</th>
                            <th class="py-3 px-6 text-center">Monto</th>
                            <th class="py-3 px-6 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody id="logsTable" class="text-gray-600 text-sm font-light">
                        <tr>
                            <td colspan="4" class="py-4 text-center text-gray-400">Cargando datos...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-sm font-bold text-gray-500 mb-2">Salida del Sistema:</h3>
            <div id="consoleOutput" class="bg-black text-green-400 font-mono text-xs p-4 rounded-lg h-32 overflow-y-auto">
                Esperando comando...
            </div>
        </div>

    </div>

    <script>
    // DETECTAR URL AUTOMÁTICAMENTE (Funciona en artisan serve y hosting)
    // Si estás en http://127.0.0.1:8000/monitor-pagos, la base será http://127.0.0.1:8000
    const baseUrl = window.location.origin; 

    // 1. FUNCIÓN PARA CARGAR DATOS
    async function loadLogs() {
        try {
            const response = await fetch(`${baseUrl}/api/admin/monitor/data`);
            
            if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);

            const data = await response.json();

            if(data.status === 'ok') {
                const tbody = document.getElementById('logsTable');
                tbody.innerHTML = ''; 

                if (data.logs.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="py-4 text-center text-gray-400">No hay registros de pagos aún.</td></tr>';
                }

                data.logs.forEach(log => {
                    const row = `
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-6 font-bold whitespace-nowrap">${log.fecha}</td>
                            <td class="py-3 px-6">${log.cedula}</td>
                            <td class="py-3 px-6 text-center">
                                <span class="bg-blue-100 text-blue-600 py-1 px-3 rounded-full text-xs">$${log.monto}</span>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <span class="${log.color} font-bold">${log.estado}</span>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });

                document.getElementById('lastUpdate').innerText = data.last_check;
                
                // Indicador visual de estado
                const ind = document.getElementById('statusIndicator');
                ind.className = "px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-bold animate-pulse";
                ind.innerText = "● En línea";
            }
        } catch (error) {
            console.error('Error monitor:', error);
            const ind = document.getElementById('statusIndicator');
            ind.className = "px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-bold";
            ind.innerText = "● Desconectado";
        }
    }

    // 2. FUNCIÓN PARA FORZAR ESCANEO (MANUAL)
    async function forceScan() {
        const btn = document.getElementById('btnScan');
        const consoleDiv = document.getElementById('consoleOutput');
        
        // Bloquear botón
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Escaneando...';
        consoleDiv.innerHTML += `\n> Conectando a: ${baseUrl}/api/admin/monitor/scan...`;

        try {
            // Hacemos la petición
            const response = await fetch(`${baseUrl}/api/admin/monitor/scan`, { 
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            // LEER RESPUESTA COMO TEXTO PRIMERO (Para ver errores PHP ocultos)
            const rawText = await response.text();

            try {
                // Intentamos convertir a JSON
                const data = JSON.parse(rawText);

                if(data.status === 'ok') {
                    consoleDiv.innerHTML += "\n" + data.details;
                    consoleDiv.innerHTML += "\n> ✅ ESCANEO COMPLETADO.";
                    loadLogs(); 
                } else {
                    consoleDiv.innerHTML += "\n> ❌ ERROR DEL SISTEMA: " + data.msg;
                }
            } catch (jsonError) {
                // SI FALLA EL JSON, ES UN ERROR DE PHP (Muestra el HTML del error)
                consoleDiv.innerHTML += "\n> 💥 ERROR GRAVE DE PHP (Servidor):";
                // Mostramos solo los primeros 200 caracteres del error para no llenar la pantalla
                consoleDiv.innerHTML += "\n" + rawText.substring(0, 200) + "...";
                console.error("Error PHP Completo:", rawText);
            }

        } catch (error) {
            consoleDiv.innerHTML += "\n> 🔌 ERROR DE RED: " + error.message;
        }

        // Restaurar botón
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i> Escanear Ahora';
        consoleDiv.scrollTop = consoleDiv.scrollHeight; 
    }

    // Auto-actualizar
    setInterval(loadLogs, 5000);
    loadLogs(); 
</script>
</body>
</html>