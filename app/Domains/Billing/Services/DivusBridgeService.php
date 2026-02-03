<?php

namespace App\Services;

use DateTime;
use DOMDocument;
use DOMXPath;

class DivusService
{
    private $loginUrl = 'https://admin.imperium.ec/Account/LogOn';
    private $baseUrl = 'https://admin.imperium.ec';
    
    // TUS CREDENCIALES
    private $username = 'finvilca';
    private $password = 'Alia8mimo+';
    
    private $cookieFile;

    public function __construct() {
        // CORRECCIÓN: Usamos la carpeta temporal del sistema (Igual que tu código original)
        // Esto evita errores de permisos en Windows/XAMPP
        $this->cookieFile = sys_get_temp_dir() . '/divus_cookies_laravel.txt';
        
        // Creamos el archivo si no existe
        if (!file_exists($this->cookieFile)) {
            $handle = fopen($this->cookieFile, 'w');
            fclose($handle);
        }
    }

    private function login() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->loginUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // IMPORTANTE: User Agent real para que no nos bloqueen
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36');
        
        $html = curl_exec($ch);
        
        if (curl_errno($ch)) {
            // Si falla CURL, lanzamos error para verlo en el monitor
            throw new \Exception("Error CURL Login: " . curl_error($ch));
        }

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

    public function getContractPaymentDetails($cedula) {
        // 1. Obtener ID Contrato
        $contratos = $this->getAllContracts($cedula);
        if ($contratos['status'] !== 'ok' || empty($contratos['contratos'])) return null;
        
        $idContrato = $contratos['contratos'][0]['id_contrato'];

        // 2. Login y Buscar
        if (!$this->login()) throw new \Exception("Fallo Login en Divus (Revisa credenciales)");

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
                $fechaEmisionTxt = trim($cols->item(2)->textContent);

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

    private function fetchPage($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 ...');
        $html = curl_exec($ch);
        curl_close($ch);
        return $html;
    }

    private function performSearch($q) {
        $url = "https://admin.imperium.ec/Servicios/ContratosHandler?draw=1&start=0&length=50&search[value]=" . $q;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Requested-With: XMLHttpRequest']);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 ...');
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    public function getAllContracts($cedula) {
        $json = $this->performSearch($cedula);
        // Si falla o pide login, intentamos loguear
        if (!$json || strpos($json, 'LogOn') !== false) {
            if ($this->login()) $json = $this->performSearch($cedula);
            else return ['status' => 'error', 'msg' => 'Error login'];
        }

        $data = json_decode($json, true);
        // Validación extra por si devuelve null
        if (!is_array($data) || !isset($data['data']) || count($data['data']) == 0) return ['status' => 'not_found'];

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
}