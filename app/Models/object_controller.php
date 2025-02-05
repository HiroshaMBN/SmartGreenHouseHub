<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class object_controller extends Model
{
    use HasFactory;

    protected $fillable = ['object_Controller_id','instance_id','name','display_name','status'];
}
