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
class soilSensorEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $Level;
    public $status;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($Level,$status)
    {
      $this->Level = $Level;
      $this->status = $status;
      Log::info("soilSensor Event Triggered with Level: $Level and status: $status");
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('soil-sensor');
    }

    public function broadcastAs()
    {
        return 'sensor.data.updated';
    }

    public function broadcastWith()
    {
        return [
            'Value' => $this->Level,
            'status' => $this->status,
        ];
    }
}
