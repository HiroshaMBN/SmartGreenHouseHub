<?php

namespace App\Http\Controllers\Thresholds;

use App\Http\Controllers\Controller;
use App\Models\thresholds;
use Exception;
use Illuminate\Support\Facades\Validator;
 
use Illuminate\Http\Request;

class thresholdsController extends Controller
{

    //get sensor names types list of dropdown
     function getSensors(){
        return $result = thresholds::get();

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
