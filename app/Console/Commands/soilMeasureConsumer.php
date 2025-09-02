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

class soilMeasureConsumer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:soilMoisture';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rabbitmq with Soil sensor';

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
      //soil moisture queue
      $channel->queue_declare('sensor/soil', false, true, false, false);
      $callback3 = function ($msg3) {
        $data3 = json_decode($msg3->body, true);
 
        if (isset($data3['Level']) && isset($data3['status'])) {
          // Broadcast the event with temperature and humidity data
          broadcast(new soilSensorEvent($data3['Level'], $data3['status']));
          // broadcast(new SensorDataUpdated($msg->body ));
          \App\Models\soilMoisture::create([
            'Level' => $data3['Level'],
            'status' => $data3['status']
          ]);
          echo "Update soil";
          Log::info('Data received from RabbitMQ: soil ' . $msg3->body);
        } else {
          Log::error('Invalid data received from RabbitMQ: ' . $data3->body);
        }
      };
      $channel->basic_consume('sensor/soil', '', false, true, false, false, $callback3);
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
