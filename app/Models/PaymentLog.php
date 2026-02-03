<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    use HasFactory;

    protected $table = 'payment_logs';

    // CAMPOS PERMITIDOS PARA GUARDAR (¡Esto es lo que faltaba!)
    protected $fillable = [
        'cedula',
        'client_name',
        'amount',
        'payment_date',
        'invoice_num',
        'plan',
        'is_processed'
    ];

    // Para que las fechas se manejen automáticamente
    protected $casts = [
        'payment_date' => 'datetime',
        'is_processed' => 'boolean'
    ];
}