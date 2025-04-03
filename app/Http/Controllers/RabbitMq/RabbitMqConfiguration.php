<?php

namespace App\Http\Controllers\RabbitMq;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
class RabbitMqConfiguration extends Controller
{



    //list available vshot in rabbitmq
    public function ListVHosts()
    {
        $result = shell_exec('rabbitmqctl list_vhosts --formatter=json');
        $VHostList = json_decode($result, true);
        return response()->json(["message" => $VHostList, "status" => 200]);
    }
    //make a virtual host in rabbitmq
    public function MakeVHost(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'hostName' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->all(), 'status' => 406]);
            }

            $result = shell_exec('sudo rabbitmqctl add_vhost ' . $request->hostName);

            return response()->json([
                "message" => $result,
                "status" => 200
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => 406
            ]);
        }
    }

    //delete virtual host in rabbitmq (improvements needs)
    public function DeleteVhost(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'hostName' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->all(), 'status' => 406]);
            }
            $result = shell_exec('sudo rabbitmqctl delete_vhost' . ' ' . $request->hostName);
            return response()->json(["message" => $request->hostName . ' virtual host deleted', "status" => 200]);
        } catch (Exception $exception) {
        }
    }

    //getUser list
    public function UserList()
    {
        try {
            $result = shell_exec('sudo rabbitmqctl list_users --formatter=json');
            $UserList = json_decode($result, true);
            return response()->json(["message" => $UserList, "status" => 200]);
        } catch (Exception $exception) {
            return response()->json(["message" => $exception->getMessage(), "status" => 406]);
        }
    }

    //create a user with password
    public function CreateUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'userName' => 'required',
                'password' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->all(), 'status' => 406]);
            }
            $result = shell_exec('rabbitmqctl add_user' . ' ' . $request->userName . ' ' . $request->password);
            return response()->json(["message" => $result, "status" => 200]);
        } catch (Exception $exception) {
            return response()->json(["message" => $exception->getMessage(), "status" => 406]);
        }
    }

    //create rabbitmq Tags list. store it database and return

    //add Tags to rabbitmqUsers
    public function AddTags(Request $request)
    {
        try {
            // Validate the incoming request data
            $validator = Validator::make($request->all(), [
                "userName" => "required",
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->all(), 'status' => 422]);
            }
            $userName = escapeshellarg($request->userName);
            if ($request->tags == 'none') {
                $tags = ' ';
            } else {
                $tags = escapeshellarg($request->tags);
            }
            $command = 'sudo rabbitmqctl set_user_tags ' . $userName . ' ' . $tags;
            $result = shell_exec($command);
            return response()->json(["message" => $result, "status" => 200]);
        } catch (Exception $exception) {
            return response()->json(["message" => $exception->getMessage(), "status" => 500]); // Use 500 for server errors
        }
    }
    // set rabbitmq permissions user's and related host
    //Note : ConfigureRegexp , WriteRegexp , ReadRegexp values should be comes from database (.* is all)
    public function CanAccessVirtualHosts(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "hostName" => "required",
            "userName" => "required",
            "ConfigureRegexp" => "required",
            "WriteRegexp" => "required",
            "ReadRegexp" => "required"
        ]);
        if ($validation->fails()) {
            return response()->json(["message" => $validation->errors()->all(), "status" => 422]);
        }
        $hostName = escapeshellarg($request->hostName);
        $userName = escapeshellarg($request->userName);
        $ConfigureRegexp = escapeshellarg($request->ConfigureRegexp);
        $WriteRegexp = escapeshellarg($request->WriteRegexp);
        $ReadRegexp = escapeshellarg($request->ReadRegexp);

        $result = shell_exec('sudo rabbitmqctl set_permissions -p ' . $hostName . ' ' . $userName . ' ' . $ConfigureRegexp . ' ' . $WriteRegexp . ' ' . $ReadRegexp);
        return response()->json(["message" => $result, "status" => 200]);
    }

    // // restart rabbitmq server
    public function RestartRabbitMq()
    {
        $output = shell_exec('sudo service rabbitmq-server restart');
        // var_dump($output);
        if ($output == NULL) {
            return response()->json(['message' => 'RabbitMq server restarted'], 200);
        } else {
            return response()->json(['message' => 'RabbitMq server restart failed'], 406);
        }
    }
    public function overView(){
      return  $output = shell_exec("rabbitmqctl status --formatter=json");
        if($output == NULL){
            return response()->json(['message' => 'RabbitMq server restart failed'], 406);
        }else{
            var_dump($output);die();
          return  $VHostList = json_decode($output, true);
            $listeners = $VHostList['listeners'] ?? [];
            return response()->json(["message"=>$listeners]);
        }
    }

    public function showConnection(){
      $output = shell_exec("rabbitmq-diagnostics list_connections ");
      // $json_output = json_encode($output, true);
      return $output;
    }

    public function Terminal(Request $request){
      $command = $request->query('cmd');
      // $allowedCommands = ['ls', 'uptime', 'df -h', 'whoami','date','systemctl status rabbitmq-server.service'];
    //   if (!in_array($command, $allowedCommands)) {
    //     return response()->json(['error' => 'Command not allowed.Contact System Administrator'], 403);
    // }
    $process = new Process([$command]);
    $process->run();
    if (!$process->isSuccessful()) {
      return response()->json(['error' => $process->getErrorOutput()], 500);
  }

  return response()->json(['output' => $process->getOutput()]);
    }
}
