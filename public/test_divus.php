<?php

namespace App\Services;

use DateTime;

class DivusService
{
    private $loginUrl = 'https://admin.imperium.ec/Account/LogOn';
    private $baseUrl  = 'https://admin.imperium.ec';
    
    // CREDENCIALES
    private $username = 'finvilca';
    private $password = 'Alia8mimo+';
    
    private $cookieFile;

    public function __construct() {
        // 1. RUTA SEGURA EN LARAVEL (Storage)
        $path = storage_path('app/divus_cookies.txt');
        
        // 2. TRUCO PARA WINDOWS: Reemplazar \ por / para que cURL no falle
        $this->cookieFile = str_replace('\\', '/', $path);

        // 3. Crear archivo vacío si no existe (para evitar error de permisos)
        if (!file_exists($this->cookieFile)) {
            file_put_contents($this->cookieFile, '');
        }
    }

    private function login() {
        $ch = curl_init();
        
        // PASO A: Obtener Token
        curl_setopt($ch, CURLOPT_URL, $this->loginUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36');
        
        $html = curl_exec($ch);
        
        preg_match('/name="__RequestVerificationToken" type="hidden" value="([^"]+)"/', $html, $matches);
        $token = $matches[1] ?? null;

        if (!$token) return false;

        // PASO B: Enviar Credenciales
        $postData = http_build_query([
            '__RequestVerificationToken' => $token,
            'UserName' => $this->username,
            'Password' => $this->password
        ]);

        curl_setopt($ch, CURLOPT_URL, $this->loginUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        // HEADERS OBLIGATORIOS
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Origin: ' . $this->baseUrl,
            'Referer: ' . $this->loginUrl,
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36'
        ]);
        
        curl_exec($ch);
        curl_close($ch);

        // Verificar si tenemos la cookie de sesión
        $cookies = file_get_contents($this->cookieFile);
        return strpos($cookies, 'FacturaStoreAdmin_ASPXAUTH') !== false;
    }

    // --- FUNCIÓN PRINCIPAL ---
    public function getLiveSales() {
        // Intentamos loguear, si falla devolvemos array vacío
        if (!$this->login()) return [];

        $url = $this->baseUrl . "/Reportes/TotalVentas";
        
        // Rango: Todo el día de HOY
        date_default_timezone_set('America/Guayaquil');
        $desde = date('d/m/Y 00:00:00');
        $hasta = date('d/m/Y 23:59:59');

        $postData = [
            'draw' => '1', 'start' => '0', 'length' => '500', 
            'Desde' => $desde, 'Hasta' => $hasta,
            'PageFilter.SortCol' => 'Fecha', 'PageFilter.SortDir' => 'desc',
            'PageFilter.Search' => '',
            'DescripcionCliente' => '', 'cliente' => '', 'establecimiento' => '', 
            'vendedor' => '', 'responsable' => ''
        ];

        $postFields = http_build_query($postData) . '&tipos=Factura&tipos=NotaVenta&tipos=Ticket';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        // HEADERS IDÉNTICOS A NAVEGADOR
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Requested-With: XMLHttpRequest',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36',
            'Referer: ' . $this->baseUrl . '/Reportes/TotalVentas',
            'Origin: ' . $this->baseUrl
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response, true);
        
        return $json['data'] ?? [];
    }
}