<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;


use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use App\Models\thresholds;
use App\Events\SensorDataUpdated;
use App\Events\AirQualityEvent;
use App\Events\ultrasonicWaterLevel;
use App\Events\soilSensorEvent;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\RabbitMq\PublishToMessageToNodemcu;

class climateConsumer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:climate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rabbitmq with DHT11 Sensor';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {

            $connection = new AMQPStreamConnection(
                // env('RABBITMQ_HOST'),
                // env('RABBITMQ_PORT'),
                // env('RABBITMQ_USERNAME'),
                // env('RABBITMQ_PASSWORD'),
                // env('RABBITMQ_VHOST')
                '192.168.8.104',
                5672,
                'guest',
                'guest',
                '/'
            );

            $channel = $connection->channel();
            //dht11 queue
            $channel->queue_declare('sensor/dht11', false, true, false, false);
            // echo " [*] Waiting for messages. To exit press CTRL+C\n";
            $callback = function ($msg) {
                $RabbitMqControlInstance = new PublishToMessageToNodemcu();

                // echo ' [x] Received ', $msg->body, "\n";
                $data = json_decode($msg->body, true);

                // broadcast(new SensorDataUpdated($data['temperature'], $data['humidity']));
                if (isset($data['temperature']) && isset($data['humidity'])) {

                    // Broadcast the event with temperature and humidity data
                    broadcast(new SensorDataUpdated($data['temperature'], $data['humidity']));

                    \App\Models\Climate::create([
                        'temperature' => $data['temperature'],
                        'humidity' => $data['humidity'],
                    ]);
                    $tmpCriticalThresholdValue = thresholds::where('sensor_name', 'dht11-tmp')->pluck('critical')->first();
                    $tmpCriticalThresholdIsAutomate = thresholds::where('sensor_name', 'dht11-tmp')->pluck('is_automate')->first();
                    if ($tmpCriticalThresholdIsAutomate == 1) {
                        if ($tmpCriticalThresholdValue <= $data['temperature']) {
                            $RabbitMqControlInstance->exhaustFanAutomated("ON");
                        } else {
                            $RabbitMqControlInstance->exhaustFanAutomated("OFF");
                        }
                    }
                } else {
                    Log::error('Invalid data received from RabbitMQ: ' . $msg->body);
                }




                echo "Update climate";
                // broadcast(new \App\Events\SensorDataReceived($msg->body ));
            };
         

            $channel->basic_consume('sensor/dht11', '', false, true, false, false, $callback);



            while ($channel->is_consuming()) {
                $channel->wait();
            }

            $channel->close();
            $connection->close();
        } catch (Exception $e) {

            echo "message: " . $e->getMessage() . " line: " . $e->getLine();
        }
    }
}
