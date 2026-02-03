<?php

namespace App\Services;

use DateTime;
use DOMDocument;
use DOMXPath;

class DivusService
{
    private $loginUrl = 'https://admin.imperium.ec/Account/LogOn';
    private $searchUrl = 'https://admin.imperium.ec/Servicios/ContratosHandler';
    private $baseUrl = 'https://admin.imperium.ec';
    
    // TUS CREDENCIALES (Lo ideal es ponerlas en el .env, pero por ahora déjalas aquí)
    private $username = 'finvilca';
    private $password = 'Alia8mimo+';
    
    private $cookieFile;

    public function __construct() {
        // Usamos storage_path de Laravel para guardar las cookies
        $this->cookieFile = storage_path('app/divus_cookies.txt');
        if (!file_exists($this->cookieFile)) file_put_contents($this->cookieFile, '');
    }

    // --- LOGIN ---
    private function login() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->loginUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        
        $html = curl_exec($ch);
        
        preg_match('/name="__RequestVerificationToken" type="hidden" value="([^"]+)"/', $html, $matches);
        $token = $matches[1] ?? null;

        if (!$token) return false;

        $postData = http_build_query([
            '__RequestVerificationToken' => $token,
            'UserName' => $this->username,
            'Password' => $this->password
        ]);

        curl_setopt($ch, CURLOPT_URL, $this->loginUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        curl_exec($ch);
        curl_close($ch);

        $cookies = file_get_contents($this->cookieFile);
        return strpos($cookies, 'FacturaStoreAdmin_ASPXAUTH') !== false;
    }

    // --- FUNCIÓN PÚBLICA: DETALLES DE PAGO (La que usamos en el Cron) ---
    public function getContractPaymentDetails($cedula) {
        // 1. Obtener ID de contrato usando la cédula
        $contratos = $this->getAllContracts($cedula);
        if ($contratos['status'] !== 'ok') return null;
        
        // Usamos el primer contrato activo
        $idContrato = $contratos['contratos'][0]['id_contrato'];

        if (!$this->login()) return null;

        $url = $this->baseUrl . "/Servicios/CobrosContrato/" . $idContrato;
        $html = $this->fetchPage($url);
        
        if (!$html) return null;

        // Parsear HTML
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        @$dom->loadHTML($html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);

        $rows = $xpath->query("//table[contains(@class, 'dataTable')]/tbody/tr");
        $ultimoPago = null;

        foreach ($rows as $row) {
            $cols = $row->getElementsByTagName('td');
            if ($cols->length > 5) {
                $estado = trim($cols->item(3)->textContent);
                $fechaCobroTxt = trim($cols->item(5)->textContent);
                $fechaEmisionTxt = trim($cols->item(2)->textContent); // Para saber qué mes pagó

                if (stripos($estado, 'Pagado') !== false) {
                    $fechaCobroTxt = preg_replace('/\s+/', ' ', $fechaCobroTxt);
                    $parts = explode(' ', $fechaCobroTxt);
                    $fechaSolo = $parts[0]; 
                    
                    $objCobro = DateTime::createFromFormat('d/m/Y', $fechaSolo);
                    $mesPagadoDate = DateTime::createFromFormat('d/m/Y', $fechaEmisionTxt);

                    if ($objCobro && $mesPagadoDate) {
                        if ($ultimoPago === null || $mesPagadoDate > $ultimoPago['obj_factura']) {
                            $ultimoPago = [
                                'obj_factura' => $mesPagadoDate,
                                'mes_pagado' => $mesPagadoDate->format('Y-m-d'),
                                'fecha_transaccion' => $objCobro->format('Y-m-d')
                            ];
                        }
                    }
                }
            }
        }
        return $ultimoPago;
    }

    // Auxiliares
    private function fetchPage($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $html = curl_exec($ch);
        curl_close($ch);
        return $html;
    }

    private function performSearch($q) {
        $url = $this->searchUrl . "?draw=1&start=0&length=50&search[value]=" . $q;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Requested-With: XMLHttpRequest']);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    public function getAllContracts($cedula) {
        $json = $this->performSearch($cedula);
        if (!$json || strpos($json, 'LogOn') !== false) {
            if ($this->login()) $json = $this->performSearch($cedula);
            else return ['status' => 'error', 'msg' => 'Error login'];
        }

        $data = json_decode($json, true);
        if (!isset($data['data']) || count($data['data']) == 0) return ['status' => 'not_found'];

        $contratos = [];
        foreach ($data['data'] as $row) {
            $contratos[] = [
                'id_contrato' => $row[1],
                'codigo' => $row[3],
                'cliente' => $row[4],
                'estado' => strip_tags($row[8]),
            ];
        }
        return ['status' => 'ok', 'contratos' => $contratos];
    }
    // --- NUEVO: OBTENER VENTAS EN VIVO (Reporte Diario) ---
    public function getLiveSales() {
        if (!$this->login()) return [];

        $url = $this->baseUrl . "/Reportes/CantidadProductosVendidos";
        
        // Fechas: Desde las 00:00 hasta las 23:59 de HOY
        $desde = date('d/m/Y 00:00:00');
        $hasta = date('d/m/Y 23:59:59');

        // Construir POST DATA para DataTables
        $postData = [
            'draw' => '1',
            'start' => '0',
            'length' => '500', // Traer hasta 500 pagos del día
            'Desde' => $desde,
            'Hasta' => $hasta,
            'PageFilter.SortCol' => 'Fecha',
            'PageFilter.SortDir' => 'desc',
            'PageFilter.Search' => '',
            // Filtros vacíos requeridos por .NET
            'DescripcionCliente' => '', 'cliente' => '', 'categoria' => '',
            'producto.Nombre' => '', 'producto' => '', 'marca' => '',
            'establecimiento' => '', 'vendedor' => '',
            'responsable.RazonSocial' => '', 'responsable' => ''
        ];

        // Convertir a string y agregar el array 'tipos' manualmente
        // (Esto es un truco para que el servidor ASP.NET entienda el array)
        $postFields = http_build_query($postData);
        $postFields .= '&tipos=Factura&tipos=NotaVenta&tipos=Ticket';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile); // Usar misma cookie del login
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Requested-With: XMLHttpRequest',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response, true);
        
        return $json['data'] ?? [];
    }
}