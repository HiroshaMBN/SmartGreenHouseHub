<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\Climate;
use App\Models\thresholds;
use App\Models\notificationActivation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
class notificationController extends Controller
{
  //alert for temperature
  public function temperatureAlert()
  {
    try {
      $tempArray = array();
      $temperatureResult = Climate::get();
      $thresholdResult = thresholds::where('sensor_name', 'LIKE', "%dht11%")->get();
      foreach ($thresholdResult as $result) {
        $normal = $result->normal;
        $warning = $result->warning;
        $critical = $result->critical;
        $is_notify = $result->is_enable_notify;
      }
      foreach ($temperatureResult as $result) {
        $data = [
          "time" => $result->created_at,
          "value" => $result->temperature,
          "sensor" => "Temperature"
        ];
        if ($is_notify == 0) { //turn on / off all notification at once , if 1 send related notifications to users
          return response()->json(["message" => "Temperature Notifications turn off"]);
        }

        if ($result->temperature <= $normal) { //normal
          $data = [
            "status" => "Normal",
            "time" => $result->created_at,
            "sensor" => "Temperature"
          ];
          array_push($tempArray, $data);
        } else if ($result->temperature > $normal && $result->temperature <= $warning) { //warning       
          $data = [
            "status" => "Warning",
            "time" => $result->created_at,
            "sensor" => "Temperature"
          ];
          array_push($tempArray, $data);
        } else if ($result->temperature > $critical) { //critical
          $data = [
            "status" => "Critical",
            "time" => $result->created_at,
            "sensor" => "Temperature"
          ];
          array_push($tempArray, $data);
        }
      }
      return $tempArray;
    } catch (Exception  $exception) {
      return response()->json(["message" => $exception->getMessage(), "status" => 401]);
    }
  }

  //enable temperature notification
  public function enableNotifications(Request $request)
  {

    try {
      $validator = validator::make($request->all(), [
        'is_enable_notify' => 'required|integer',
        'sensor_name' => 'required'
      ]);
      if ($validator->fails()) {
        return response()->json(["message" => $validator->errors()->all(), 'status' => 406]);
      }
      $update = thresholds::where('sensor_name', $request->sensor_name)->get();
      foreach ($update as $result) {
        $check_activation = $result->is_enable_notify;
      }
      if ($check_activation == (int)$request->is_enable_notify) {
        if ((int)$request->is_enable_notify == 0) {
          return response()->json(["message" => "Temperature notification already disable"]);
        } else {
          return response()->json(["message" => "Temperature notification already enabled"]);
        }
      }

      $update = thresholds::where('sensor_name', $request->sensor_name)->update(['is_enable_notify' => (int)$request->is_enable_notify]);
      return response()->json(["message" => "Notification Update", "status" => 200]);
    } catch (Exception $exception) {
      return response()->json(["message" => $exception->getMessage(), "status" => 406]);
    }
  }
}
