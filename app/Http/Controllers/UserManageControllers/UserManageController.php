<?php

namespace App\Http\Controllers\UserManageControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserManageController extends Controller
{
    //create user for the system
    public function createUser(Request $request){
        $instanceId = 1;
        $firstName = $request->input("firstName");
        $lastName = $request->input("lastName");
        $email = $request->input("email");


    }
}
