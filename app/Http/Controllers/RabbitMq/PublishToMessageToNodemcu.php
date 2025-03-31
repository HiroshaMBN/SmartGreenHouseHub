<?php

namespace App\Http\Controllers\RabbitMq;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
// use App\Jobs\RabbitMQConsumer;
use App\Console\Commands\RabbitMQConsumer;
use Illuminate\Support\Str;

class PublishToMessageToNodemcu extends Controller
{

    public function lightOne(Request $request) #D1
    {
        try {
            $messageData = $request->input('message_data', []);
            $success = RabbitMQConsumer::greenHouseLightOne(env('CONTROL_QUEUE'), $messageData);
            if ($success) {
                if ($messageData == "ON") {
                    return response()->json(["message" => env('LIGHT_ONE_ON'), "status" => 200]);
                } else {
                    return response()->json(["message" => env('LIGHT_ONE_OFF'), "status" => 200]);
                }
            } else {
                return resposne()->json(["message" => "Failed to publish message", "status" => 500]);
            }
        } catch (\Exception $e) {
            return response()->json(["message" => $exception->getMessage(), "status" => 500]);
        }
    }


    public function lightTwo(Request $request) #D2
    {
        try {
            $messageData = $request->input('message_data', []);
            $success = RabbitMQConsumer::greenHouseLightTwo(env('CONTROL_QUEUE'), $messageData);
            if ($success) {
                if ($messageData == "ON") {
                    return response()->json(["message" => env('LIGHT_TWO_ON'), "status" => 200]);
                } else {
                    return response()->json(["message" => env('LIGHT_TWO_OFF'), "status" => 200]);
                }
            } else {
                return resposne()->json(["message" => "Failed to publish message", "status" => 500]);
            }
        } catch (Exception $exception) {
            return response()->json(["message" => $exception->getMessage(), "status" => 500]);
        }
    }

    public function  exhaustFan(Request $request) { #D3
        try{
            $messageData = $request->input('message_data',[]);
            $success = RabbitMQConsumer::greenHouseExhaustFan(env('CONTROL_QUEUE'),$messageData);
            if($success){
                if($messageData == "ON"){
                    return response()->json(["message"=>env('EXHAUST_FAN_ON'),"status"=>200]);
                }else{
                    return response()->json(["message"=>env('EXHAUST_FAN_OFF'),"status"=>200]);
                }
            }else{
                return response()->json(["message"=>$exception->getMessage(),"status"=>500]);
            }
        }catch(Exception $exception){
            return response()->json(["message"=>$exception->getMessage(),"status"=>500]);
        }

    }
}
