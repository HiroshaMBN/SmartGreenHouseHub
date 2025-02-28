<?php

namespace App\Jobs;

use App\Models\Climate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class readClimate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $payload;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($payload)
    {
        $this->payload = $payload;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Received Sensor Data: ", (array) $this->payload);


        // Climate::create([
        //     'temperature' => 50,
        //     'humidity' => 50
        // ]);



        // DB::table('climates')->insert([
        //     'temperature' => $this->payload['temperature'],
        //     'humidity' => $this->payload['humidity'],
        //     'created_at' => now(),
        // ]);
    }
}
