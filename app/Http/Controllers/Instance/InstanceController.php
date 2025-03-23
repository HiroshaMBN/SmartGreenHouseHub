<?php

namespace App\Http\Controllers\Instance;

use App\Http\Controllers\Controller;
use App\Models\instance;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class InstanceController extends Controller
{
    //create IOT instance
    public function Instance(Request $request){
     try{
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'ip' => 'required|unique:instances',
            'model' =>'required|string',
            'ssid' => 'required|string',
            'password' =>'required|string'
        ]);
        if($validator->fails()){
            Log::channel('custom')->error(Auth::user()->email.':make instance:'.$validator->errors()->all());

            return response()->json(["message"=>$validator->errors()->all(),
                                    "status"=>406]);
        }
        instance::create($request->toArray());
        Log::channel('custom')->info(Auth::user()->email.':make instance:'.'Instance created successfully');

        return response()->json(["message"=>"Instance created successfully","status"=>200]);
     }catch(Exception $exception){
        Log::channel('custom')->error(Auth::user()->email.':make instance:'.$exception->getMessage());
        return response()->json(["message"=>$exception->getMessage(),"status"=>406]);
     }
    }
}
