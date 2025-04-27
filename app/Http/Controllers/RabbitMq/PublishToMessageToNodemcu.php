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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\thresholds;

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
    public function lightThree(Request $request) #D2
    {
        try {
            $messageData = $request->input('message_data', []);
            $success = RabbitMQConsumer::greenHouseLightThree(env('CONTROL_QUEUE'), $messageData);
            if ($success) {
                if ($messageData == "ON") {
                    Log::channel('custom')->info(Auth::user()->email . ':publishToQ' . 'LIGHT_THREE_ON');
                    return response()->json(["message" => env('LIGHT_THREE_ON'), "status" => 200]);
                } else {
                    Log::channel('custom')->info(Auth::user()->email . ':publishToQ' . 'LIGHT_THREE_OFF');
                    return response()->json(["message" => env('LIGHT_THREE_OFF'), "status" => 200]);
                }
            } else {
                Log::channel('custom')->info(Auth::user()->email . ':publishToQ' . 'Failed to LIGHT_THREE publish message');
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

    public function  waterTank(Request $request)
    { #D5
        try {
            $messageData = $request->input('message_data', []);
            $success = RabbitMQConsumer::greenHouseWaterMotor(env('CONTROL_QUEUE'), $messageData);
            if ($success) {
                if ($messageData == "ON") {
                    Log::channel('custom')->info(Auth::user()->email . ':publishToQ' . 'WATER_TANK_ON');
                    return response()->json(["message" => env('WATER_TANK_ON'), "status" => 200]);
                } else {
                    Log::channel('custom')->info(Auth::user()->email . ':publishToQ' . 'WATER_TANK_OFF');
                    return response()->json(["message" => env('WATER_TANK_OFF'), "status" => 200]);
                }
            } else {
                Log::channel('custom')->info(Auth::user()->email . ':publishToQ' . "Failed to WATER_TANK publish message");
                return response()->json(["message" => 'Failed to publish message', "status" => 500]);
            }
        } catch (Exception $exception) {
            Log::channel('custom')->info(Auth::user()->email . ':publishToQ' . $exception->getMessage());
            return response()->json(["message" => $exception->getMessage(), "status" => 500]);
        }
    }


    //automated function
    public function  exhaustFanAutomated($messageData)
    { #
        try {
            $threshold = thresholds::where('sensor_name', 'dht11-tmp')->get('is_automate');

            // $messageData = $request->input('message_data', []);
            $success = RabbitMQConsumer::greenHouseExhaustFan(env('CONTROL_QUEUE'), $messageData);
            if ($success) {
                if ($messageData == "ON") {
                    Log::channel('custom')->info("System generated" . ':Automate' . 'EXHAUST_FAN_ON');
                    return response()->json(["message" => env('EXHAUST_FAN_ON'), "status" => 200]);
                } else {
                    Log::channel('custom')->info("System generated"  . ':Automate' . 'EXHAUST_FAN_OFF');
                    return response()->json(["message" => env('EXHAUST_FAN_OFF'), "status" => 200]);
                }
            } else {
                Log::channel('custom')->info("System generated" . ':Automate' . "Failed to EXHAUST_FAN publish message");
                return response()->json(["message" => 'Failed to publish message', "status" => 500]);
            }
        } catch (Exception $exception) {
            Log::channel('custom')->info("System generated" . ':Automate' . $exception->getMessage());
            return response()->json(["message" => $exception->getMessage(), "status" => 500]);
        }
    }

    public function  waterMotorAutomated($messageData)
    { #D5
        try {
            // $messageData = $request->input('message_data', []);
            $success = RabbitMQConsumer::greenHouseWaterMotor(env('CONTROL_QUEUE'), $messageData);
            if ($success) {
                if ($messageData == "ON") {
                    Log::channel('custom')->info("System generated" . ':Automate' . 'WATER_TANK_ON');
                    return response()->json(["message" => env('WATER_TANK_ON'), "status" => 200]);
                } else {
                    Log::channel('custom')->info("System generated" . ':Automate' . 'WATER_TANK_OFF');
                    return response()->json(["message" => env('WATER_TANK_OFF'), "status" => 200]);
                }
            } else {
                Log::channel('custom')->info("System generated". ':Automate' . "Failed to WATER_TANK publish message");
                return response()->json(["message" => 'Failed to publish message', "status" => 500]);
            }
        } catch (Exception $exception) {
            Log::channel('custom')->info("System generated". ':Automate' . $exception->getMessage());
            return response()->json(["message" => $exception->getMessage(), "status" => 500]);
        }
    }
}
