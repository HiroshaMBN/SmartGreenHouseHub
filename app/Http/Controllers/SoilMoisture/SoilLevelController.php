<?php

namespace App\Http\Controllers\SoilMoisture;

use App\Http\Controllers\Controller;
use App\Models\soilMoisture;
use Exception;
use Illuminate\Http\Request;


class SoilLevelController extends Controller
{
  //get soil moisture level
  public function SoilMoistureLevel(Request $request)
  {
    try {
      $type = $request->type;
      $date = $request->date;
   

      //following sql is support for find maximum number of average of soil status 
      // SELECT MAX(avg_level) AS maxAvg
      // FROM (
      //     SELECT AVG(Level) AS avg_level
      //     FROM soil_moistures
      //     GROUP BY DATE(created_at)  
      // ) AS avg_values;
      // Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'User request all temperature data');
      $readTemperature = soilMoisture::selectRaw("ROUND(AVG(Level),2) AS level,DATE_FORMAT(created_at, '%Y-%m-%d') as day")
          ->groupBy('day')
          ->get();

      // $readTemperature = Climate::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as day, ROUND(AVG(temperature), 2) as temperature")
      // ->whereBetween('created_at', [$start, $end])
      // ->groupBy('day')
      // ->get();


      // $readSoilLevel = soilMoisture::selectRaw('ROUND(AVG(Level), 2) AS level, MAX(Level) as maxAvg ,DATE(created_at) AS day')
      //   ->where('created_at', 'LIKE', '%2025-03%')
      //   ->groupByRaw('DATE(created_at)')
      //   ->orderBy('created_date', 'asc')
      //   ->get();

      return $readTemperature;


      // SELECT ROUND(AVG(Level), 2) AS level, DATE(created_at) AS created_date FROM `soil_moistures`
      //  WHERE created_at LIKE "%" GROUP BY created_date ORDER BY created_date DESC; 
    } catch (Exception $exception) {
      return response()->json(["message" => $exception->getMessage(), "status" => 406]);
    }
    //  return response()->json(soilMoisture::all());

  }
}
