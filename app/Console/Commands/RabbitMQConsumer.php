<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

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
        '10.128.1.52',
        5672,
        'guest',
        'guest',
        '/'
      );

      $channel = $connection->channel();
      //dht11 queue
      $channel->queue_declare('sensor/dht11', false, true, false, false);
      //mq2 queue
      $channel->queue_declare('sensor/mq2', false, true, false, false);
      //soil moisture queue
      $channel->queue_declare('sensor/soil', false, true, false, false);
      //on off trigger
      // $channel->queue_declare('mqtt-subscription-ESP8266Client-4ff3qos0',false,true,false,false);
      $channel->queue_declare('device_control', false, true, false, false);
      echo " [*] Waiting for messages in queue:device_control\n";

      // echo " [*] Waiting for messages. To exit press CTRL+C\n";
      $callback = function ($msg) {
        // echo ' [x] Received ', $msg->body, "\n";

        $data = json_decode($msg->body, true);

        \App\Models\Climate::create([
          'temperature' => $data['temperature'],
          'humidity' => $data['humidity'],
        ]);
      };


      $callback2 = function ($msg2) {
        // echo ' [x] Received ', $msg2->body, "\n";
        $data2 = json_decode($msg2->body, true);
        \App\Models\airCondition::create([
          'value' => $data2['Value'],
          'status' => $data2['status']
        ]);
      };

      $callback3 = function ($msg3) {
        $data3 = json_decode($msg3->body, true);
        \App\Models\soilMoisture::create([
          'Level' => $data3['Level'],
          'status' => $data3['status']
        ]);
      };

      $callback4 = function (AMQPMessage $msg) {
        echo " [x] Received: ", $msg->body, "\n";
        // echo " Custom Value: ", $this->customValue, "\n"; // Example of passing data
        $msg->ack();
      };


      $channel->basic_consume('sensor/dht11', '', false, true, false, false, $callback);
      $channel->basic_consume('sensor/mq2', '', false, true, false, false, $callback2);
      $channel->basic_consume('sensor/soil', '', false, true, false, false, $callback3);
      //read message from q to
      $channel->basic_consume('device_control', '', false, false, false, false, $callback4);

      while ($channel->is_consuming()) {
        $channel->wait();
      }

      $channel->close();
      $connection->close();
    } catch (Exception $e) {
      echo $e->getMessage();
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

  public static function greenHouseExhaustFan($queueName,$data){
    try{
      $connection = new AMQPStreamConnection(env('RABBITMQ_HOST'), env('RABBITMQ_PORT'), env('RABBITMQ_USERNAME'), env('RABBITMQ_PASSWORD'));
      $channel = $connection->channel();
      $messageBody = json_encode($data);
      $message = new AMQPMessage($messageBody,['delivery_mode'=>AMQPMessage::DELIVERY_MODE_PERSISTENT]);
      $channel->basic_publish($message,env('CONTROL_EXCHANGE'), env('EXHAUST_ROUTE_KEY'));
      $channel->close();
      $connection->close();
      return true;
    }catch(Exception $exception){
      return response()->json(["message"=>$exception ]);
    }
  }
}
