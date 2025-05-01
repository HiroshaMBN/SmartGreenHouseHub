<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\Climate;
use App\Models\thresholds;
use App\Models\airCondition;
use App\Models\soilMoisture;
use App\Models\notificationActivation;
use App\Models\notification;
use App\Models\contactToThreshold;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Mail\NotificationEmail;
use Illuminate\Support\Facades\Mail;

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
        "user_id" => $result->user,
        "message" => $result->message,
        "type" => $result->Type,
        "time" => $result->created_at,
        "mobile" => $result->mobile,
        "email" => $result->email,
      ];
      array_push($resultArray, $data);
    }
    return response()->json(["message" => $resultArray, "status" => 200]);
  }
  public function userNotifications($sensorName)
  {
    $contactDetails = array();

    $contacts = DB::table('contacts')
      ->select('contacts.mobile', 'contacts.email', 'users.first_name', 'thresholds.sensor_name')
      ->join('users', 'contacts.user_id', '=', 'users.id')
      ->join('contact_to_thresholds', 'contact_to_thresholds.contact_id', '=', 'contacts.user_id')
      ->join('thresholds', 'thresholds.id', '=', 'contact_to_thresholds.threshold_id')
      ->where('thresholds.sensor_name', '=', $sensorName)
      ->get();

    foreach ($contacts as $result) {
      // Add the mobile number to the array
      $contactDetails[] = [
        "mobile" => $result->mobile,
        "email" => $result->email,
        "user_name" => $result->first_name
      ];
    }
    return $contactDetails;
  }
  public function filterContactToThreshold($sensor_name)
  {
    $contactToThreshold = DB::table('contacts')
      ->join('contact_to_thresholds', 'contact_to_thresholds.contact_id', '=', 'contacts.contact_id')
      ->join('thresholds', 'thresholds.id', '=', 'contact_to_thresholds.threshold_id')
      // ->join('users', 'users.id', '=', 'contact_to_thresholds.threshold_id')
      ->select('contacts.mobile', 'contacts.email', 'contacts.user_id')
      ->where('thresholds.sensor_name', '=', $sensor_name)
      ->get();
    return $contactToThreshold;
  }
  //sms
  public function sendNotificationUsingGammuSmsService()
  {



    $phoneNumber = escapeshellarg('+94755557130');
    $message = escapeshellarg("SMS by Laravel");
    $scriptPath = '/var/www/html/SmartGreenHouseHub/config/send_sms.sh';
    $command = "/bin/bash $scriptPath $phoneNumber $message";

    $output = null;
    $resultCode = null;
    exec($command, $output, $resultCode);

    Log::channel('custom')->info("Gammu SMS Service" . ':Notification: ' . implode("\n", $output) . " " . $resultCode);
  }

  public function sendNotificationEmailService()
  {
    $user = "malithhirosha@gmail.com";
    $details = [
      'name' => 'User Name',
      'message' => 'This is a test message.'
    ];

    // Send the email
    Mail::to($user)->send(new NotificationEmail($details));

    return response()->json(['message' => 'Email sent successfully!']);
  }

  //send temperature notification 
  public function sendTemperatureNotification()
  {
    $thresholdResult = thresholds::where('sensor_name', '=', "dht11-tmp")->get();
    $temperatureResult = Climate::latest()->first();
    $threshold = thresholds::where('sensor_name', 'dht11-tmp')->first();

    $userNotificationClass = new notificationController();
    $contactDetails = $userNotificationClass->userNotifications('dht11-tmp');
    $contactToThreshold = $userNotificationClass->filterContactToThreshold('dht11-tmp');


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
      $notifyType = $result->notify_type;
    }
    if ($isEnableNotify == 0) {
      return response()->json(["message" => "Notifications not enabled", "status" => 404]);
      exit;
    }

    if ($temperatureResult->temperature >= $normalThreshold && $temperatureResult->temperature < $warningThreshold) {
      if ($isNormal == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            // foreach ($contactDetails as $result) {
            //   $threshold->increment('count');
            //   Log::channel('custom')->info("System Generated" . ':Notification:' . $result['mobile'] . 'Send SMS of Temperature(Normal) - Successfully');
            // }
            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['first_name'],
                  "mobile" => $result->mobile,
                  "message" => "Send SMS of Temperature(Normal) - Successfully",
                  "Type" => "SMS"
                ]);
                // Log::channel('custom')->info("System Generated" . ':Notification:' . $result['mobile'] . ' Send SMS of Temperature(Normal) - Successfully');
                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                // var_dump($result);die();
                $threshold->increment('count');
                notification::create([
                  // "user" => $result->first_name,
                  "mobile" => $result->mobile,
                  "message" => "Send SMS of Temperature(Normal) - Successfully",
                  "Type" => "SMS"
                ]);
                // Log::channel('custom')->info("System Generated" . ':Notification:' . $result['mobile'] . ' Send SMS of Temperature(Normal) - Successfully');

              }
            }
            return true;
          } else if ($notifyType == "email") {
            // foreach ($contactDetails as $result) {
            //   $threshold->increment('count');
            //   Log::channel('custom')->info("System Generated" . ':Notification:' . $result['email'] . 'Send E-mail of Temperature(Normal) - Successfully');
            // }
            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['first_name'],
                  "email" => $result->email,
                  "message" => "Send E-mail of Temperature(Normal) - Successfully",
                  "Type" => "Email"
                ]);
                // Log::channel('custom')->info("System Generated" . ':Notification:' . $result['email'] . ' Send E-mail of Temperature(Normal) - Successfully');
                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['first_name'],
                  "email" => $result->email,
                  "message" => "Send SMS of Temperature(Normal) - Successfully",
                  "Type" => "Email"
                ]);
                // Log::channel('custom')->info("System Generated" . ':Notification:' . $result['email'] . ' Send SMS of Temperature(Normal) - Successfully');

              }
            }

            return true;
          } else {
            foreach ($contactToThreshold as $result) {
              // foreach ($contactDetails as $result) {
              //   $threshold->increment('count');
              //   Log::channel('custom')->info("System Generated" . ':Notification:' . $result['mobile'] . " & " . $result['email'] . 'Send both E-mail and SMS of Temperature(Normal) - Successfully');
              // }
              if (($notifyCount + 1) == $stopLimit) {
                $threshold->increment('count');
                foreach ($contactToThreshold as $result) {
                  notification::create([
                    // "user" => $result['first_name'],
                    "mobile" => $result->mobile,
                    "email" => $result->email,
                    "message" => "Send both SMS & E-mail of Temperature(Normal) - Successfully",
                    "Type" => "SMS & Email"
                  ]);
                  break;
                }
              } else {
                foreach ($contactToThreshold as $result) {
                  $threshold->increment('count');
                  notification::create([
                    // "user" => $result['first_name'],
                    "mobile" => $result->mobile,
                    "email" => $result->email,
                    "message" => "Send both SMS & E-mail Temperature(Normal) - Successfully",
                    "Type" => "SMS & Email"
                  ]);
                }
              }
            }
            return true;
          }
        } else if ($stopLimit == $notifyCount) {
          Log::channel('custom')->info("System Generated" . ':Notification:' . 'Notification limit exceeded.Never send notifications to users(Normal)');
          return true;
        }
      }
    } else if ($temperatureResult->temperature <= $normalThreshold) {
      if ($stopLimit == $notifyCount) {
        Log::channel('custom')->info("System Generated" . ':Notification:' . 'Notification limit exceeded.Never send notifications to users(Normal)');
        return true;
      } else {
        $threshold->increment('count');
        Log::channel('custom')->info("System Generated" . ':Notification:' . 'Temperature level too much low. Please take action(Normal)');
        return true;
      }
    }


    if ($temperatureResult->temperature >= $warningThreshold && $temperatureResult->temperature < $criticalThreshold) {
      if ($isWarning == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['first_name'],
                  "mobile" => $result->mobile,
                  "message" => "Send SMS of Temperature(Warning) - Successfully",
                  "Type" => "SMS"
                ]);
                // Log::channel('custom')->info("System Generated" . ':Notification:' . $result['mobile'] . ' Send SMS of Temperature(Warning) - Successfully');
                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['first_name'],
                  "mobile" => $result->mobile,
                  "message" => "Send SMS of Temperature(Warning) - Successfully",
                  "Type" => "SMS"
                ]);
                // Log::channel('custom')->info("System Generated" . ':Notification:' . $result['mobile'] . ' Send SMS of Temperature(Warning) - Successfully');

              }
            }

            return true;
          } else if ($notifyType == "email") {

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['first_name'],
                  "email" => $result->email,
                  "message" => "Send E-mail of Temperature(Warning) - Successfully",
                  "Type" => "Email"
                ]);
                // Log::channel('custom')->info("System Generated" . ':Notification:' . $result['email'] . ' Send E-mail of Temperature(Warning) - Successfully');
                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['first_name'],
                  "email" => $result->email,
                  "message" => "Send E-mail of Temperature(Warning) - Successfully",
                  "Type" => "Email"
                ]);
                // Log::channel('custom')->info("System Generated" . ':Notification:' . $result['email'] . ' Send E-mail of Temperature(Warning) - Successfully');
              }
            }

            return true;
          } else {

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['first_name'],
                  "mobile" => $result->mobile,
                  "email" => $result->email,
                  "message" => "Send both SMS & E-mail of Temperature(Critical) - Successfully",
                  "Type" => "SMS & Email"
                ]);
                //Log::channel('custom')->info("System Generated" . ':Notification:' . $result['mobile'] . " & " . $result['email'] . ' Send Both SMS & E-mail of Temperature(Warning) - Successfully');
                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['first_name'],
                  "mobile" => $result->mobile,
                  "email" => $result->email,
                  "message" => "Send both SMS & E-mail of Temperature(Critical) - Successfully",
                  "Type" => "SMS & Email"
                ]);
                //   // Log::channel('custom')->info("System Generated" . ':Notification:' . $result['mobile'] . " & " . $result['email'] . ' Send Both SMS & E-mail of Temperature(Warning) - Successfully');

              }
            }

            return true;
          }
        } else if ($stopLimit == $notifyCount) {
          Log::channel('custom')->info("System Generated" . ':Notification:' . 'Notification limit exceeded.Never send notifications to users(Warning).');
          return true;
        }
      }
    }




    if ($temperatureResult->temperature >= $criticalThreshold) {
      if ($isCritical == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            // foreach ($contactDetails as $result) {
            //   $threshold->increment('count');
            //   Log::channel('custom')->info("System Generated" . ':Notification: ' . $result['mobile'] . ' Send SMS of Temperature(Critical) - Successfully');
            // }


            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['first_name'],
                  "email" => $result->email,
                  "message" => "Send SMS of Temperature(Critical) - Successfully",
                  "Type" => "SMS"
                ]);
                //Log::channel('custom')->info("System Generated" . ':Notification:' . $result['mobile'] . " & " . $result['email'] . ' Send Both SMS & E-mail of Temperature(Warning) - Successfully');
                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['first_name'],
                  "mobile" => $result->mobile,
                  "message" => "Send SMS of Temperature(Critical) - Successfully",
                  "Type" => "SMS"
                ]);
                //   // Log::channel('custom')->info("System Generated" . ':Notification:' . $result['mobile'] . " & " . $result['email'] . ' Send Both SMS & E-mail of Temperature(Warning) - Successfully');

              }
            }
            return true;
          } else if ($notifyType == "email") {
            // foreach ($contactDetails as $result) {
            //   $threshold->increment('count');
            //   Log::channel('custom')->info("System Generated" . ':Notification: ' . $result['email'] . ' Send E-mail of Temperature(Critical) - Successfully');
            // }

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['first_name'],
                  "email" => $result->email,
                  "message" => "Send E-mail of Temperature(Critical) - Successfully",
                  "Type" => "Email"
                ]);
                //Log::channel('custom')->info("System Generated" . ':Notification:' . $result['mobile'] . " & " . $result['email'] . ' Send Both SMS & E-mail of Temperature(Warning) - Successfully');
                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['first_name'],
                  "email" => $result->email,
                  "message" => "Send E-mail of Temperature(Critical) - Successfully",
                  "Type" => "Email"
                ]);
                //   // Log::channel('custom')->info("System Generated" . ':Notification:' . $result['mobile'] . " & " . $result['email'] . ' Send Both SMS & E-mail of Temperature(Warning) - Successfully');

              }
            }





            return true;
          } else {
            // foreach ($contactDetails as $result) {

            //   Log::channel('custom')->info("System Generated" . ':Notification:' . $result['mobile'] . " & " . $result['email'] . 'Send Both SMS & E-mail of Temperature(Critical) - Successfully');
            //   $threshold->increment('count');
            // }

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['first_name'],
                  "mobile" => $result->mobile,
                  "email" => $result->email,
                  "message" => "Send both SMS & E-mail of Temperature(Critical) - Successfully",
                  "Type" => "SMS & Email"
                ]);
                //Log::channel('custom')->info("System Generated" . ':Notification:' . $result['mobile'] . " & " . $result['email'] . ' Send Both SMS & E-mail of Temperature(Warning) - Successfully');
                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['first_name'],
                  "mobile" => $result->mobile,
                  "email" => $result->email,
                  "message" => "Send both SMS & E-mail of Temperature(Critical) - Successfully",
                  "Type" => "SMS & Email"
                ]);
                //   // Log::channel('custom')->info("System Generated" . ':Notification:' . $result['mobile'] . " & " . $result['email'] . ' Send Both SMS & E-mail of Temperature(Warning) - Successfully');

              }
            }

            return true;
          }
        } else if ($stopLimit == $notifyCount) {
          Log::channel('custom')->info("System Generated" . ':Notification:' . 'Temperature Notification limit exceeded.Never send notifications to users');
          return true;
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
    $userNotificationClass = new notificationController();
    $contactDetails = $userNotificationClass->userNotifications('dht11-humid');
    $contactToThreshold = $userNotificationClass->filterContactToThreshold('dht11-humid');


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
      $notifyType = $result->notify_type;
    }
    if ($isEnableNotify == 0) {
      return response()->json(["message" => "Notifications not enabled", "status" => 404]);
      exit;
    }

    if ($humidityResult->humidity >= $normalThreshold && $humidityResult->humidity < $warningThreshold) {
      if ($isNormal == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send SMS of Humidity(Normal) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,
                  // "email" => $result->email,
                  "message" => "Send SMS of Humidity(Normal) - Successfully",
                  "Type" => "SMS"
                ]);
                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,
                  "message" => "Send SMS of Humidity(Normal) - Successfully",
                  "Type" => "SMS"
                ]);
              }
            }
            return true;
          } else if ($notifyType == "email") {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send E-mail of Humidity(Normal) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  "mobile" => $result->email,
                  "message" => "Send E-mail of Humidity(Normal) - Successfully",
                  "Type" => "Email"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->email,
                  "message" => "Send E-mail of Humidity(Normal) - Successfully",
                  "Type" => "Email"
                ]);
              }
            }



            return true;
          } else {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send Both SMS & E-mail of Humidity(Normal) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,
                  "mobile" => $result->email,
                  "message" => "Send both SMS & E-mail of Humidity(Normal) - Successfully",
                  "Type" => "SMS & Email"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,
                  "mobile" => $result->email,
                  "message" => "Send both SMS & E-mail of Humidity(Normal) - Successfully",
                  "Type" => "SMS & Email"
                ]);
              }
            }
            return true;
          }
          $threshold->increment('count');
        } else if ($stopLimit == $notifyCount) {
          Log::channel('custom')->info("System Generated" . ':Notification:' . 'Notification limit exceeded.Never send notifications to users');
          return true;
        }
      }
    } else if ($humidityResult->humidity <= $normalThreshold) {
      if ($stopLimit == $notifyCount) {
        Log::channel('custom')->info("System Generated" . ':Notification:' . 'Notification limit exceeded.Never send notifications to users.');
        return true;
      } else {
        //alert 
        Log::channel('custom')->info("System Generated" . ':Notification:' . 'Humidity level too much low. Please take action');
        return true;
      }
    }


    if ($humidityResult->humidity >= $warningThreshold && $humidityResult->humidity < $criticalThreshold) {
      if ($isWarning == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send SMS of Humidity(Warning) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,
                  "message" => "Send both SMS & E-mail of Humidity(Warning) - Successfully",
                  "Type" => "SMS"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,
                  "message" => "Send both SMS & E-mail of Humidity(Warning) - Successfully",
                  "Type" => "SMS"
                ]);
              }
            }
            return true;
          } else if ($notifyType == "email") {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send SMS of Humidity(Warning) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->email,
                  "message" => "Send E-mail of Humidity(Warning) - Successfully",
                  "Type" => "Email"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  "mobile" => $result->email,
                  "message" => "Send E-mail of Humidity(Warning) - Successfully",
                  "Type" => "Email"
                ]);
              }
            }
            return true;
          } else {
            Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send SMS of Humidity(Warning) - Successfully');
            $threshold->increment('count');
            return true;
          }
          $threshold->increment('count');
        } else if ($stopLimit == $notifyCount) {
          Log::channel('custom')->info("System Generated" . ':Notification:' . 'Notification limit exceeded.Never send notifications to users.');
          return true;
        }
      }
    }


    if ($humidityResult->humidity >= $criticalThreshold) {
      if ($isCritical == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send SMS of Humidity(Critical) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,

                  "message" => "Send SMS of Humidity(Critical) - Successfully",
                  "Type" => "SMS"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,
                  "message" => "Send SMS of Humidity(Critical) - Successfully",
                  "Type" => "SMS"
                ]);
              }
            }
            return true;
          } else if ($notifyType == "email") {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send E-mail of Humidity(Critical) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  "user" => $result['user_name'],
                  "mobile" => $result->email,
                  "message" => "Send E-mail of Humidity(Critical) - Successfully",
                  "Type" => "Email"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  "user" => $result['user_name'],
                  "mobile" => $result->email,
                  "message" => "Send E-mail of Humidity(Critical) - Successfully",
                  "Type" => "Email"
                ]);
              }
            }
            return true;
          } else {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send Both SMS & E-mail of Humidity(Critical) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  "mobile" => $result->mobile,
                  "mobile" => $result->email,
                  "message" => "Send both SMS & E-mail of Humidity(Critical) - Successfully",
                  "Type" => "SMS & Email"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  "mobile" => $result->mobile,
                  "mobile" => $result->email,
                  "message" => "Send both SMS & E-mail of Humidity(Critical) - Successfully",
                  "Type" => "SMS & Email"
                ]);
              }
            }

            return true;
          }
          $threshold->increment('count');
        } else if ($stopLimit == $notifyCount) {
          Log::channel('custom')->info("System Generated" . ':Notification:' . 'Notification limit exceeded.Never send notifications to users.');
          $threshold->increment('count');
          return true;
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

    $userNotificationClass = new notificationController();
    $contactDetails = $userNotificationClass->userNotifications('mq2');
    $contactToThreshold = $userNotificationClass->filterContactToThreshold('mq2');

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
      $notifyType = $result->notify_type;
    }
    if ($isEnableNotify == 0) {
      return response()->json(["message" => "Notifications not enabled", "status" => 404]);
      exit;
    }
    if ($airQualityResult->value <= $criticalThreshold) {
      if ($isCritical == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send SMS of Air Quality(Critical) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  "mobile" => $result->mobile,
                  "message" => "Send SMS of Air Quality(Critical) - Successfully",
                  "Type" => "SMS"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  "mobile" => $result->mobile,
                  "message" => "Send SMS of Air Quality(Critical) - Successfully",
                  "Type" => "SMS"
                ]);
              }
            }
            return true;
          } else if ($notifyType == "email") {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send E-mail of Air Quality(Critical) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  "email" => $result->email,
                  "message" => "Send E-mail of Air Quality(Critical) - Successfully",
                  "Type" => "Email"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  "email" => $result->email,
                  "message" => "Send E-mail of Air Quality(Critical) - Successfully",
                  "Type" => "Email"
                ]);
              }
            }

            return true;
          } else {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send Both SMS & E-mail of Air Quality(Critical) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  "mobile" => $result->mobile,
                  "email" => $result->email,
                  "message" => "Send both SMS & E-mail of Air Quality(Critical) - Successfully",
                  "Type" => "SMS & Email"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  "mobile" => $result->mobile,
                  "email" => $result->email,
                  "message" => "Send both SMS & E-mail of Air Quality(Critical) - Successfully",
                  "Type" => "SMS & Email"
                ]);
              }
            }
            return true;
          };
        } else if ($stopLimit == $notifyCount) {
          echo ("Notification limit exceeded.Never send notifications to users.");
        }
      }
    } else if ($airQualityResult->value <= $warningThreshold && $airQualityResult->value < $normalThreshold) {
      if ($isWarning == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send SMS of Air Quality(Warning) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,
                  "message" => "Send SMS of Air Quality(Warning) - Successfully",
                  "Type" => "SMS"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,
                  "message" => "Send SMS of Air Quality(Warning) - Successfully",
                  "Type" => "SMS"
                ]);
              }
            }
            return true;
          } else if ($notifyType == "email") {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send E-mail of Air Quality(Warning) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  "mobile" => $result->email,
                  "message" => "Send E-mail of Air Quality(Warning) - Successfully",
                  "Type" => "Email"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->email,
                  "message" => "Send E-mail of Air Quality(Warning) - Successfully",
                  "Type" => "Email"
                ]);
              }
            }
            return true;
          } else {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send Both SMS & E-mail of Air Quality(Warning) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  "mobile" => $result->mobile,
                  "email" => $result->email,
                  "message" => "Send both SMS & E-mail of Air Quality(Warning) - Successfully",
                  "Type" => "SMS & Email"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  "mobile" => $result->mobile,
                  "email" => $result->email,
                  "message" => "Send both SMS & E-mail of Air Quality(Warning) - Successfully",
                  "Type" => "SMS & Email"
                ]);
              }
            }
            return true;
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
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send SMS of Air Quality(Normal) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  "mobile" => $result->mobile,
                  "message" => "Send SMS of Air Quality(Normal) - Successfully",
                  "Type" => "SMS"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  "mobile" => $result->mobile,
                  "message" => "Send SMS of Air Quality(Normal) - Successfully",
                  "Type" => "SMS"
                ]);
              }
            }
            return true;
          } else if ($notifyType == "email") {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send E-mail of Air Quality(Normal) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['user_name'],
                  "email" => $result->email,
                  "message" => "Send E-mail of Air Quality(Normal) - Successfully",
                  "Type" => "Email"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['user_name'],
                  "email" => $result->email,
                  "message" => "Send E-mail of Air Quality(Normal) - Successfully",
                  "Type" => "Email"
                ]);
              }
            }
            return true;;
          } else {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send Both SMS & E-mail of Air Quality(Normal) - Successfully');
            // $threshold->increment('count');
            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,
                  "email" => $result->email,
                  "message" => "Send both SMS & E-mail of Air Quality(Normal) - Successfully",
                  "Type" => "SMS & Email"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  "mobile" => $result->mobile,
                  "email" => $result->email,
                  "message" => "Send both SMS & E-mail of Air Quality(Normal) - Successfully",
                  "Type" => "SMS & Email"
                ]);
              }
            }
            return true;;
          }
          $threshold->increment('count');
        } else if ($stopLimit == $notifyCount) {
          echo ("Notification limit exceeded.Never send notifications to users.");
        }
      }
    }
  }

  //send soil level notifications
  // public function soilQualityNotification()
  // {
  //   $thresholdResult = thresholds::where('sensor_name', '=', "soil")->get();
  //   $airQualityResult = soilMoisture::latest()->first();
  //   $threshold = thresholds::where('sensor_name', 'soil')->first();
  //   $userNotificationClass = new notificationController();
  //   $contactDetails = $userNotificationClass->userNotifications('soil');

  //   foreach ($thresholdResult as $result) {
  //     $isNormal = $result->is_normal;
  //     $isWarning = $result->is_warning;
  //     $isCritical = $result->is_critical;
  //     $normalThreshold = $result->normal;
  //     $warningThreshold = $result->warning;
  //     $criticalThreshold = $result->critical;
  //     $notifyCount = $result->count;
  //     $stopLimit = $result->stop_limit;
  //     $notifyType = $result->notify_type;
  //     $isEnableNotify = $result->is_enable_notify;
  //     $notifyType = $result->notify_type;
  //   }
  //   if ($isEnableNotify == 0) {
  //     return response()->json(["message" => "Notifications not enabled", "status" => 404]);
  //     exit;
  //   }

  //   if ($airQualityResult->Level >= $normalThreshold && $airQualityResult->Level <= $warningThreshold) {

  //     if ($isCritical == 1) {
  //       if ($stopLimit > $notifyCount) {
  //         if ($notifyType == "sms") {
  //           // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send SMS of Soil Status(Normal) - Successfully');
  //           // $threshold->increment('count');

  //           if (($notifyCount + 1) == $stopLimit) {
  //             $threshold->increment('count');
  //             foreach ($contactDetails as $result) {
  //               notification::create([
  //                 "user" => $result['user_name'],
  //                 "mobile" => $result['mobile'],
  //                 "message" => "Send SMS of Air Quality(Normal) - Successfully",
  //                 "Type" => "SMS"
  //               ]);

  //               break;
  //             }
  //           } else {
  //             foreach ($contactDetails as $result) {
  //               $threshold->increment('count');
  //               notification::create([
  //                 "user" => $result['user_name'],
  //                 "mobile" => $result['mobile'],
  //                 "message" => "Send SMS of Air Quality(Normal) - Successfully",
  //                 "Type" => "SMS"
  //               ]);
  //             }
  //           }
  //           return true;
  //         } else if ($notifyType == "email") {
  //           // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send E-mail of Soil Status(Normal) - Successfully');
  //           // $threshold->increment('count');

  //           if (($notifyCount + 1) == $stopLimit) {
  //             $threshold->increment('count');
  //             foreach ($contactDetails as $result) {
  //               notification::create([
  //                 "user" => $result['user_name'],
  //                 "email" => $result['email'],
  //                 "message" => "Send E-mail of Air Quality(Normal) - Successfully",
  //                 "Type" => "Email"
  //               ]);

  //               break;
  //             }
  //           } else {
  //             foreach ($contactDetails as $result) {
  //               $threshold->increment('count');
  //               notification::create([
  //                 "user" => $result['user_name'],
  //                 "email" => $result['email'],
  //                 "message" => "Send E-mail of Air Quality(Normal) - Successfully",
  //                 "Type" => "Email"
  //               ]);
  //             }
  //           }
  //           return true;
  //         } else {
  //           // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send both SMS & E-mail of Air Quality(Normal) - Successfully');
  //           // $threshold->increment('count');

  //           if (($notifyCount + 1) == $stopLimit) {
  //             $threshold->increment('count');
  //             foreach ($contactDetails as $result) {
  //               notification::create([
  //                 "user" => $result['user_name'],
  //                 "mobile" => $result['mobile'],
  //                 "email" => $result['email'],
  //                 "message" => "Send both SMS & E-mail of Air Quality(Normal) - Successfully",
  //                 "Type" => "SMS & Email"
  //               ]);

  //               break;
  //             }
  //           } else {
  //             foreach ($contactDetails as $result) {
  //               $threshold->increment('count');
  //               notification::create([
  //                 "user" => $result['user_name'],
  //                 "mobile" => $result['mobile'],
  //                 "email" => $result['email'],
  //                 "message" => "Send both SMS & E-mail of Air Quality(Normal) - Successfully",
  //                 "Type" => "SMS & Email"
  //               ]);
  //             }
  //           }
  //           return true;
  //         }
  //       } else if ($stopLimit == $notifyCount) {
  //         Log::channel('custom')->info("System Generated" . ':Notification:' . 'Notification limit exceeded.Never send notifications to users.');
  //         return true;
  //       }
  //     }
  //   } else if ($airQualityResult->Level >= $warningThreshold && $airQualityResult->Level < $criticalThreshold) {
  //     if ($isWarning == 1) {
  //       if ($stopLimit > $notifyCount) {
  //         if ($notifyType == "sms") {
  //           // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send SMS of Soil Status(Warning) - Successfully');
  //           // $threshold->increment('count');

  //           if (($notifyCount + 1) == $stopLimit) {
  //             $threshold->increment('count');
  //             foreach ($contactDetails as $result) {
  //               notification::create([
  //                 "user" => $result['user_name'],
  //                 "mobile" => $result['mobile'],
  //                 "message" => "Send SMS of Air Quality(Warning) - Successfully",
  //                 "Type" => "SMS"
  //               ]);

  //               break;
  //             }
  //           } else {
  //             foreach ($contactDetails as $result) {
  //               $threshold->increment('count');
  //               notification::create([
  //                 "user" => $result['user_name'],
  //                 "mobile" => $result['mobile'],
  //                 "email" => $result['email'],
  //                 "message" => "Send SMS of Air Quality(Warning) - Successfully",
  //                 "Type" => "SMS"
  //               ]);
  //             }
  //           }
  //           return true;
  //         } else if ($notifyType == "email") {
  //           // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send E-mail of Soil Status(Warning) - Successfully');
  //           // $threshold->increment('count');

  //           if (($notifyCount + 1) == $stopLimit) {
  //             $threshold->increment('count');
  //             foreach ($contactDetails as $result) {
  //               notification::create([
  //                 "user" => $result['user_name'],
  //                 "email" => $result['email'],
  //                 "message" => "Send E-mail of Air Quality(Warning) - Successfully",
  //                 "Type" => "Email"
  //               ]);

  //               break;
  //             }
  //           } else {
  //             foreach ($contactDetails as $result) {
  //               $threshold->increment('count');
  //               notification::create([
  //                 "user" => $result['user_name'],
  //                 "email" => $result['email'],
  //                 "message" => "SendE-mail of Air Quality(Warning) - Successfully",
  //                 "Type" => "Email"
  //               ]);
  //             }
  //           }
  //           return true;
  //         } else {
  //           // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send both SMS $ E-mail of Soil Status(Warning) - Successfully');
  //           // $threshold->increment('count');

  //           if (($notifyCount + 1) == $stopLimit) {
  //             $threshold->increment('count');
  //             foreach ($contactDetails as $result) {
  //               notification::create([
  //                 "user" => $result['user_name'],
  //                 "mobile" => $result['mobile'],
  //                 "email" => $result['email'],
  //                 "message" => "Send both SMS & E-mail of Air Quality(Warning) - Successfully",
  //                 "Type" => "SMS & Email"
  //               ]);

  //               break;
  //             }
  //           } else {
  //             foreach ($contactDetails as $result) {
  //               $threshold->increment('count');
  //               notification::create([
  //                 "user" => $result['user_name'],
  //                 "mobile" => $result['mobile'],
  //                 "email" => $result['email'],
  //                 "message" => "Send both SMS & E-mail of Air Quality(Warning) - Successfully",
  //                 "Type" => "SMS & Email"
  //               ]);
  //             }
  //           }

  //           return true;
  //         }
  //         $threshold->increment('count');
  //       } else if ($stopLimit == $notifyCount) {
  //         Log::channel('custom')->info("System Generated" . ':Notification:' . 'Notification limit exceeded.Never send notifications to users.');
  //         return true;
  //       }
  //     }
  //   } else if ($airQualityResult->Level >= $criticalThreshold) {
  //     if ($isNormal == 1) {
  //       if ($stopLimit > $notifyCount) {
  //         if ($notifyType == "sms") {
  //           // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send SMS of Soil Status(Critical) - Successfully');
  //           // $threshold->increment('count');

  //           if (($notifyCount + 1) == $stopLimit) {
  //             $threshold->increment('count');
  //             foreach ($contactDetails as $result) {
  //               notification::create([
  //                 "user" => $result['user_name'],
  //                 "mobile" => $result['mobile'],
  //                 "message" => "Send SMS of Air Quality(Critical) - Successfully",
  //                 "Type" => "SMS"
  //               ]);

  //               break;
  //             }
  //           } else {
  //             foreach ($contactDetails as $result) {
  //               $threshold->increment('count');
  //               notification::create([
  //                 "user" => $result['user_name'],
  //                 "mobile" => $result['mobile'],
  //                 "message" => "Send SMS of Air Quality(Critical) - Successfully",
  //                 "Type" => "SMS"
  //               ]);
  //             }
  //           }
  //           return true;
  //         } else if ($notifyType == "email") {
  //           // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send E-mail of Soil Status(Critical) - Successfully');
  //           // $threshold->increment('count');

  //           if (($notifyCount + 1) == $stopLimit) {
  //             $threshold->increment('count');
  //             foreach ($contactDetails as $result) {
  //               notification::create([
  //                 "user" => $result['user_name'],
  //                 "mobile" => $result['mobile'],
  //                 "email" => $result['email'],
  //                 "message" => "Send E-mail of Air Quality(Critical) - Successfully",
  //                 "Type" => "Email"
  //               ]);

  //               break;
  //             }
  //           } else {
  //             foreach ($contactDetails as $result) {
  //               $threshold->increment('count');
  //               notification::create([
  //                 "user" => $result['user_name'],
  //                 "mobile" => $result['mobile'],
  //                 "email" => $result['email'],
  //                 "message" => "Send E-mail of Air Quality(Critical) - Successfully",
  //                 "Type" => "Email"
  //               ]);
  //             }
  //           }
  //           return true;
  //         } else {
  //           // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send both SMS & E-mail of Soil Status(Critical) - Successfully');
  //           // $threshold->increment('count');

  //           if (($notifyCount + 1) == $stopLimit) {
  //             $threshold->increment('count');
  //             foreach ($contactDetails as $result) {
  //               notification::create([
  //                 "user" => $result['user_name'],
  //                 "mobile" => $result['mobile'],
  //                 "email" => $result['email'],
  //                 "message" => "Send both SMS & E-mail of Air Quality(Critical) - Successfully",
  //                 "Type" => "SMS & Email"
  //               ]);

  //               break;
  //             }
  //           } else {
  //             foreach ($contactDetails as $result) {
  //               $threshold->increment('count');
  //               notification::create([
  //                 "user" => $result['user_name'],
  //                 "mobile" => $result['mobile'],
  //                 "email" => $result['email'],
  //                 "message" => "Send both SMS & E-mail of Air Quality(Critical) - Successfully",
  //                 "Type" => "SMS & Email"
  //               ]);
  //             }
  //           }
  //           return true;
  //         }
  //         $threshold->increment('count');
  //       } else if ($stopLimit == $notifyCount) {
  //         Log::channel('custom')->info("System Generated" . ':Notification:' . 'Notification limit exceeded.Never send notifications to users.');
  //       }
  //     }
  //   } else {
  //     if ($notifyType == "sms") {
  //       Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send SMS of Soil Status(Soil wet too much) - Successfully');
  //       $threshold->increment('count');
  //       return true;
  //     } else if ($notifyType == "email") {
  //       Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send E-mail of Soil Status(Soil wet too much) - Successfully');
  //       $threshold->increment('count');
  //       return true;
  //     } else {
  //       Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send both SMS & E-mail of Soil Status(Soil wet too much) - Successfully');
  //       $threshold->increment('count');
  //       return true;
  //     }
  //   }
  // }

  public function soilQualityNotification()
  {
    $thresholdResult = thresholds::where('sensor_name', '=', "soil")->get();
    $soilLevel = soilMoisture::latest()->first();
    $threshold = thresholds::where('sensor_name', 'soil')->first();
    $userNotificationClass = new notificationController();
    $contactDetails = $userNotificationClass->userNotifications('soil');
    $contactToThreshold = $userNotificationClass->filterContactToThreshold('soil');

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
      $notifyType = $result->notify_type;
    }
    if ($isEnableNotify == 0) {
      return response()->json(["message" => "Notifications not enabled", "status" => 404]);
      exit;
    }

    if ($soilLevel->Level >= $normalThreshold && $soilLevel->Level <= $warningThreshold) {

      if ($isCritical == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send SMS of Soil Status(Normal) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,
                  "message" => "Send SMS of Soil Level(Normal) - Successfully",
                  "Type" => "SMS"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,

                  "message" => "Send SMS of Soil Level(Normal) - Successfully",
                  "Type" => "SMS"
                ]);
              }
            }
            return true;
          } else if ($notifyType == "email") {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send E-mail of Soil Status(Normal) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['user_name'],
                  "email" => $result->email,
                  "message" => "Send E-mail of Soil Level(Normal) - Successfully",
                  "Type" => "Email"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['user_name'],
                  "email" => $result->email,
                  "message" => "Send E-mail of Soil Level(Normal) - Successfully",
                  "Type" => "Email"
                ]);
              }
            }
            return true;
          } else {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send both SMS & E-mail of Soil Level(Normal) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,
                  "email" => $result->email,
                  "message" => "Send both SMS & E-mail of Soil Level(Normal) - Successfully",
                  "Type" => "SMS & Email"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,
                  "email" => $result->email,
                  "message" => "Send both SMS & E-mail of Soil Level(Normal) - Successfully",
                  "Type" => "SMS & Email"
                ]);
              }
            }
            return true;
          }
        } else if ($stopLimit == $notifyCount) {
          Log::channel('custom')->info("System Generated" . ':Notification:' . 'Notification limit exceeded.Never send notifications to users.');
          return true;
        }
      }
    } else if ($soilLevel->Level >= $warningThreshold && $soilLevel->Level < $criticalThreshold) {
      if ($isWarning == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send SMS of Soil Status(Warning) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,

                  "message" => "Send SMS of Soil Level(Warning) - Successfully",
                  "Type" => "SMS"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,
                  "email" => $result->email,
                  "message" => "Send SMS of Soil Level(Warning) - Successfully",
                  "Type" => "SMS"
                ]);
              }
            }
            return true;
          } else if ($notifyType == "email") {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send E-mail of Soil Status(Warning) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  "user" => $result['user_name'],
                  "email" => $result['email'],
                  "message" => "Send E-mail of Soil Level(Warning) - Successfully",
                  "Type" => "Email"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['user_name'],
                  "email" => $result->email,

                  "message" => "Send E-mail of Soil Level(Warning) - Successfully",
                  "Type" => "Email"
                ]);
              }
            }
            return true;
          } else {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send both SMS $ E-mail of Soil Status(Warning) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,
                  "email" => $result->email,
                  "message" => "Send both SMS & E-mail of Soil Level(Warning) - Successfully",
                  "Type" => "SMS & Email"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  "mobile" => $result->mobile,
                  "email" => $result->email,
                  "message" => "Send both SMS & E-mail of Soil Level(Warning) - Successfully",
                  "Type" => "SMS & Email"
                ]);
              }
            }

            return true;
          }
          $threshold->increment('count');
        } else if ($stopLimit == $notifyCount) {
          Log::channel('custom')->info("System Generated" . ':Notification:' . 'Notification limit exceeded.Never send notifications to users.');
          return true;
        }
      }
    } else if ($soilLevel->Level >= $criticalThreshold) {
      if ($isNormal == 1) {
        if ($stopLimit > $notifyCount) {
          if ($notifyType == "sms") {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send SMS of Soil Status(Critical) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,
                  "message" => "Send SMS of Soil Level(Critical) - Successfully",
                  "Type" => "SMS"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,
                  "message" => "Send SMS of Soil Level(Critical) - Successfully",
                  "Type" => "SMS"
                ]);
              }
            }
            return true;
          } else if ($notifyType == "email") {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send E-mail of Soil Status(Critical) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,
                  "email" => $result->email,
                  "message" => "Send E-mail of Soil Level(Critical) - Successfully",
                  "Type" => "Email"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  // "user" => $result['user_name'],
                  "mobile" => $result->mobile,
                  "email" => $result->email,
                  "message" => "Send E-mail of Soil Level(Critical) - Successfully",
                  "Type" => "Email"
                ]);
              }
            }
            return true;
          } else {
            // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send both SMS & E-mail of Soil Status(Critical) - Successfully');
            // $threshold->increment('count');

            if (($notifyCount + 1) == $stopLimit) {
              $threshold->increment('count');
              foreach ($contactToThreshold as $result) {
                notification::create([
                  "mobile" => $result->mobile,
                  "email" => $result->email,
                  "message" => "Send both SMS & E-mail of Soil Level(Critical) - Successfully",
                  "Type" => "SMS & Email"
                ]);

                break;
              }
            } else {
              foreach ($contactToThreshold as $result) {
                $threshold->increment('count');
                notification::create([
                  "mobile" => $result->mobile,
                  "email" => $result->email,
                  "message" => "Send both SMS & E-mail of Soil Level(Critical) - Successfully",
                  "Type" => "SMS & Email"
                ]);
              }
            }
            return true;
          }
          $threshold->increment('count');
        } else if ($stopLimit == $notifyCount) {
          Log::channel('custom')->info("System Generated" . ':Notification:' . 'Notification limit exceeded.Never send notifications to users.');
        }
      }
    } else {
      if ($notifyType == "sms") {
        // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send SMS of Soil Status(Soil wet too much) - Successfully');
        // $threshold->increment('count');
        if (($notifyCount + 1) == $stopLimit) {
          $threshold->increment('count');
          foreach ($contactToThreshold as $result) {
            notification::create([
              // "user" => $result['user_name'],
              "mobile" => $result->mobile,
              "message" => "Send SMS of Soil Status(Soil wet too much) - Successfully",
              "Type" => "SMS"
            ]);

            break;
          }
        } else {
          foreach ($contactToThreshold as $result) {
            $threshold->increment('count');
            notification::create([
              // "user" => $result['user_name'],
              "mobile" => $result->mobile,
              "message" => "Send SMS of Soil Status(Soil wet too much) - Successfully",
              "Type" => "SMS"
            ]);
          }
        }
        return true;
      } else if ($notifyType == "email") {
        // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send E-mail of Soil Status(Soil wet too much) - Successfully');
        // $threshold->increment('count');
        if (($notifyCount + 1) == $stopLimit) {
          $threshold->increment('count');
          foreach ($contactToThreshold as $result) {
            notification::create([
              // "user" => $result['user_name'],
              "email" => $result->email,
              "message" => "Send E-mail of Soil Status(Soil wet too much) - Successfully",
              "Type" => "Email"
            ]);

            break;
          }
        } else {
          foreach ($contactToThreshold as $result) {
            $threshold->increment('count');
            notification::create([
              // "user" => $result['user_name'],
              "email" => $result->email,
              "message" => "Send E-mail of Soil Status(Soil wet too much) - Successfully",
              "Type" => "Email"
            ]);
          }
        }
        return true;
      } else {
        // Log::channel('custom')->info("System Generated" . ':Notification:' . 'Send both SMS & E-mail of Soil Status(Soil wet too much) - Successfully');
        // $threshold->increment('count');


        if (($notifyCount + 1) == $stopLimit) {
          $threshold->increment('count');
          foreach ($contactToThreshold as $result) {
            notification::create([
              // "user" => $result['user_name'],
              "mobile" => $result->mobile,
              "email" => $result->email,
              "message" => "Send both SMS & E-mail of Soil Status(Soil wet too much) - Successfully",
              "Type" => "SMS & Email"
            ]);

            break;
          }
        } else {
          foreach ($contactToThreshold as $result) {
            $threshold->increment('count');
            notification::create([
              // "user" => $result['user_name'],
              "mobile" => $result->mobile,
              "email" => $result->email,
              "message" => "Send both SMS & E-mail of Soil Status(Soil wet too much) - Successfully",
              "Type" => "SMS & Email"
            ]);
          }
        }
        return true;
      }
    }
  }
  public function contactToSensorNotification(Request $request)
  {
    $contactDetails = array();

    $contacts = DB::table('users')
      ->select('users.email', 'users.first_name', 'contacts.mobile', 'contacts.email', 'thresholds.sensor_name')
      ->join('contacts', 'contacts.user_id', '=', 'users.id')
      ->join('contact_to_thresholds', 'contact_to_thresholds.contact_id', '=', 'contacts.user_id')
      ->join('thresholds', 'thresholds.id', '=', 'contact_to_thresholds.threshold_id')
      ->where('contacts.email', '=', $request->email)
      ->get();

    foreach ($contacts as $result) {
      // Add the mobile number to the array
      $contactDetails[] = [
        "email" => $result->email,
        "mobile" => $result->mobile,
        "user_name" => $result->first_name,
        "thresholds" => $result->sensor_name,
      ];
    }
    if($contactDetails){
      return response()->json(["message"=>$contactDetails,"status"=>200]);
    }else{
      return response()->json(["message"=>"Not set notifications for ".$request->email,"status"=>404]);

    }
   
  }
  public function deleteContactFromNotification(Request $request)
  {
    // DELETE contact_to_thresholds FROM contact_to_thresholds
    //  INNER JOIN contacts ON contact_to_thresholds.contact_id = contacts.user_id WHERE contacts.email = "hirosha@gmial.com";
    // echo ($request->email);
    // echo ( $request->sensor_name);die();
    // $result = DB::table('contact_to_thresholds')
    //   ->join('contacts', 'contact_to_thresholds.contact_id', '=', 'contacts.user_id')
    //   ->join('thresholds', 'thresholds.id', '=', 'contact_to_thresholds.threshold_id')
    //   ->where('contacts.email', '=', $request->email)
    //   ->where('thresholds.sensor_name', '=', $request->sensor_name)
    //   ->delete();
    $result = DB::table('contact_to_thresholds')
      ->join('contacts', 'contact_to_thresholds.contact_id', '=', 'contacts.contact_id')
      ->join('thresholds', 'thresholds.id', '=', 'contact_to_thresholds.threshold_id')
      ->where('contacts.email', '=', $request->email)
      ->where('thresholds.sensor_name', '=', $request->sensor_name)
      ->delete();




    return  $result;
  }
}
