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
    public function AddSensors(Request $request){
        $validator = Validator::make($request->all(),[
            'name'=>'required|string',
            'display_name' => 'required|string',
            'status' =>"required"
        ]);

        if($validator->fails()){
            return response()->json(["message"=>$validator->errors()->all(),"status"=>406]);
        }
        $addSensor = sensor_controller::create($request->toArray());
        return response()->json(["message"=>"Sensor Add Successfully","status"=>200]);

        // $user = User::create($request->toArray());  $validator->errors()->all()


    }
    //get sensor names types list of dropdown
    public function getSensors(){
        $sensorArray = array();
        $sensors = sensor_controller::get();

        foreach($sensors as $result){
            $data =[
                "name"=>$result->name,
                "display_name"=>$result->display_name,
                "status"=>$result->status
            ];
            array_push($sensorArray,$data);
        }

        return $sensorArray;

    }
    public function SensorThresholds(Request $request){
       try{
        $validator = Validator::make($request->all(), [
            // 'instance_id' => 'integer',
            'sensor_name'=>'required',
            'normal' => 'required',         
            'warning'=>'required',
            'critical' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->all(), 'status' => 406]);
        }

        
        // $user = User::create($request->toArray());
        $result = thresholds::create($request->toArray());
        return response()->json(["message"=>"Add new threshold for ".$result['sensor_name'].' successfully',"status"=>200]);

        return $result;
       }catch(Exception $exception){
        return response()->json(["message"=>$exception->getMessage(),"status"=>"401"]);
       }



    }

  
}
