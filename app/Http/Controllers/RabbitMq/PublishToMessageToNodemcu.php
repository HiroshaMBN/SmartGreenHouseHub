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
                    Log::channel('custom')->info(Auth::user()->email . ':publishToQ' . 'LIGHT_ONE_ON');
                    return response()->json(["message" => env('LIGHT_ONE_ON'), "status" => 200]);
                } else {
                    Log::channel('custom')->info(Auth::user()->email . ':publishToQ' . 'LIGHT_ONE_OFF');
                    return response()->json(["message" => env('LIGHT_ONE_OFF'), "status" => 200]);
                }
            } else {
                Log::channel('custom')->info(Auth::user()->email . ':publishToQ' . 'Failed to LIGHT_ONE publish message');
                return resposne()->json(["message" => "Failed to publish message", "status" => 500]);
            }
        } catch (\Exception $e) {
            Log::channel('custom')->info(Auth::user()->email . ':publishToQ' . $exception->getMessage());
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
                    Log::channel('custom')->info(Auth::user()->email . ':publishToQ' . 'LIGHT_TWO_ON');
                    return response()->json(["message" => env('LIGHT_TWO_ON'), "status" => 200]);
                } else {
                    Log::channel('custom')->info(Auth::user()->email . ':publishToQ' . 'LIGHT_TWO_OFF');
                    return response()->json(["message" => env('LIGHT_TWO_OFF'), "status" => 200]);
                }
            } else {
                Log::channel('custom')->info(Auth::user()->email . ':publishToQ' . 'Failed to LIGHT_TWO publish message');
                return resposne()->json(["message" => "Failed to publish message", "status" => 500]);
            }
        } catch (Exception $exception) {
            Log::channel('custom')->info(Auth::user()->email . ':publishToQ' . $exception->getMessage());
            return response()->json(["message" => $exception->getMessage(), "status" => 500]);
        }
    }

    public function  exhaustFan(Request $request)
    { #D3
        try {
            $messageData = $request->input('message_data', []);
            $success = RabbitMQConsumer::greenHouseExhaustFan(env('CONTROL_QUEUE'), $messageData);
            if ($success) {
                if ($messageData == "ON") {
                    Log::channel('custom')->info(Auth::user()->email . ':publishToQ' . 'EXHAUST_FAN_ON');
                    return response()->json(["message" => env('EXHAUST_FAN_ON'), "status" => 200]);
                } else {
                    Log::channel('custom')->info(Auth::user()->email . ':publishToQ' . 'EXHAUST_FAN_OFF');
                    return response()->json(["message" => env('EXHAUST_FAN_OFF'), "status" => 200]);
                }
            } else {
                Log::channel('custom')->info(Auth::user()->email . ':publishToQ' . "Failed to EXHAUST_FAN publish message");
                return response()->json(["message" => 'Failed to publish message', "status" => 500]);
            }
        } catch (Exception $exception) {
            Log::channel('custom')->info(Auth::user()->email . ':publishToQ' . $exception->getMessage());
            return response()->json(["message" => $exception->getMessage(), "status" => 500]);
        }
    }
}
