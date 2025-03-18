<?php

namespace App\Http\Controllers\mq2;

use App\Http\Controllers\Controller;
use App\Models\airCondition;
use Illuminate\Http\Request;

class AirConditionStatusController extends Controller
{
    //read air quality
    public function mq2Co2(){
        // return response()->json(airCondition::all());

    }
}
