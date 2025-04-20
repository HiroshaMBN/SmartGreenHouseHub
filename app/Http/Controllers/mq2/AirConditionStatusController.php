<?php

namespace App\Http\Controllers\mq2;

use App\Http\Controllers\Controller;
use App\Models\airCondition;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AirConditionStatusController extends Controller
{
  //read air quality
  public function mq2Co2(Request $request)
  {

    try {
      //   $airQualityData = airCondition::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') AS day , ROUND(AVG(value),2) as value")
      //   ->groupBy('day')->orderBy('day')->get();
      // Log::channel('custom')->info(Auth::user()->email . ':airQuality:' . 'User request all air quality data');
      $type = $request->type;
      $start = $request->startDate;
      $end = $request->endDate;


      if ($type == "%") { //all data by day
        Log::channel('custom')->info(Auth::user()->email . ':airCondition:' . 'User request all air quality data');
        $readAirCondition = airCondition::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as day, ROUND(AVG(value),2) as value")
          ->groupBy('day')
          ->get();
      } else if ($type == "daily") {
        Log::channel('custom')->info(Auth::user()->email . ':airCondition:' . 'User request in between' . $start . ' and ' . $end . ' air quality data');
        $readAirCondition = airCondition::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as day, ROUND(AVG(value), 2) as value")
          ->whereBetween('created_at', [$start, $end])
          ->groupBy('day')
          ->get();
      } else if ($type == "monthly") {
        $start = substr($start, 0, 7); //remove day
        Log::channel('custom')->info(Auth::user()->email . ':airCondition:' . 'User request monthly air quality data');
        $readAirCondition = airCondition::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as day, ROUND(AVG(value), 2) as value")
          ->where('created_at', 'LIKE', '%' . $start . '%')
          ->groupBy('day')
          ->get();
      } else if ($type == "yearly") {
        $start = substr($start, 0, 5); //keep year
        Log::channel('custom')->info(Auth::user()->email . ':airCondition:' . 'User request yearly air quality data');
        $readAirCondition = airCondition::selectRaw("DATE_FORMAT(created_at, '%Y') as day, ROUND(AVG(value), 2) as value")
          ->where('created_at', 'LIKE', '%' . $start . '%')
          ->groupBy('day')
          ->get();
      } else {
        Log::channel('custom')->info(Auth::user()->email . ':airCondition:' . 'User request invalid type of time period in air quality reports');
        return response()->json(["message" => "Invalid type", "status" => 404]);
      }
      $airQuality = array();
      foreach ($readAirCondition as $result) {
        $airQualityData = [
          "air" => $result->value,
          "day" => $result->day
        ];
        array_push($airQuality, $airQualityData);
      }
      if ($airQuality == null) {
        Log::channel('custom')->warning(Auth::user()->email . ':airCondition:' . 'No air quality data found');
        return response()->json(['message' => 'No air quality data found', "status" => 404]);
      }
      Log::channel('custom')->info(Auth::user()->email . ':airCondition:' . 'Show  historical air quality successfully');
      return $airQuality;
    } catch (Exception $exception) {
      Log::channel('custom')->info(Auth::user()->email . ':airQuality:' . $exception->getMessage());
      return response()->json(['message' => $exception->getMessage()], 500);
    }
  }
}
