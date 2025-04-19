<?php

namespace App\Http\Controllers\SoilMoisture;

use App\Http\Controllers\Controller;
use App\Models\soilMoisture;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SoilLevelController extends Controller
{
  //get soil moisture level
  public function SoilMoistureLevel(Request $request)
  {
    try {
      $type = $request->type;
      $start = $request->startDate;
      $end = $request->endDate;
      if ($type == "%") { //all data by day
        Log::channel('custom')->info(Auth::user()->email . ':soilMoisture:' . 'User request all soil level data');
        $readSoilMoisture = soilMoisture::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as day, ROUND(AVG(Level),2) as soil")
          ->groupBy('day')
          ->get();
          // print_r($readSoilMoisture);die();
      } else if ($type == "daily") {
        Log::channel('custom')->info(Auth::user()->email . ':soilMoisture:' . 'User request in between' . $start . ' and ' . $end . ' soil level data');
        $readSoilMoisture = soilMoisture::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as day, ROUND(AVG(Level), 2) as soil")
          ->whereBetween('created_at', [$start, $end])
          ->groupBy('day')
          ->get();
      } else if ($type == "monthly") {
        $start = substr($start, 0, 7); //remove day
        Log::channel('custom')->info(Auth::user()->email . ':soilMoisture:' . 'User request monthly soil level data');
        $readSoilMoisture = soilMoisture::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as day, ROUND(AVG(Level), 2) as soil")
          ->where('created_at', 'LIKE', '%' . $start . '%')
          ->groupBy('day')
          ->get();
      } else if ($type == "yearly") {
        $start = substr($start, 0, 5); //keep year
        Log::channel('custom')->info(Auth::user()->email . ':soilMoisture:' . 'User request yearly soil level data');
        $readSoilMoisture = soilMoisture::selectRaw("DATE_FORMAT(created_at, '%Y') as day, ROUND(AVG(Level), 2) as soil")
          ->where('created_at', 'LIKE', '%' . $start . '%')
          ->groupBy('day')
          ->get();
      } else {
        Log::channel('custom')->info(Auth::user()->email . ':soilMoisture:' . 'User request invalid type of time period in soil level reports');
        return response()->json(["message" => "Invalid type", "status" => 404]);
      }
      $soil = array();
   
      foreach ($readSoilMoisture as $result) {
        $soilData = [
          "soil" => $result->soil,
          "created_at" => $result->day
        ];
        array_push($soil, $soilData);
      }
      if ($soil == null) {
        Log::channel('custom')->warning(Auth::user()->email . ':soilMoisture:' . 'No soil data found');
        return response()->json(['message' => 'No soil data found', "status" => 404]);
      }
      Log::channel('custom')->info(Auth::user()->email . ':soilMoisture:' . 'Show  historical soil successfully');
      return $soil;
    } catch (Exception $exception) {
      Log::channel('custom')->error(Auth::user()->email . ':soilMoisture:' . $exception->getMessage());
      return response()->json(["message" => $exception->getMessage(), "status" => 406]);
    }
  }
}
