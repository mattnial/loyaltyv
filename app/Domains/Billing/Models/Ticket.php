<?php

namespace App\Domains\Billing\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'customer_id', 
        'subject', 
        'description', 
        'status', 
        'priority', 
        'admin_response'
    ];

    // Relación: Un ticket pertenece a un Cliente
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}