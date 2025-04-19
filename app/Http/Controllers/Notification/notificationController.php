<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\Climate;
use App\Models\thresholds;
use App\Models\airCondition;
use App\Models\soilMoisture;
use App\Models\notificationActivation;
use App\Models\notification;
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

  //read all notifications
  public function readNotification(Request $request)
  {
    $validator = validator::make($request->all(), [
      "type" => "string",
      "startDate" => "required",
      "endDate" => "required"
    ]);
    if ($validator->fails()) {
      return response()->json(["message" => $validator->errors()->all(), "status" => 406]);
    }

    // $results = notification::where('type', $request->type)
    // ->where('created_at', 'LIKE %', $request->startDate.'%')
    // ->where('created_at', 'LIKE %', $request->endDate.'%') // exclusive upper bound
    // ->get();
    if ($request->type == "%") {
      $results = Notification::get();
    } else {
      $results = Notification::where('type', $request->type)
        ->where('created_at', '>=', $request->startDate)
        ->where('created_at', '<=', $request->endDate) // exclusive upper bound
        ->get();
    }

    $resultArray = array();
    foreach ($results as $result) {
      $data = [
        "user_id" => $result->user_id,
        "message" => $result->message,
        "type" => $result->Type,
        "time" => $result->created_at
      ];
      array_push($resultArray, $data);
    }
    return response()->json(["message" => $resultArray, "status" => 200]);
  }

  //send temperature notification 
  public function sendTemperatureNotification()
  {
    $thresholdResult = thresholds::where('sensor_name', '=', "dht11-tmp")->get();
    $temperatureResult = Climate::latest()->first();
    $threshold = thresholds::where('sensor_name', 'dht11-tmp')->first();

    //  return  $temperatureResult;

    foreach ($thresholdResult as $result) {
      $isNormal = $result->is_normal;
      $isWarning = $result->is_warning;
      $isCritical = $result->is_critical;
      $normalThreshold = $result->normal;
      $warningThreshold = $result->warning;
      $criticalThreshold = $result->critical;
      $notifyCount = $result->count;
      $stopLimit = $result->stop_limit;
      $isEnableNotify = $result->is_enable_notify;
    }
    if($isEnableNotify == 0){
      return response()->json(["message"=>"Notifications not enabled","status"=>404]);
      exit;
    }

    if ($temperatureResult->temperature >= $normalThreshold && $temperatureResult->temperature < $warningThreshold) {
      if ($isNormal == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            echo "send sms normal";
          } else if ($notifyType == "email") {
            echo "send  emails normal";
          } else {
            echo "send both sms and emails normal";
          }
          $threshold->increment('count');
        } else if ($stopLimit == $notifyCount) {
          echo ("Notification limit exceeded.Never send notifications to users.");
        }
      }
    } else if ($temperatureResult->humidity <= $normalThreshold) {
      if ($stopLimit == $notifyCount) {
        echo ("Notification limit exceeded.Never send notifications to users.");
      } else {
        $threshold->increment('count');
        echo "Temperature level too much low. Please take action";
      }
    }


    if ($temperatureResult->temperature >= $warningThreshold && $temperatureResult->temperature < $criticalThreshold) {
      if ($isWarning == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            echo "send sms warn";
          } else if ($notifyType == "email") {
            echo "send  emails warn";
          } else {
            echo "send both sms and emails warn";
          }
          $threshold->increment('count');
        } else if ($stopLimit == $notifyCount) {
          echo ("Notification limit exceeded.Never send notifications to users.");
        }
      }
    }


    if ($temperatureResult->temperature >= $criticalThreshold) {
      if ($isCritical == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            echo "send sms critical";
          } else if ($notifyType == "email") {
            echo "send  emails critical";
          } else {
            echo "send both sms and emails critical";
          }
          $threshold->increment('count');
        } else if ($stopLimit == $notifyCount) {
          echo ("Notification limit exceeded.Never send notifications to users.");
        }
      }
    }
  }

  //send humidity notifications
  public function sendHumidityNotification()
  {
    $thresholdResult = thresholds::where('sensor_name', '=', "dht11-humid")->get();
    $humidityResult = Climate::latest()->first();
    $threshold = thresholds::where('sensor_name', 'dht11-humid')->first();

    //  print_r($thresholdResult);


    foreach ($thresholdResult as $result) {
      $isNormal = $result->is_normal;
      $isWarning = $result->is_warning;
      $isCritical = $result->is_critical;
      $normalThreshold = $result->normal;
      $warningThreshold = $result->warning;
      $criticalThreshold = $result->critical;
      $notifyCount = $result->count;
      $stopLimit = $result->stop_limit;
      $isEnableNotify = $result->is_enable_notify;
    }
    if($isEnableNotify == 0){
      return response()->json(["message"=>"Notifications not enabled","status"=>404]);
      exit;
    }

    if ($humidityResult->humidity >= $normalThreshold && $humidityResult->humidity < $warningThreshold) {
      if ($isNormal == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            echo "send sms normal";
          } else if ($notifyType == "email") {
            echo "send  emails normal";
          } else {
            echo "send both sms and emails normal";
          }
          $threshold->increment('count');
        } else if ($stopLimit == $notifyCount) {
          echo ("Notification limit exceeded.Never send notifications to users.");
        }
      }
    } else if ($humidityResult->humidity <= $normalThreshold) {
      if ($stopLimit == $notifyCount) {
        echo ("Notification limit exceeded.Never send notifications to users.");
      } else {
        $threshold->increment('count');
        echo "Humidity level too much low. Please take action";
      }
    }


    if ($humidityResult->humidity >= $warningThreshold && $humidityResult->humidity < $criticalThreshold) {
      if ($isWarning == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            echo "send sms warn";
          } else if ($notifyType == "email") {
            echo "send  emails warn";
          } else {
            echo "send both sms and emails warn";
          }
          $threshold->increment('count');
        } else if ($stopLimit == $notifyCount) {
          echo ("Notification limit exceeded.Never send notifications to users.");
        }
      }
    }


    if ($humidityResult->humidity >= $criticalThreshold) {
      if ($isCritical == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            echo "send sms critical";
          } else if ($notifyType == "email") {
            echo "send  emails critical";
          } else {
            echo "send both sms and emails critical";
          }
          $threshold->increment('count');
        } else if ($stopLimit == $notifyCount) {
          echo ("Notification limit exceeded.Never send notifications to users.");
        }
      }
    }
  }

  //send air quality notifications
  public function airQualityNotification()
  {
    $thresholdResult = thresholds::where('sensor_name', '=', "mq2")->get();
    $airQualityResult = airCondition::latest()->first();
    $threshold = thresholds::where('sensor_name', 'mq2')->first();


    foreach ($thresholdResult as $result) {
      $isNormal = $result->is_normal;
      $isWarning = $result->is_warning;
      $isCritical = $result->is_critical;
      $normalThreshold = $result->normal;
      $warningThreshold = $result->warning;
      $criticalThreshold = $result->critical;
      $notifyCount = $result->count;
      $stopLimit = $result->stop_limit;
      $isEnableNotify = $result->is_enable_notify;
    }
    if($isEnableNotify == 0){
      return response()->json(["message"=>"Notifications not enabled","status"=>404]);
      exit;
    }
    if ($airQualityResult->value <= $criticalThreshold) {
      if ($isCritical == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            echo "send sms critical";
          } else if ($notifyType == "email") {
            echo "send  emails critical";
          } else {
            echo "send both sms and emails critical";
          }
          $threshold->increment('count');
        } else if ($stopLimit == $notifyCount) {
          echo ("Notification limit exceeded.Never send notifications to users.");
        }
      }
    } else if ($airQualityResult->value <= $warningThreshold && $airQualityResult->value < $normalThreshold) {
      if ($isWarning == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            echo "send sms warn";
          } else if ($notifyType == "email") {
            echo "send  emails warn";
          } else {
            echo "send both sms and emails warn";
          }
          $threshold->increment('count');
        } else if ($stopLimit == $notifyCount) {
          echo ("Notification limit exceeded.Never send notifications to users.");
        }
      }
    } else {
      if ($isNormal == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            echo "send sms normal";
          } else if ($notifyType == "email") {
            echo "send  emails normal";
          } else {
            echo "send both sms and emails normal";
          }
          $threshold->increment('count');
        } else if ($stopLimit == $notifyCount) {
          echo ("Notification limit exceeded.Never send notifications to users.");
        }
      }
    }
  }

  //send soil level notifications
  public function soilQualityNotification()
  {
    $thresholdResult = thresholds::where('sensor_name', '=', "soil")->get();
    $airQualityResult = soilMoisture::latest()->first();
    $threshold = thresholds::where('sensor_name', 'soil')->first();

    foreach ($thresholdResult as $result) {
      $isNormal = $result->is_normal;
      $isWarning = $result->is_warning;
      $isCritical = $result->is_critical;
      $normalThreshold = $result->normal;
      $warningThreshold = $result->warning;
      $criticalThreshold = $result->critical;
      $notifyCount = $result->count;
      $stopLimit = $result->stop_limit;
      $notifyType = $result->notify_type;
      $isEnableNotify = $result->is_enable_notify;
    }
    if($isEnableNotify == 0){
      return response()->json(["message"=>"Notifications not enabled","status"=>404]);
      exit;
    }

    if ($airQualityResult->Level >= $normalThreshold && $airQualityResult->Level <= $warningThreshold) {

      if ($isCritical == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            echo "send sms normal";
          } else if ($notifyType == "email") {
            echo "send  emails normal";
          } else {
            echo "send both sms and emails normal";
          }
          $threshold->increment('count');
        } else if ($stopLimit == $notifyCount) {
          echo ("Notification limit exceeded.Never send notifications to users.");
        }
      }
    } else if ($airQualityResult->Level >= $warningThreshold && $airQualityResult->Level < $criticalThreshold) {
      if ($isWarning == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            echo "send sms warn";
          } else if ($notifyType == "email") {
            echo "send  emails warn";
          } else {
            echo "send both sms and emails warn";
          }
          $threshold->increment('count');
        } else if ($stopLimit == $notifyCount) {
          echo ("Notification limit exceeded.Never send notifications to users.");
        }
      }
    } else if ($airQualityResult->Level >= $criticalThreshold) {
      if ($isNormal == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            echo "send sms critical";
          } else if ($notifyType == "email") {
            echo "send  emails critical";
          } else {
            echo "send both sms and emails critical";
          }
          $threshold->increment('count');
        } else if ($stopLimit == $notifyCount) {
          echo ("Notification limit exceeded.Never send notifications to users.");
        }
      }
    } else {
      echo "Soil wet too much";
    }
  }
}
