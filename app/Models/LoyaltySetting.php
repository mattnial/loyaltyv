<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltySetting extends Model
{
    use HasFactory;

    protected $table = 'loyalty_settings';

    protected $fillable = [
        'points_per_payment',
        'payment_start_day',
        'payment_end_day',
        'double_points_start',
        'double_points_end',
        'points_birthday',
        'points_anniversary',
        'points_christmas'
    ];
}