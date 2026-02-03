<?php

namespace App\Domains\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domains\Technical\Models\Olt;

class Subscription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'internet_plan_id',
        'olt_id',
        'service_ip',
        'pppoe_user',
        'pppoe_password',
        'onu_serial',
        'onu_index',
        'wifi_ssid',
        'wifi_password',
        'status',
        'installation_date'
    ];

    // --- Relaciones ---

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function plan()
    {
        return $this->belongsTo(InternetPlan::class, 'internet_plan_id');
    }

    public function olt()
    {
        return $this->belongsTo(Olt::class);
    }

    // --- Scopes (Filtros Rápidos) ---
    
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }
}