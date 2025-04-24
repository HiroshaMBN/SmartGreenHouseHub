<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Notification\notificationController;


class sendAirQualityNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:mq2Notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
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
        $response = $controller->airQualityNotification();
          
         if ($response === true) {
            $this->info('Air quality notification trigger successfully.');
        } else {
            $this->error('Air quality notification trigger failed.');
        }

    }
}
