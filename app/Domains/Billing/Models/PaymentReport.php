<?php

namespace App\Domains\Billing\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentReport extends Model
{
    // Permitimos que estos campos se guarden masivamente
    protected $fillable = [
        'customer_id', 
        'invoice_number', 
        'amount', 
        'payment_method', 
        'proof_image_path', 
        'status',
        'admin_note'
    ];

    // Relación: Un reporte de pago pertenece a un Cliente
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}