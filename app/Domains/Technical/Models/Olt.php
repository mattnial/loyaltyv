<?php

namespace App\Domains\Technical\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @project: ISP-App-Ecuador
 * @module: Technical/Infrastructure
 * @file: Olt.php
 * @status: PRODUCTION
 */
class Olt extends Model
{
    use SoftDeletes;

    // Apuntamos explícitamente a la tabla
    protected $table = 'olts';

    protected $fillable = [
        'name',
        'ip_address',
        'driver',
        'telnet_port',
        'snmp_port',
        'admin_user',
        'admin_password',
        'snmp_community',
        'is_active',
        'extra_config'
    ];

    /**
     * MAGIA DE LARAVEL:
     * Al poner 'encrypted', Laravel encriptará automáticamente al guardar en BD
     * y desencriptará al leer. ¡Nunca verás la clave plana en la DB!
     */
    protected $casts = [
        'admin_password' => 'encrypted',
        'extra_config' => 'array',
        'is_active' => 'boolean',
    ];
}