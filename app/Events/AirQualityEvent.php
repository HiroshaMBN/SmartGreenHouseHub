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

class AirQualityEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $value;
    public $status;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($value,$status)
    {
        $this->value = $value;
        $this->status = $status;
        Log::info("Air quality  Event Triggered with value: $value and status: $status");
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('air-quality');
    }
    public function broadcastAs(){
        return 'sensor.data.updated';
    }

    public function broadcastWith(){
        return [
            'value' => $this->value,
            'status' => $this->status,
        ];
    }
}
