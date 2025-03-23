<?php

namespace App\Http\Controllers\SoilMoisture;

use App\Http\Controllers\Controller;
use App\Models\soilMoisture;
use Illuminate\Http\Request;

class SoilLevelController extends Controller
{
    //get soil moisture level
    public function SoilMoistureLevel(){
     return response()->json(soilMoisture::all());

    }
}
