<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Notification\notificationController;

class sendTemperatureNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:temNotify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Temperature Notification';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
     // Execute the console command
    public function handle()
    {
        // Call the sendTemperatureNotification method
        $controller = new notificationController();
        $response = $controller->sendTemperatureNotification();

        // Log the response or output to console
        if ($response === true) {
            $this->info('Temperature notification trigger successfully.');
        } else {
            $this->error('Temperature notification trigger failed.');
        }
    }
}
