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
      $type = $request->type;
      $start = $request->startDate;
      $end = $request->endDate;
      $time = $request->time;
      $airQuality = array();

      if ($type == "%") {
        $airQualityData = airCondition::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') AS day , ROUND(AVG(value),2) as value")
          ->groupBy('day')->orderBy('day')->get();
        Log::channel('custom')->info(Auth::user()->email . ':airQuality:' . 'User request all air quality data');
      } else {
        Log::channel('custom')->info(Auth::user()->email . ':airQuality:' . 'User request in between' . $start . ' and ' . $end . ' air quality data');
        $airQualityData = airCondition::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as day, ROUND(AVG(value),2) as value")
          ->whereBetween('created_at', [$start, $end])
          ->groupBy('day')
          ->get();
      }
      foreach ($airQualityData as $result) {
        $data = [
          "value" => $result->value,
          "day" => $result->day
        ];
        array_push($airQuality, $data);
      }
      Log::channel('custom')->info(Auth::user()->email . ':airQuality:' . 'Reading historical air quality data successfully');
      return $airQuality;
    } catch (Exception $exception) {
      Log::channel('custom')->info(Auth::user()->email . ':airQuality:' . $exception->getMessage());
      return response()->json(['message' => $exception->getMessage()], 500);
    }
  }
}
