<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class seeds extends Model
{
    use HasFactory;
    protected $fillable = ["seed_name","availability","unit_type","next_stock_date","unit_price","total_price","stock_level"];

}
