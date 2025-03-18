<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQConsumer extends Command
{
    protected $signature = 'rabbitmq:consume';
    protected $description = 'Consume data from RabbitMQ';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST'),
            env('RABBITMQ_PORT'),
            env('RABBITMQ_USERNAME'),
            env('RABBITMQ_PASSWORD'),
            env('RABBITMQ_VHOST')
        );

        $channel = $connection->channel();
        //dht11 queue
        $channel->queue_declare('sensor/dht11', false, true, false, false);
        //mq2 queue
        $channel->queue_declare('sensor/mq2', false, true, false, false);
        //soil moisture queue
        $channel->queue_declare('sensor/soil',false,true,false,false);


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

        $callback3 = function($msg3){
            $data3 = json_decode($msg3->body, true);
            \App\Models\soilMoisture::create([
                'Level' => $data3['Level'],
                'status' => $data3['status']
            ]);
        };

        $channel->basic_consume('dht11_queue', '', false, true, false, false, $callback);
        $channel->basic_consume('sensor/mq2', '', false, true, false, false, $callback2);
        $channel->basic_consume('sensor/soil','',false,true,false,false,$callback3);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}
