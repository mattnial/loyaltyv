<?php

namespace App\Domains\Billing\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyClient extends Model
{
    protected $connection = 'bonus'; 
    protected $table = 'clients'; 
    public $timestamps = false;
}