<?php

namespace App\Events;

use Exception;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
class SensorDataUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $temperature;
    public $humidity;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($temperature, $humidity)
    {
        //
        $this->temperature = $temperature;
        $this->humidity = $humidity;   
        Log::info("SensorDataUpdated Event Triggered with Temperature: $temperature and Humidity: $humidity");
        // return new Channel('sensor-data');
       
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('sensor-data');
    }

    public function broadcastAs()
    {
        return 'sensor.data.updated';
    }

    public function broadcastWith()
    {
        return [
            'temperature' => $this->temperature,
            'humidity' => $this->humidity,
        ];
    }
}
