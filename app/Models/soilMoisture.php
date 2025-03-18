<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class soilMoisture extends Model
{
    use HasFactory;
    protected $fillable =['sensor_id','sensor_controller_id','instance_id','Level','status'];
}
