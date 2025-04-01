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
            $date = $request->date;
            $time = $request->time;
            if ($type == "%") {
                $dateAndTime = $date . ' ' . $time;
                $readTemperature = Climate::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as day, ROUND(AVG(temperature),2) as temperature")
                    ->groupBy('day')
                    ->get();
            } else {
                $readTemperature = Climate::where('created_at', 'LIKE', "%$date%")->get();
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
                Log::channel('custom')->warning(Auth::user()->email . ':Reading Temperature:' . 'No temperature data found');
                return response()->json(['message' => 'No temperature data found'], 404);
            }
            Log::channel('custom')->info(Auth::user()->email . ':Reading Temperature:' . 'Reading Historical Temperature');
            return $temperature;
        } catch (Exception $exception) {
            Log::channel('custom')->error(Auth::user()->email . ':Reading Temperature:' . $exception->getMessage());
        }
    }
    //read humidity
    public function humidity(Request $request)
    {

        try {
            $type = $request->type;
            $date = $request->date;
            $time = $request->time;

            // $readTemperature = Climate::all();
            // $readTemperature = Climate::where('created_at',$date)->get();
            //% is required both date and time
            if ($type == "%") {
                $dateAndTime = $date . ' ' . $time;
                $readHumidity = Climate::where('created_at', 'LIKE', "%$dateAndTime%")->get();
            } else {
                $readHumidity = Climate::where('created_at', 'LIKE', "%$date%")->get();
            }
            $humidity = array();
            foreach ($readHumidity as $result) {
                // array_push($temperature, $result->temperature, $result->created_at);
                $humidityData = [
                    "humidity" => $result->humidity,
                    "created_at" => Carbon::parse($result->created_at)->format('Y-m-d H:i:s')
                ];
                array_push($humidity, $humidityData);
            }
            if ($humidity == null) {
                Log::channel('custom')->warning(Auth::user()->email . ':Reading Humidity:' . 'No humidity data found');
                return response()->json(['message' => 'No Humidity data found'], 404);
            }
            Log::channel('custom')->info(Auth::user()->email . ':Reading Humidity:' . 'Reading Historical humidity');

            return $humidity;
        } catch (Exception $exception) {
            Log::channel('custom')->error(Auth::user()->email . ':Reading Humidity:' . $exception->getMessage());
        }
    }

    //read both temperature and humidity
    public function bothTemHumidity(Request $request)
    {

        try {
            $type = $request->type;
            $date = $request->date;
            $time = $request->time;

            // $readTemperature = Climate::all();
            // $readTemperature = Climate::where('created_at',$date)->get();
            //% is required both date and time
            if ($type == "%") {
                $dateAndTime = $date . ' ' . $time;
                $tempHumidity = Climate::where('created_at', 'LIKE', "%$dateAndTime%")->get();
            } else {
                $tempHumidity = Climate::where('created_at', 'LIKE', "%$date%")->get();
            }
            $climate = array();
            foreach ($tempHumidity as $result) {
                // array_push($temperature, $result->temperature, $result->created_at);
                $climateData = [
                    "temperature" => $result->temperature,
                    "humidity" => $result->humidity,
                    "created_at" => Carbon::parse($result->created_at)->format('Y-m-d H:i:s')
                ];
                array_push($climate, $climateData);
            }
            if ($climate == null) {
                Log::channel('custom')->warning(Auth::user()->email . ':Reading Humidity:' . 'No climate data found');
                return response()->json(['message' => 'No Humidity data found'], 404);
            }
            Log::channel('custom')->info(Auth::user()->email . ':Reading Humidity:' . 'Reading historical climate');

            return $climate;
        } catch (Exception $exception) {
            Log::channel('custom')->error(Auth::user()->email . ':Reading Humidity:' . $exception->getMessage());
        }
    }

    //in a monthly
    public function highestNumberOFHumidityRecord()
    {
        //         SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, MAX(humidity) as maximum_humidity
        // FROM climates
        // GROUP BY month
        // ORDER BY month;

        // $tempHumidity = Climate::where('created_at', 'LIKE', "%$date%")->get();
        $humidityResult = Climate::selectRaw("DATE_FORMAT(created_at, '%Y-%m') AS month, MAX(humidity) as max_humidity")
            ->groupBy('month')->orderBy('month')->get();
        return $humidityResult;
    }





    //get sensor data
    public function index()
    {
        return response()->json(Climate::all());
    }
}
