<?php

namespace App\Http\Controllers\Objects;

use App\Http\Controllers\Controller;
use App\Models\instance;
use App\Models\object_controller;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObjectController extends Controller
{
    //object controller for the add new sensors and other objects
    public function GetInstances()
    {
        try {
            //get instance details
            $instanceArrayData = array();
            $instance = instance::all();
            foreach ($instance as $data) {
                $instanceArray = [
                    "id" => $data->id,
                    "name" => $data->name,
                    "ip" => $data->ip,
                    "model" => $data->model
                ];
                array_push($instanceArrayData, $instanceArray);
            }

            // print_r($instanceArrayData);die();
            return response()->json(["message" => $instanceArrayData, "status" => 200]);
        } catch (Exception $exception) {
            return response()->json(["message" => $exception->getMessage(), "status" => 406]);
        }
    }


    public function AddNewSensor(Request $request)
    {
        try {



            $validator = Validator::make($request->all(), [
                'instanceName' => 'required|integer',
                'sensorTechnicalName' => 'required|string',
                'SensorDisplayName' => 'required|string',
                'status' => 'string',
            ]);


            if ($validator->fails()) {
                return response()->json(["message" => $validator->errors()->all(), "status" => 406]);
            }
            $instanceName = $validator['instanceName'];
            var_dump(123443);
            $instance = DB::table('instances')->select('id')->where('name', $validator["instanceName"])->get();
            $saveSensor = object_controller::create($request->array());



            return response()->json(["message" => "Sensor added successfully", "status" => 200]);
        } catch (Exception $exception) {
            return response()->json(["message" => $exception->getMessage(), "status" => 406]);
        }
    }



}
