<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class objects extends Model
{
    use HasFactory;
    protected $fillable = ['object_id','object_controller_id','instance_id','value','delay','status'];
}
