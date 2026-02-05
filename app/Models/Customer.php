<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // <--- 1. IMPORTAR ESTO

class Customer extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'identification',
        'email',
        'fcm_token',
        'phone',
        'address',
        'password',
        'status',
        'points',
        'streak_count',
        'plan'
    ];

    protected $hidden = [
        'password',
        'remember_token',
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