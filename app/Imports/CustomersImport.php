<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CustomersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (!isset($row['cedula']) || empty($row['cedula'])) {
            return null;
        }

        $cedula = trim($row['cedula']);
        // Contraseña por defecto: 4 últimos dígitos o 1234
        $pass = strlen($cedula) > 4 ? substr($cedula, -4) : '1234';

        // Manejo de fecha
        $inicioContrato = now();
        if (isset($row['fecha_inicio']) && !empty($row['fecha_inicio'])) {
            try {
                if (is_numeric($row['fecha_inicio'])) {
                    $inicioContrato = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha_inicio']);
                } else {
                    $inicioContrato = Carbon::parse($row['fecha_inicio']);
                }
            } catch (\Exception $e) {
                $inicioContrato = now();
            }
        }

        // CREAR SIEMPRE NUEVO (Porque vamos a limpiar la tabla antes)
        return new Customer([
            'identification' => $cedula,
            'first_name'    => $row['nombres'] ?? 'Cliente',
            'last_name'     => $row['apellidos'] ?? 'Vilcanet',
            'email'         => $row['email'] ?? null,
            'plan'          => $row['plan'] ?? 'Estándar', // <--- LEEMOS EL PLAN
            'phone'         => $row['telefono'] ?? null,
            'address'       => $row['direccion'] ?? null,
            'contract_start_date' => $inicioContrato,
            'password'      => Hash::make($pass),
            'points'        => 0, // <--- INICIA EN CERO
            'streak_count'  => 0
        ]);
    }
}