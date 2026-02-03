<?php

namespace App\Domains\Technical\Drivers\Core;

use Exception;

class RouterOsClient
{
    private $socket;
    private $connected = false;

    public function connect($ip, $user, $password, $port = 8728)
    {
        $this->socket = @fsockopen($ip, $port, $errno, $errstr, 3);
        
        if (!$this->socket) {
            throw new Exception("No se pudo conectar al Mikrotik $ip: $errstr");
        }

        // Proceso de Login (Challenge/Response)
        $this->write('/login');
        $response = $this->read();
        
        // RouterOS moderno (6.43+) usa login simple, antiguo usa challenge
        if (isset($response[0]['!ret'])) {
            // Método antiguo (Challenge)
            $challenge = pack('H*', $response[0]['!ret']);
            $md5 = md5(chr(0) . $password . $challenge);
            $this->write('/login', ['name' => $user, 'response' => '00' . $md5]);
            $this->read();
        } else {
            // Método moderno (Post 6.43)
            $this->write('/login', ['name' => $user, 'password' => $password]);
            $response = $this->read();
        }

        $this->connected = true;
        return true;
    }

    public function disconnect()
    {
        if ($this->socket) fclose($this->socket);
        $this->connected = false;
    }

    /**
     * Envía comandos (Ej: /ppp/secret/print)
     */
    public function comm($command, array $params = [])
    {
        if (!$this->connected) throw new Exception("No hay conexión activa con Mikrotik");

        $this->write($command, $params);
        return $this->read();
    }

    // --- Lógica de Bajo Nivel (Byte Encoding) ---

    private function write($command, array $params = [])
    {
        $payload = [$command];
        foreach ($params as $k => $v) {
            $payload[] = "=$k=$v";
        }
        
        foreach ($payload as $data) {
            $this->encodeLength(strlen($data));
            fwrite($this->socket, $data);
        }
        fwrite($this->socket, chr(0)); // Fin del comando
    }

    private function read()
    {
        $response = [];
        while (true) {
            $line = $this->decodeLength();
            if ($line === null) break; // Fin de respuesta
            
            // Parsear respuesta (!re, !done, !trap)
            if ($line === '!done') return $response;
            
            if (strpos($line, '!trap') === 0) {
                // Error devuelto por Mikrotik
                $data = $this->decodeLength(); // Leer mensaje de error
                throw new Exception("Mikrotik Error: " . ($data ?? 'Unknown'));
            }
            
            if (strpos($line, '!re') === 0) {
                // Nueva línea de datos
                $item = [];
                while (true) {
                    $attr = $this->decodeLength();
                    if ($attr === null || $attr === '') break;
                    
                    if (strpos($attr, '=') === 0) {
                        $parts = explode('=', substr($attr, 1), 2);
                        if (count($parts) == 2) $item[$parts[0]] = $parts[1];
                    }
                }
                $response[] = $item;
            }
        }
        return $response;
    }

    private function encodeLength($length)
    {
        if ($length < 0x80) {
            fwrite($this->socket, chr($length));
        } elseif ($length < 0x4000) {
            fwrite($this->socket, chr($length >> 8 | 0x80) . chr($length & 0xFF));
        } else {
            // Soporte básico para longitudes, expandible si es necesario
            throw new Exception("Payload muy largo para esta implementación simple.");
        }
    }

    private function decodeLength()
    {
        if (feof($this->socket)) return null;
        $byte = ord(fread($this->socket, 1));
        
        if ($byte === 0) return null; // Fin de bloque
        
        if (($byte & 0x80) == 0) {
            $length = $byte;
        } else {
            $byte2 = ord(fread($this->socket, 1));
            $length = (($byte & 0x7F) << 8) | $byte2;
        }
        
        $data = "";
        if ($length > 0) {
            $data = "";
            while (strlen($data) < $length) {
                $chunk = fread($this->socket, $length - strlen($data));
                $data .= $chunk;
            }
        }
        return $data;
    }
}