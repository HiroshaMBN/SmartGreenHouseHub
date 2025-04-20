<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class thresholds extends Model
{
    use HasFactory;
    protected $fillable = ['sensor_name', 'normal','warning','critical','is_enable_notify','is_normal','is_warning','is_critical','stop_limit','notify_type','count'];

}
