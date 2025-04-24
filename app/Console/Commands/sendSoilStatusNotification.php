<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Notification\notificationController;

class sendSoilStatusNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:soilNotify';

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
        $response = $controller->soilQualityNotification();
          
         if ($response === true) {
            $this->info('Soil level notification trigger successfully.');
        } else {
            $this->error('Soil level notification trigger failed.');
        }
    }
}
