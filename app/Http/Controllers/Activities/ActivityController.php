<?php

namespace App\Http\Controllers\Activities;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\errorLogData;
use App\Models\infoLogData;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class ActivityController extends Controller
{
    // public function errorLog()
    // {
    //     // Define the path to your custom.log file
    //     $logFilePath = storage_path('logs/custom.log'); // Or your custom path

    //     // Check if the log file exists
    //     if (!file_exists($logFilePath)) {
    //         return response()->json(['error' => 'Log file not found'], 404);
    //     }

    //     // Read the log file contents
    //     $logContents = file_get_contents($logFilePath);

    //     // Filter out the lines with 'local.ERROR'
    //     preg_match_all('/\[(.*?)\] local\.ERROR: (.*?)\n/', $logContents, $matches);

    //     // Prepare the output with timestamps and error messages
    //     $errorLogs = [];
    //     foreach ($matches[1] as $key => $timestamp) {
    //         $errorLogs[] = [
    //             'timestamp' => $timestamp,
    //             'error_message' => $matches[2][$key],
    //         ];
    //     }

    //     // Return the error logs as a JSON response
    //     return response()->json(['error_logs' => $errorLogs]);
    // }

    public function errorLogToDB()
    {
        // Define the path to your custom.log file
        $logFilePath = storage_path('logs/custom.log'); // Or your custom path

        // Check if the log file exists
        if (!file_exists($logFilePath)) {
            return response()->json(['error' => 'Log file not found'], 404);
        }

        // Read the log file contents
        $logContents = file_get_contents($logFilePath);

        // Filter out the lines with 'local.ERROR' or 'local.WARNING'
        preg_match_all('/\[(.*?)\] local\.(ERROR|WARNING): (.*?)\n/', $logContents, $matches);

        // Prepare the output with timestamps, error levels, and error messages
        foreach ($matches[1] as $key => $timestamp) {
            // $errorLevel = $matches[2][$key];  // ERROR or WARNING
            $errorMessage = $matches[3][$key]; // Error or warning message

            // Check if a record with this timestamp exists in the database
            $existingLog = errorLogData::where('log_time', $timestamp)->first();

            if ($existingLog) {
                // If the record exists, update the information
                $existingLog->information = $errorMessage;
                // $existingLog->log_level = $errorLevel;  // Assuming you have a log_level field
                $existingLog->save(); // Update the record
            } else {
                // If the record doesn't exist, create a new one
                errorLogData::create([
                    'log_time' => $timestamp,
                    'information' => $errorMessage,
                    // 'log_level' => $errorLevel  // Assuming you want to store the log level
                ]);
            }
        }

        // Return a response after processing
        return response()->json(['status' => 'Log processed successfully']);
    }

    public function infoLogToDB()
    {
        // Define the path to your custom.log file
        $logFilePath = storage_path('logs/custom.log'); // Or your custom path

        // Check if the log file exists
        if (!file_exists($logFilePath)) {
            return response()->json(['error' => 'Log file not found'], 404);
        }

        // Read the log file contents
        $logContents = file_get_contents($logFilePath);

        // Filter out the lines with 'local.ERROR'
        preg_match_all('/\[(.*?)\] local\.INFO: (.*?)\n/', $logContents, $matches);

        // Prepare the output with timestamps and error messages
        foreach ($matches[1] as $key => $timestamp) {
            $errorMessage = $matches[2][$key];

            // Check if a record with this timestamp exists in the database
            $existingLog = infoLogData::where('log_time', $timestamp)->first();

            if ($existingLog) {
                // If the record exists, update the information
                $existingLog->information = $errorMessage;
                $existingLog->save(); // Update the record
            } else {
                // If the record doesn't exist, create a new one
                infoLogData::create([
                    'log_time' => $timestamp,
                    'information' => $errorMessage
                ]);
            }
        }

        // Return a response after processing
        return response()->json(['status' => 'Log processed successfully']);
    }
    public function showErrorLogToDb(Request $request)
    {
        try {
            $logDataResult = errorLogData::where('log_time', 'LIKE', '%' . $request->date . '%')->get();


            $logArray = array();
            foreach ($logDataResult as $result) {
                $data = [
                    "log_time" => $result->log_time,
                    "information" => $result->information
                ];
                array_push($logArray, $data);
            }

            return response()->json(["message" => $logArray, "status" => 200]);
        } catch (Exception $exception) {
            return response()->json(["message" => $exception->getMessage(), "status" => 406]);
        }
    }
    public function showInfoLogToDb(Request $request)
    {
        try {
            $logDataResult = infoLogData::where('log_time', 'LIKE', '%' . $request->date . '%')->get();


            $logArray = array();
            foreach ($logDataResult as $result) {
                $data = [
                    "log_time" => $result->log_time,
                    "information" => $result->information
                ];
                array_push($logArray, $data);
            }

            return response()->json(["message" => $logArray, "status" => 200]);
        } catch (Exception $exception) {
            return response()->json(["message" => $exception->getMessage(), "status" => 406]);
        }
    }
    public function getGreeting(){
        $hour = now()->hour; // Uses Laravel's Carbon instance
 
        if ($hour >= 5 && $hour < 12) {
            return response()->json(["name"=>'Hi '.Auth::user()->first_name,"greet"=>' Good Morning..!',"status"=>200]);
        } elseif ($hour >= 12 && $hour < 17) {
            return response()->json(["name"=>'Hi '.Auth::user()->first_name,"greet"=>' Good Afternoon..!',"status"=>200]);
        } elseif ($hour >= 17 && $hour < 20) {
            return response()->json(["name"=>'Hi '.Auth::user()->first_name,"greet"=>' Good Evening..!',"status"=>200]);
        } else {
            return response()->json(["name"=>'Hi '.Auth::user()->first_name,"greet"=>' Good Night..!',"status"=>200]);
        }
    }
}
