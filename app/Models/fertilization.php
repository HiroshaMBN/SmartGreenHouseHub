<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class fertilization extends Model
{
    use HasFactory;
    protected $fillable = ["fertilize_name","availability","unit_type","next_stock_date","qty","unit_price","total_price","stock_level"];
}
