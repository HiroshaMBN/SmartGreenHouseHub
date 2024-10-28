<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sensor_controller extends Model
{
    use HasFactory;
    protected $fillable = ['sensor_controller_id','sensor_id','instance_id','name','display_name','status'];
}
