<?php

namespace App\Domains\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternetPlan extends Model
{
    use SoftDeletes;

    protected $table = 'internet_plans';

    protected $fillable = [
        'name',
        'price',
        'download_speed_kbps',
        'upload_speed_kbps',
        'mikrotik_profile_name',
        'is_active'
    ];

    // Helper para mostrar velocidad legible (Ej: convierte 102400 a "100 Mbps")
    public function getFormattedSpeedAttribute()
    {
        return round($this->download_speed_kbps / 1024) . ' Mbps';
    }
}