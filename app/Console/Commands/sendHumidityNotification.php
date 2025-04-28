<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Notification\notificationController;


class sendHumidityNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:humidNotify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Humidity Notification';
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
         
        $controller = new notificationController();
        $response = $controller->sendHumidityNotification();
          
         if ($response === true) {
            $this->info('Humidity notification trigger successfully.');
        } else {
            $this->error('Humidity notification trigger failed');
        }

    }
}
