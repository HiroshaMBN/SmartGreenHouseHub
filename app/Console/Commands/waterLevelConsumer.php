<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use App\Models\thresholds;
use App\Events\SensorDataUpdated;
use App\Events\AirQualityEvent;
use App\Events\ultrasonicWaterLevel;
use App\Events\soilSensorEvent;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\RabbitMq\PublishToMessageToNodemcu;

class waterLevelConsumer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:water_level';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
      $channel->queue_declare('sensor/ultrasonic', false, true, false, false);

    


      $callback5 = function ($msg5) {
        $data = json_decode($msg5->body, true);
var_dump($data);
        $RabbitMqControlInstance = new PublishToMessageToNodemcu();
        if (isset($data['distance'])) {

          broadcast(new ultrasonicWaterLevel($data['distance']));
          // broadcast(new SensorDataUpdated($msg->body ));
          \App\Models\waterLevel::create([
            'stock' => $data['distance']
          ]);
          $tmpCriticalThresholdValue = thresholds::where('sensor_name', 'ultra-sonic')->pluck('critical')->first();
          $tmpCriticalThresholdIsAutomate = thresholds::where('sensor_name', 'ultra-sonic')->pluck('is_automate')->first();
          if ($tmpCriticalThresholdIsAutomate == 1) {
            if ($tmpCriticalThresholdValue <= $data['distance']) {
              $RabbitMqControlInstance->waterMotorAutomated("ON");
            } else {
              $RabbitMqControlInstance->waterMotorAutomated("OFF");
            }
          }
          Log::info('Data received from RabbitMQ: ultrasonic: ' . $data['distance']);
        } else {
          Log::error('Invalid data received from RabbitMQ: ' . $data['distance']);
        }

        echo "Update ultrasonic";
      };


      $channel->basic_consume('sensor/ultrasonic', '', false, true, false, false, $callback5);


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
