<?php

namespace App\Domains\Technical\Drivers\Core;

use Exception;

class TelnetClient
{
    private $socket = null;
    private $prompt = '';
    private $timeout = 10;
    private $buffer = '';

    /**
     * Abre la conexión con la OLT
     */
    public function connect(string $host, int $port = 23, int $timeout = 10): bool
    {
        $this->timeout = $timeout;
        $this->socket = @fsockopen($host, $port, $errno, $errstr, $this->timeout);

        if (!$this->socket) {
            throw new Exception("Error de Conexión Telnet: $errstr ($errno)");
        }

        stream_set_timeout($this->socket, $this->timeout);
        return true;
    }

    /**
     * Realiza el login en la OLT
     */
    public function login(string $user, string $pass, string $userPrompt = 'User name:', string $passPrompt = 'User password:'): bool
    {
        $this->waitPrompt($userPrompt);
        $this->write($user);
        
        $this->waitPrompt($passPrompt);
        $this->write($pass);

        // Esperamos el prompt final (ej: MA5608T>) para confirmar acceso
        $output = $this->read();
        
        // Detectamos si el login falló
        if (strpos($output, 'fail') !== false || strpos($output, 'error') !== false) {
            return false;
        }

        return true;
    }

    /**
     * Envía un comando y devuelve la respuesta limpia
     */
    public function exec(string $command): string
    {
        $this->write($command);
        $output = $this->read();
        
        // Limpieza básica de caracteres basura del buffer
        return trim(str_replace($command, '', $output));
    }

    public function disconnect(): void
    {
        if ($this->socket) {
            $this->write('quit'); // Cierre elegante
            fclose($this->socket);
            $this->socket = null;
        }
    }

    // --- Métodos Internos (Private) ---

    private function write(string $cmd): void
    {
        if (!$this->socket) return;
        fwrite($this->socket, $cmd . "\r\n");
    }

    private function read(): string
    {
        if (!$this->socket) return '';
        
        $buffer = '';
        // Leemos hasta que el socket se calle o pase el timeout
        while (!feof($this->socket)) {
            $char = fgetc($this->socket);
            $buffer .= $char;
            
            // Truco: Si detectamos el símbolo '>' o '#' al final, la OLT terminó de hablar
            if (substr($buffer, -1) === '>' || substr($buffer, -1) === '#') {
                break;
            }
        }
        return $buffer;
    }

    private function waitPrompt(string $promptString): void
    {
        // Lógica simplificada para esperar texto específico
        $this->read(); 
    }
}