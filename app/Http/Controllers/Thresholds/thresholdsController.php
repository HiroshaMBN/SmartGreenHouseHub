<?php

namespace App\Http\Controllers\Thresholds;

use App\Http\Controllers\Controller;
use App\Models\thresholds;
use App\Models\sensor_controller;
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
                'is_enable_notify'=>'',
                'is_normal'=>'',
                'is_warning'=>'',
                'is_critical'=>'',
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->all(), 'status' => 406]);
            }


            // $user = User::create($request->toArray());
            $result = thresholds::where('sensor_name', $request->sensor_name)->update([
                'normal' => $request->normal,
                'warning' => $request->warning,
                'critical' => $request->critical,
                'stop_limit' => $request->stop_limit,
                'notify_type' => $request->notify_type,
                'is_enable_notify'=>$request->is_enable_notify,
                'is_normal'=>$request->is_normal,
                'is_warning'=>$request->is_warning,
                'is_critical'=>$request->is_critical,
                'count' => 0
            ]);

            // $result = thresholds::create($request->toArray());
            return response()->json(["message" => $request->sensor_name . ' Thresholds update successfully', "status" => 200]);
        } catch (Exception $exception) {
            return response()->json(["message" => $exception->getMessage(), "status" => "401"]);
        }
    }
   


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
}
