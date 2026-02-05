<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'cost',         // El nombre real en la BD
        'stock',
        'image_path',   // Agregamos este
        'is_featured',  // Agregamos este
        'is_active'
    ];
}