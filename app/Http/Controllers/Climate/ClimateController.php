<?php

namespace App\Http\Controllers\Climate;

use App\Http\Controllers\Controller;
use App\Models\Climate;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\isEmpty;

class ClimateController extends Controller
{
    //read temperature

    public function temperature(Request $request)
    {
        try {
            $type = $request->type;
            $start = $request->startDate;
            $end = $request->endDate;
            $time = $request->time;

            if ($type == "%") { //all data
                Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'User request all temperature data');
                $readTemperature = Climate::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as day, ROUND(AVG(temperature),2) as temperature")
                    ->groupBy('day')
                    ->get();
            } else if ($type == "daily") {
                Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'User request in between' . $start . ' and ' . $end . ' temperature data');

                $readTemperature = Climate::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as day, ROUND(AVG(temperature), 2) as temperature")
                    ->whereBetween('created_at', [$start, $end])
                    ->groupBy('day')
                    ->get();
            } else if ($type == "monthly") {
                $start = substr($start, 0, 7); //remove day
                Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'User request monthly temperature data');
                $readTemperature = Climate::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as day, ROUND(AVG(temperature), 2) as temperature")
                    ->where('created_at', 'LIKE', '%' . $start . '%')
                    ->groupBy('day')
                    ->get();
            } else if ($type == "yearly") {
                $start = substr($start, 0, 5); //keep year
                Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'User request yearly temperature data');
                $readTemperature = Climate::selectRaw("DATE_FORMAT(created_at, '%Y') as day, ROUND(AVG(temperature), 2) as temperature")
                    ->where('created_at', 'LIKE', '%' . $start . '%')
                    ->groupBy('day')
                    ->get();
            } else {
                Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'User request invalid type of time period in temperature reports');
                return response()->json(["message" => "Invalid type", "status" => 404]);
            }
            $temperature = array();
            foreach ($readTemperature as $result) {
                $temperatureData = [
                    "temperature" => $result->temperature,
                    "created_at" => $result->day
                ];
                array_push($temperature, $temperatureData);
            }
            if ($temperature == null) {
                Log::channel('custom')->warning(Auth::user()->email . ':climate:' . 'No temperature data found');
                return response()->json(['message' => 'No temperature data found'], 404);
            }
            Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'Show  historical temperature successfully');
            return $temperature;
        } catch (Exception $exception) {
            Log::channel('custom')->error(Auth::user()->email . ':climate:' . $exception->getMessage());
            return response()->json(["message" => $exception->getMessage(), "status" => 406]);
        }
    }
    //read humidity
    public function humidity(Request $request)
    {
        try {
            $type = $request->type;
            $start = $request->startDate;
            $end = $request->endDate;
            if ($type == "%") {
                Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'User request all humidity data');
                $readHumidity = Climate::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as day, ROUND(AVG(humidity),2) as humidity")
                    ->groupBy('day')
                    ->get();
            } else if ($type == "daily") {
                Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'User request in between' . $start . ' and ' . $end . ' humidity data');
                $readHumidity = Climate::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as day, ROUND(AVG(humidity), 2) as humidity")
                    ->whereBetween('created_at', [$start, $end])
                    ->groupBy('day')
                    ->get();
            } else if ($type == "monthly") {
                $start = substr($start, 0, 7); //remove day
                Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'User request monthly humidity data');
                $readHumidity = Climate::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as day, ROUND(AVG(humidity), 2) as humidity")
                    ->where('created_at', 'LIKE', '%' . $start . '%')
                    ->groupBy('day')
                    ->get();
            } else if ($type == "yearly") {
                $start = substr($start, 0, 5); //keep year
                Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'User request monthly humidity data');
                $readHumidity = Climate::selectRaw("DATE_FORMAT(created_at, '%Y') as day, ROUND(AVG(humidity), 2) as humidity")
                    ->where('created_at', 'LIKE', '%' . $start . '%')
                    ->groupBy('day')
                    ->get();
            } else {
                Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'User request invalid type of time period in humidity reports');

                return response()->json(["message" => "Invalid type", "status" => 404]);
            }

            $humidity = array();
            foreach ($readHumidity as $result) {
                $humidityData = [
                    "humidity" => $result->humidity,
                    "created_at" => $result->day,
                ];
                array_push($humidity, $humidityData);
            }
            if ($humidity == null) {
                Log::channel('custom')->warning(Auth::user()->email . ':climate:' . 'No humidity data found');
                return response()->json(['message' => 'No Humidity data found'], 404);
            }
            Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'Show  historical humidity data successfully');

            return $humidity;
        } catch (Exception $exception) {
            Log::channel('custom')->error(Auth::user()->email . ':climate:' . $exception->getMessage());
            return response()->json(["message" => $exception->getMessage(), "status" => 406]);
        }
    }

    //read both temperature and humidity
    public function bothTemHumidity(Request $request)
    {
        try {
            $climate = array();
            $type = $request->type;
            $start = $request->startDate;
            $end = $request->endDate;

            if ($type == "%") { //all
                Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'User request both temperature and humidity data');
                $bothClimate = Climate::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as day,ROUND(AVG(humidity), 2) as humidity, ROUND(AVG(temperature), 2) as temperature")
                    ->groupBy('day')
                    ->get();
            } else if ($type == "daily") {
                Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'User request in between' . $start . ' and ' . $end . ' both temperature and humidity data');
                $bothClimate = Climate::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as day, ROUND(AVG(temperature), 2) as temperature,ROUND(AVG(humidity), 2) as humidity")
                    ->whereBetween('created_at', [$start, $end])
                    ->groupBy('day')
                    ->get();
            } else if ($type == "monthly") {
                $start = substr($start, 0, 7);
                Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'User request in monthly both temperature and humidity data');
                $bothClimate = Climate::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as day, ROUND(AVG(temperature), 2) as temperature,ROUND(AVG(humidity), 2) as humidity")
                    // ->where('created_at', 'LIKE', '%' . $start . '%')
                    ->groupBy('day')
                    ->get();
            } else if ($type == "yearly") {
                $start = substr($start, 0, 5); //keep year
                Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'User request in monthly both temperature and humidity data');
                $bothClimate = Climate::selectRaw("DATE_FORMAT(created_at, '%Y') as day, ROUND(AVG(temperature), 2) as temperature,ROUND(AVG(humidity), 2) as humidity")
                    ->where('created_at', 'LIKE', '%' . $start . '%')
                    ->groupBy('day')
                    ->get();
            } else {
                Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'User request invalid type of time period in both temperature and humidity reports');
                return response()->json(["message" => "Invalid type", "status" => 404]);
            }
            foreach ($bothClimate as $result) {
                $climateData = [
                    "temperature" => $result->temperature,
                    "humidity" => $result->humidity,
                    "created_at" => $result->day
                ];
                array_push($climate, $climateData);
            }
            if ($climate == null) {
                Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'Not available both temperature and humidity data');
                return response()->json(['message' => 'No Humidity data found'], 404);
            }
            Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'Show  historical both temperature and humidity data');
            return $climate;
        } catch (Exception $exception) {
            Log::channel('custom')->error(Auth::user()->email . ':climate:' . 'Both climate data: ' . $exception->getMessage());
            return response()->json(["message" => $exception->getMessage(), "status" => 406]);
        }
    }

    //in a daily , monthly
    public function highestNumberOFHumidityRecord(Request $request)
    {
        try {
            $type = $request->type;
            $year = $request->startDate;
            if ($type == "daily") {
                $start = substr($year, 0, 5); //keep year
                $humidityResult = Climate::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') AS month, MAX(humidity) as max_humidity")
                    ->where('created_at', 'LIKE', '%' . $start . '%')
                    ->groupBy('month')->orderBy('month')->get();
                Log::channel('custom')->error(Auth::user()->email . ':climate:' . 'User request highest number of humidity in a daily');
            }else if($type == "weekly"){
                $temperatureResults = Climate::selectRaw("YEAR(created_at) AS year, WEEK(created_at) AS week, MAX(humidity) AS humidity")
                ->where('created_at', 'LIKE', '%' . $start . '%')
                ->groupBy('year', 'week')
                ->orderBy('year')
                ->orderBy('week')
                ->get();
                Log::channel('custom')->error(Auth::user()->email . ':climate:' . 'User request highest number of humidity in a week');
            } else if ($type == "monthly") {
                $start = substr($year, 0, 5); //keep year
                $humidityResult = Climate::selectRaw("DATE_FORMAT(created_at, '%Y-%m') AS month, MAX(humidity) as max_humidity")
                    ->where('created_at', 'LIKE', '%' . $start . '%')
                    ->groupBy('month')->orderBy('month')->get();
                Log::channel('custom')->error(Auth::user()->email . ':climate:' . 'User request highest number of humidity in a month');
            } else {
                Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'User request invalid type of time period in highest number of humidity reports');
                return response()->json(["message" => "Invalid type", "status" => 404]);
            }
            return $humidityResult;
        } catch (Exception $exception) {
            Log::channel('custom')->error(Auth::user()->email . ':climate:' . $exception->getMessage());
            return response()->json(["message" => $exception->getMessage(), "status" => 406]);
        }
    }

    //in a daily monthly
    public function highestNumberOFTemperatureRecord(Request $request)
    {
        try {
            $type = $request->type;
            $year = $request->startDate;
            $start = substr($year, 0, 5); //keep year
            if ($type == "daily") {
                $temperatureResults = Climate::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') AS month, MAX(temperature) as temperature")
                    ->where('created_at', 'LIKE', '%' . $start . '%')
                    ->groupBy('month')->orderBy('month')->get();
                Log::channel('custom')->error(Auth::user()->email . ':climate:' . 'User request highest number of temperature in a month');
            } else if($type == "weekly"){
                $temperatureResults = Climate::selectRaw("YEAR(created_at) AS year, WEEK(created_at) AS week, MAX(temperature) AS temperature")
                ->where('created_at', 'LIKE', '%' . $start . '%')
                ->groupBy('year', 'week')
                ->orderBy('year')
                ->orderBy('week')
                ->get();
            } else if ($type == "monthly") {
                $temperatureResults = Climate::selectRaw("DATE_FORMAT(created_at, '%Y-%m') AS month, MAX(temperature) as temperature")
                    ->where('created_at', 'LIKE', '%' . $start . '%')
                    ->groupBy('month')->orderBy('month')->get();
                Log::channel('custom')->error(Auth::user()->email . ':climate:' . 'User request highest number of temperature in a month');
            } else {
                Log::channel('custom')->info(Auth::user()->email . ':climate:' . 'User request invalid type of time period in highest number of temperature reports');
                return response()->json(["message" => "Invalid type", "status" => 404]);
            }
            return $temperatureResults;
        } catch (Exception $exception) {
            Log::channel('custom')->error(Auth::user()->email . ':climate:' . $exception->getMessage());
            return response()->json(["message" => $exception->getMessage(), "status" => 406]);
        }
    }
}
