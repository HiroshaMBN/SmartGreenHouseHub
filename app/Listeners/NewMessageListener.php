<?php

namespace App\Listeners;

use App\Events\SensorDataUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NewMessageListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\SensorDataUpdated  $event
     * @return void
     */
    public function handle(SensorDataUpdated $event)
    {
        //
        // broadcast(new SensorDataUpdated($event->message))->toOthers();
    }
}
