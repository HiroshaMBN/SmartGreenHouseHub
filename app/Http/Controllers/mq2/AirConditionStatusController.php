<?php

namespace App\Http\Controllers\mq2;

use App\Http\Controllers\Controller;
use App\Models\airCondition;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;

class AirConditionStatusController extends Controller
{
    //read air quality
    public function mq2Co2(Request $request){

      try{
        // SELECT DATE_FORMAT(created_at, '%Y-%m-%d') AS day , AVG(value) FROM air_conditions GROUP BY day ORDER BY day;
        $airQualityData = airCondition::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') AS day , AVG(value) as value")
        ->groupBy('day')->orderBy('day')->get();
        return $airQualityData;
      }catch(Exception $exception){
        return response()->json(['message' => $exception->getMessage()], 500);  
      }

      // $airQuality = array();
      // $result = airCondition::all();
      // foreach ($result as $value) {
// SELECT  DATE_FORMAT(created_at, '%Y-%m') AS month , AVG(value) 
// FROM air_conditions
// GROUP BY month
// ORDER BY month;

// $humidityResult = Climate::selectRaw("DATE_FORMAT(created_at, '%Y-%m') AS month, MAX(humidity) as max_humidity")
// ->groupBy('month')->orderBy('month')->get();
// return $humidityResult;










      //   $airQualityData = [
      //     "value" => $value->value,
      //     "created_at" => Carbon::parse($value->created_at)->format('Y-m-d H:i:s')
      //   ];
      //   array_push($airQuality, $airQualityData);
      // }


    }
}
