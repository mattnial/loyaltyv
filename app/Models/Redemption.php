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
        'points_spent',      // Ojo: Asegúrate que tu migración use este nombre. Si antes era 'points_spent', cámbialo aquí.
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