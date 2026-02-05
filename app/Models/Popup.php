<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Popup extends Model
{
    use HasFactory;

    // 👇 AGREGA ESTO PARA DAR PERMISO DE GUARDADO
    protected $fillable = [
        'title',
        'image_path',
        'is_active'
    ];
}