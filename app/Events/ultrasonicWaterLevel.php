<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
class ultrasonicWaterLevel implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $distance;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($distance)
    {
        $this->distance = $distance;
        Log::info("ultrasonic Event Triggered with water distance: $distance");
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('ultrasonic-sensor');
    }

    public function broadcastAs()
    {
        return 'sensor.data.updated';
    }

    public function broadcastWith()
    {
        if($this->distance < 10){
            return [
                'distance' => $this->distance  ,
                'status'=>'FULL'  
            ];
        }else if($this->distance >=10 && $this->distance <= 50){
            return [
                'distance' => $this->distance,
                'status'=>'MEDIUM'  
            ];
        }else{
            return [
                'distance' => $this->distance,
                'status'=>'LOW'
            ];
        }
        
    }
}
