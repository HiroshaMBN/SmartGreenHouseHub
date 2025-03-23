<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class notificationActivation extends Model
{
    use HasFactory;
    // protected $fillable = ['sensor_name', 'normal','warning','critical','is_enable_notify'];
    protected $fillable = ['is_normal','is_warning','is_critical'];

}
