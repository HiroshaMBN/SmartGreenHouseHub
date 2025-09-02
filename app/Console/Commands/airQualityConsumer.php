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

class airQualityConsumer extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'rabbitmq:airQuality';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Rabbitmq with MQ2 Sensor';

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
      //mq2 queue
      $channel->queue_declare('sensor/mq2', false, true, false, false);


      $callback2 = function ($msg2) {
        // echo ' [x] Received ', $msg2->body, "\n";
        $data2 = json_decode($msg2->body, true);

        if (isset($data2['Value']) && isset($data2['status'])) {
          // Broadcast the event with temperature and humidity data
          broadcast(new AirQualityEvent($data2['Value'], $data2['status']));
          // broadcast(new SensorDataUpdated($msg->body ));
        } else {
          Log::error('Invalid data received from RabbitMQ: ' . $msg2->body);
        }


        Log::error('Data received from RabbitMQ: MQ2 ' . $msg2->body);

        \App\Models\airCondition::create([
          'value' => $data2['Value'],
          'status' => $data2['status']
        ]);
        echo "Update mq2";
      };

      $channel->basic_consume('sensor/mq2', '', false, true, false, false, $callback2);

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
