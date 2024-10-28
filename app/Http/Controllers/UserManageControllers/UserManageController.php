<?php

namespace App\Http\Controllers\UserManageControllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class UserManageController extends Controller
{
    //create user for the system
    public function createUser(Request $request){
      try{
        $instanceId = 1; //instance for testing instance id
        $firstName = $request->input("firstName");
        $lastName = $request->input("lastName");
        $email = $request->input("email");

        $createUser = User::create([
            'instance_id' =>$instanceId,
            'contact_id' => NULL,
            'first_name' =>$firstName,
            'last_name' =>$lastName,
            'email' =>$email
        ]);

        $createUser->save();

        return response()->json(["message" =>"User Created Success","status"=>201]);

      }catch(Exception $exception){
        return response()->json(["message"=> $exception->getMessage(), "status"=>500]);
      }


    }
}
