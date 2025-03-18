<?php

namespace App\Http\Controllers\RabbitMq;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QueueNexchangeController extends Controller
{
    // make exchange
    public function MakeExchange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'type' => 'required|string',
            'durable' => 'required',
            'vhost' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->all(), 'status' => 406]);
        }
        $name = escapeshellarg($request->name);
        $type = escapeshellarg($request->type);
        $durable = escapeshellarg($request->durable);
        $vhost = escapeshellarg($request->vhost);

        $result = shell_exec('rabbitmqadmin declare exchange --vhost=' . $vhost . ' name=' . $name . ' type=' . $type . ' durable=' . $durable);
        if ($result == NULL) {
            return response()->json(['message' => 'Exchange Failed', 'status' => 406]);
        }
        return response()->json(['message' => 'Exchange created successfully', 'status' => 200]);
    }

    //make queue
    public function MakeQueue(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'queue_name' => 'required|string',
            'durable' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->all(), 'status' => 406]);
        }
        $queueName = escapeshellarg($request->queue_name);
        $durable = escapeshellarg($request->durable);
        $result = shell_exec('rabbitmqadmin declare queue name=' . $queueName . ' durable=' . $durable);
        if ($result === "queue declared") {
            return response()->json(['message' => 'Queue created successfully', 'status' => 200]);
        } else {
            return response()->json(['message' => 'Queue creation failed', 'status' => 406]);
        }
    }


    //delete exchange
    public function DeleteExchange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'vhost' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->all(), "status" => 406]);
        }
        $name = escapeshellarg($request->name);
        $vhost = escapeshellarg($request->vhost);
        $command = "rabbitmqadmin --vhost=" . $vhost . " delete exchange name=" . $name;
        $result = shell_exec($command);
        if ($result == NULL) {
            return response()->json(["message" => "Failed to delete exchange", "status" => 406]);
        } else {
            return response()->json(["message" => "Exchange deleted successfully", "status" => 200]);
        }
    }

    #Q Deleting method start
    //1st try to delete queue if empty
    public function DeleteQueue(Request $request)
    {
        $validator = validator::make($request->all(), [
            'queue_name' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->all(), "status" => 404]);
        }
        $queueName = escapeshellarg($request->queue_name);
        $checkRunningQueue = shell_exec('rabbitmqadmin get queue=' . $queueName);


        if ($checkRunningQueue == NULL) {
            return response()->json(["message" => $queueName . " Queue not found", "status" => 404]);
        }
        if (trim($checkRunningQueue) == "No items") {
            $result = shell_exec('rabbitmqctl delete_queue' . ' ' . $queueName . ' ' . '--formatter=json');
            return $result;
        } else {
            return response()->json(["message" => "This queue is running.If you want to delete this queue,please use force delete option", "status" => 404]);
        }
    }

    //force delete request for queue
    public function RequestForceDeleteQueue()
    {
        return response()->json(["message" => "Are you sure delete this queue?", "status" => 200]);
    }

    public function QDeleteConfirmed(Request $request)
    {
        $validator = validator::make($request->all(), [
            'queue_name' => 'required',
            'choose' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->all(), "status" => 406]);
        }
        if ($request->choose == "yes") {
            $queueName = escapeshellarg($request->queue_name);
            return $this->ConfirmForceDeleteQueue($queueName);
        } else {
            return response()->json(["message" => "Queue deletion cancelled", "status" => 200]);
        }
    }

    //confirm force delete queue
    public function ConfirmForceDeleteQueue($queueName)
    {
        $result = shell_exec('rabbitmqctl delete_queue' . ' ' . $queueName . ' ' . '--formatter=json');
        if ($result == null) {
            return response()->json(["message" => "Something went wrong", "status" => 404]);
        }
        $result = json_decode($result, true);
        return  $result;
    }

    #Q Deleting method end
}
