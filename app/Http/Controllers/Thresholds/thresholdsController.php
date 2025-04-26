<?php

namespace App\Http\Controllers\Thresholds;

use App\Http\Controllers\Controller;
use App\Models\thresholds;
use App\Models\User;
use App\Models\sensor_controller;
use App\Models\contactToThreshold;
use Exception;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class thresholdsController extends Controller
{

    //add sensors
    public function AddSensors(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'display_name' => 'required|string',
            'status' => "required"
        ]);

        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->all(), "status" => 406]);
        }
        $addSensor = sensor_controller::create($request->toArray());
        return response()->json(["message" => "Sensor Add Successfully", "status" => 200]);

        // $user = User::create($request->toArray());  $validator->errors()->all()


    }
    //get sensor names types list of dropdown
    public function getSensors()
    {
        $sensorArray = array();
        $sensors = sensor_controller::get();

        foreach ($sensors as $result) {
            $data = [
                "name" => $result->name,
                "display_name" => $result->display_name,
                "status" => $result->status
            ];
            array_push($sensorArray, $data);
        }

        return $sensorArray;
    }
    //set threshold values
    public function SensorThresholds(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                // 'instance_id' => 'integer',
                'sensor_name' => 'required',
                'normal' => 'required',
                'warning' => 'required',
                'critical' => 'required',
                'stop_limit' => 'required',
                'notify_type' => 'required',
                'is_automate'=>'',
                'is_enable_notify' => '',
                'is_normal' => '',
                'is_warning' => '',
                'is_critical' => '',
                'notify_interval' => '',
                'email' => ''
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->all(), 'status' => 406]);
            }

            $threshold = thresholds::where('sensor_name', $request->sensor_name)->get('id');
            $user = User::where('email', $request->email)->get('id');
            foreach ($threshold as $result) {
                $thresholdID = $result->id;
            }
            foreach ($user as $result) {
                $userID = $result->id;
                $userName = $result->first_name;
            }
            
 
            if($request->email != NULL){
                contactToThreshold::create([
                    "contact_id" => $userID,
                    "threshold_id" => $thresholdID,
                    "notify_type" =>$request->notify_type,
                    'notify_interval' => $request->notify_interval	
                ]);
            }
 

            // $user = User::create($request->toArray());
            $result = thresholds::where('sensor_name', $request->sensor_name)->update([
                'normal' => $request->normal,
                'warning' => $request->warning,
                'critical' => $request->critical,
                'stop_limit' => $request->stop_limit,
                'is_automate' => $request->is_automate,
                'is_enable_notify' => $request->is_enable_notify,
                'is_normal' => $request->is_normal,
                'is_warning' => $request->is_warning,
                'is_critical' => $request->is_critical,
                'notify_interval' => $request->notify_interval,
                'count' => 0,
                // 'count' => ($existLimit == $request->stop_limit )?:0, 

            ]);

            // $result = thresholds::create($request->toArray());
            return response()->json(["message" => $request->sensor_name . ' Thresholds update successfully', "status" => 200]);
        } catch (Exception $exception) {
            
            if((strpos($exception->getMessage(),'Duplicate entry'))){
            return response()->json(["message" => $request->email." is already define to ".$request->sensor_name." sensor threshold","status"=>"exist"]);
            }else{
                return response()->json(["message" => $exception->getMessage(), "status" => "406sss"]);

            }
        }
    }
    //update notify
    public function updateNotifications(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'sensor_name' => 'required',
                'is_normal' => 'required',
                'is_warning' => 'required',
                'is_critical' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(["message" => $validator->errors()->all(), "status" => 406]);
            }


            $result = thresholds::where('sensor_name', $request->sensor_name)
                ->update([
                    'is_normal' => $request->is_normal,
                    'is_warning' => $request->is_warning,
                    'is_critical' => $request->is_critical
                ]);
            return response()->json(["message" => $result, "status" => 200]);
        } catch (Exception $exception) {
            return response()->json(["message" => $exception->getMessage(), "status" => 406]);
        }
    }
    //show threshold values ($x == 1) ? 'yes' : 'no';
    public function showThreshold()
    {
        try {
            $thresholdArray = array();
            $thresholdResult = thresholds::get();
            foreach ($thresholdResult as $result) {
                $data = [
                    "sensor_name" => $result->sensor_name,
                    "normal" => $result->normal,
                    "warning" => $result->warning,
                    "critical" => $result->critical,
                    "is_enable_notification" => ($result->is_enable_notify == 1) ? 'Notification enabled' : 'Notification disabled',
                    "is_normal" => ($result->is_normal == 1) ? 'Send Notification' : 'Never send notification',
                    "is_warning" => ($result->is_warning == 1) ? 'Send Notification' : 'Never send notification',
                    "is_critical" => ($result->is_critical == 1) ? 'Send Notification' : 'Never send notification',
                    "notification_stop_limit" => $result->stop_limit,
                    "send_notification_count" => $result->count,
                    "notification_method" => $result->notify_type,
                    "notify_interval" => $result->notify_interval,

                ];
                array_push($thresholdArray, $data);
            }
            return response()->json(["message" => $thresholdArray, "status" => 200]);
        } catch (Exception $exception) {
            return response()->json(["message" => $exception->getMessage(), "line" => $exception->getLine(), "status" => 406]);
        }
    }
}
