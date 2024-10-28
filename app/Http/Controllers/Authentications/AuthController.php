<?php

namespace App\Http\Controllers\Authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    //register users
    public function userRegister(Request $request){
        $validator = $request->validate([
            'name'=> 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed'
        ]);


        // $input = $request->all();
        // $input['password'] = Hash::make($input['password']);
        // $user = User::create($input);
        // $success['token'] = $user->createToken('MY_New_Project')->accessToken;
        // $success['message'] = "User Registration Successfully!";
        // return response()->json(['data' => $success], 200);
    }
}
