<?php

namespace App\Http\Controllers\RabbitMq;

use App\Http\Controllers\Controller;
use App\Jobs\readClimate;
use App\Jobs\TestQueue;
use App\Models\Climate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PassToQController extends Controller
{
    //Turn On light
    public function lightOn(Request $request){
        $data = ['name' => 'light', 'type' => 'on'];
        $this->dispatch(new TestQueue($data));
    }


    public function lightOff(Request $request){
        $data = ['name'=>'light','type'=>'off'];
        $this->dispatch(new TestQueue($data));
    }

    public function receiveSensorData(Request $request)
{

    //defineJob::
    $r = readClimate::dispatch($request->all());
//    var_dump($r);
Log::channel('custom')->info(Auth::user()->email.':RabbitMQ data handling:'.'Sensor Data received');

    return response()->json(['message' => 'Data received'], 200);
}
}
