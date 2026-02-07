<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'reward_id',
        'reward_name',   // <--- ¡FALTABA ESTE! Sin esto, Laravel borra el nombre antes de guardar.
        'points_spent',  // <--- Este es el nombre correcto de tu base de datos.
        'status',           // pending, approved, completed, rejected
        'pickup_branch',    // Loja, Vilcabamba, Palanda
        'proof_photo_path', // La foto de la entrega
        'admin_note'
    ];

    // RELACIÓN 1: El Cliente que canjeó
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // RELACIÓN 2: El Premio que se llevó
    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }
    
    // AYUDAS VISUALES (Para que el panel se vea bonito)
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending'   => 'yellow',
            'approved'  => 'blue',
            'completed' => 'green',
            'rejected'  => 'red',
            default     => 'gray'
        };
    }
}