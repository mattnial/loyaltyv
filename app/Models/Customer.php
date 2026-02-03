<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    // Aseguramos que apunte a la tabla 'customers'
    protected $table = 'customers';

    // Campos que permitimos modificar
    protected $fillable = [
        'identification', // Cédula (VITAL para buscar)
        'first_name',
        'last_name',
        'email',
        'plan',
        'phone',
        'address',
        'password',
        'points',
        'streak_count',
        'last_payment_date',
        'birth_date',
        'contract_start_date'
    ];

    // Configuración de tipos de datos
    protected $casts = [
        'points' => 'integer',
        'streak_count' => 'integer',
        'last_payment_date' => 'datetime',
        'birth_date' => 'date',
        'contract_start_date' => 'date'
    ];
}