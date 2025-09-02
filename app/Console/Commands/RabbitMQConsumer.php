<?php

namespace App\Console\Commands;

use Exception;
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

class RabbitMQConsumer extends Command
{
  protected $signature = 'rabbitmq:consume';
  protected $description = 'Consume data from RabbitMQ';
  public $x = "";
  public function __construct()
  {
    parent::__construct();
    // $this->queueName = $queueName;
    // $this->customValue = $customValue;
  }

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
     

      //on off trigger
      // $channel->queue_declare('mqtt-subscription-ESP8266Client-4ff3qos0',false,true,false,false);
      $channel->queue_declare('device_control', false, true, false, false);
      echo " [*] Waiting for messages in queue:device_control\n";

      // echo " [*] Waiting for messages. To exit press CTRL+C\n";
    

      $callback4 = function (AMQPMessage $msg) {
        echo " [x] Received: ", $msg->body, "\n";
        // echo " Custom Value: ", $this->customValue, "\n"; // Example of passing data
        $msg->ack();
      };

   


      //read message from q to
      $channel->basic_consume('device_control', '', false, false, false, false, $callback4);



      while ($channel->is_consuming()) {
        $channel->wait();
      }

      $channel->close();
      $connection->close();
    } catch (Exception $e) {

      echo "message: " . $e->getMessage() . " line: " . $e->getLine();
    }
  }

  public static function greenHouseLightOne($queueName, $data)
  {
    try {
      $host = env('RABBITMQ_HOST');
      $port = env('RABBITMQ_PORT');
      $user = env('RABBITMQ_USERNAME');
      $password = env('RABBITMQ_PASSWORD');
      $connection = new AMQPStreamConnection($host, $port, $user, $password);
      $channel = $connection->channel();
      // Declare a queue
      // $channel->queue_declare($queueName, false, true, false, false);
      // Convert data to JSON
      // $channel->exchange_declare("amq.topic", 'direct', false, true, false);
      $messageBody = json_encode($data);
      $message = new AMQPMessage($messageBody, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
      // Publish message to queue
      $channel->basic_publish($message, env('CONTROL_EXCHANGE'), env('LIGHT_ONE_ROUTE_KEY'));
      // $channel->basic_publish($message, '', $queueName);
      $channel->close();
      $connection->close();
      return true;
    } catch (\Exception $e) {
      \Log::error('RabbitMQ Publish Error: ' . $e->getMessage());
      return response()->json(["message" => $e->getMessage()]);
    }
  }

  public static function greenHouseLightTwo($queueName, $data)
  {
    try {
      $connection = new AMQPStreamConnection(env('RABBITMQ_HOST'), env('RABBITMQ_PORT'), env('RABBITMQ_USERNAME'), env('RABBITMQ_PASSWORD'));
      $channel = $connection->channel();
      $messageBody = json_encode($data);
      $message = new AMQPMessage($messageBody, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
      $channel->basic_publish($message, env('CONTROL_EXCHANGE'), env('LIGHT_TWO_ROUTE_KEY'));
      $channel->close();
      $connection->close();
      return true;
    } catch (Exception $exception) {
      return response()->json(["message" => $exception->getMessage()]);
    }
  }

  public static function greenHouseLightThree($queueName, $data)
  {
    try {
      $connection = new AMQPStreamConnection(env('RABBITMQ_HOST'), env('RABBITMQ_PORT'), env('RABBITMQ_USERNAME'), env('RABBITMQ_PASSWORD'));
      $channel = $connection->channel();
      $messageBody = json_encode($data);
      $message = new AMQPMessage($messageBody, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
      $channel->basic_publish($message, env('CONTROL_EXCHANGE'), env('LIGHT_THREE_ROUTE_KEY'));
      $channel->close();
      $connection->close();
      return true;
    } catch (Exception $exception) {
      return response()->json(["message" => $exception->getMessage()]);
    }
  }

  public static function greenHouseExhaustFan($queueName, $data)
  {
    try {
      $connection = new AMQPStreamConnection(env('RABBITMQ_HOST'), env('RABBITMQ_PORT'), env('RABBITMQ_USERNAME'), env('RABBITMQ_PASSWORD'));
      $channel = $connection->channel();
      $messageBody = json_encode($data);
      $message = new AMQPMessage($messageBody, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
      $channel->basic_publish($message, env('CONTROL_EXCHANGE'), env('EXHAUST_ROUTE_KEY'));
      $channel->close();
      $connection->close();
      return true;
    } catch (Exception $exception) {
      return response()->json(["message" => $exception]);
    }
  }


  public static function greenHouseWaterMotor($queueName, $data)
  {
    try {
      $connection = new AMQPStreamConnection(env('RABBITMQ_HOST'), env('RABBITMQ_PORT'), env('RABBITMQ_USERNAME'), env('RABBITMQ_PASSWORD'));
      $channel = $connection->channel();
      $messageBody = json_encode($data);
      $message = new AMQPMessage($messageBody, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
      $channel->basic_publish($message, env('CONTROL_EXCHANGE'), env('WATER_TANK'));
      $channel->close();
      $connection->close();
      return true;
    } catch (Exception $exception) {
      return response()->json(["message" => $exception]);
    }
  }
}
