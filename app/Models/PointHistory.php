<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'user_id', // <--- NUEVO
        'type',    // 'earn' o 'spend'
        'points',
        'description'
    ];

    // Relación con el usuario (empleado) que hizo el movimiento
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con el cliente
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // ACCESOR MÁGICO: Para mostrar el nombre en la vista fácilmente
    // Uso: $history->author_name (Devuelve "SISTEMA" o "Juan Tecnico")
    public function getAuthorNameAttribute()
    {
        return $this->user ? $this->user->name : 'SISTEMA 🤖';
    }
}