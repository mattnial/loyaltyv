<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StreakMilestone extends Model
{
    use HasFactory;

    protected $table = 'streak_milestones';

    protected $fillable = [
        'months_required',
        'bonus_points'
    ];
}