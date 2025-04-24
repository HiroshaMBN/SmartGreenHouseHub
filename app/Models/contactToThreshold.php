<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class contactToThreshold extends Model
{
    use HasFactory;
    protected $fillable =['id','contact_id','threshold_id'];

}
