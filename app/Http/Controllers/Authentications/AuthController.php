<?php

namespace App\Http\Controllers\Authentications;

use App\Http\Controllers\Controller;
use App\Models\contact;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\HasApiTokens;

class AuthController extends Controller
{
    //register users
    public function userRegister(Request $request)
    {
        try {
            $instance_id = 1;
            //user input values validation
            $validator = Validator::make($request->all(), [
                // 'instance_id' => 'integer',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'mobile' => 'string|min:10',
            ]);
            $request['instance_id'] = 1;
            //validation fails
            if ($validator->fails()) {
            Log::channel('custom')->info($request->email.':user registration:'.$validator->errors()->all());
                return response()->json(['message' => $validator->errors()->all(), 'status' => 406]);
            }
            $request['password'] = Hash::make($request['password']);
            //  $request['remember_token'] = Str::random(10);
            $contact = contact::created($request->toArray());
            $user = User::create($request->toArray());
            // $token = $user->createToken('GreenHouseMainAPI')->accessToken;
            $token = $user->createToken('GreenHouseMainAPI', ['read-profile'])->accessToken;
            // $token = $user->createToken('GreenHouseMainAPI', ['read-profile', 'read-profile'])->accessToken;
            $response = ['token' => $token];
            Log::channel('custom')->info($user->email.':user registration:'.'User logged in successfully');
            return response()->json([
                "token" => $response,
                "message"=>"User created successfully"
            ]);
            return response($response, 200);
        } catch (Exception $exception) {
            // Http error code 406 is Not Acceptable error message
            Log::channel('custom')->error($user->email.':user registration:'. $exception->getMessage());
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => 406
            ]);
        }
    }

    // Login user function
    public function LogInUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6'
            ]);
            if ($validator->fails()) {
                Log::channel('custom')->error($request->email.':user login:'.$validator->errors()->all());
                return response()->json(['message' => $validator->errors()->all(), 'status' => 406]);
                // return response()->json(['message' =>$validator->errors()->all()],422);
            }
            $user = User::where('email', $request->email)->first();
            if($user['is_active'] == 0){
                Log::channel('custom')->error($request->email.':user login:'.'User is not active. Please contact the System Admin');
                return response()->json([
                    "message" => "User is not active. Please contact the System Admin",
                    "status" => 406
                ]);
                exit();
            }

            if ($user) {
                if (Hash::check($request->password, $user->password)) {
                    $token = $user->createToken('GreenHouseMainAPI',['read-profile'])->accessToken;
                    $response = ['token' => $token];
                    Log::channel('custom')->info($user->email.':user login:'.'User logged in successfully');
                    return response()->json([
                        "token" => $response,
                        "userId" => auth(),
                        "userName" => $user->email,
                        "status" => 200,
                        "message"=>"User logged in successfully"
                    ]);
                } else {
                    Log::channel('custom')->error($user->email.':user login:'. 'Password mismatch');
                    return response()->json(["message" => "Password mismatch", "status" => 422]);
                }
            } else {
                Log::channel('custom')->error($user->email.':user login:'.' user does not exist');
                return response(["message" => "user does not exist", "status" => 404]);
            }
        } catch (Exception $exception) {
            // Http error code 406 is Not Acceptable error message
            Log::channel('custom')->error($user->email.':user login:'.$exception->getMessage());

            return response()->json([
                "message" => $exception->getMessage(),
                "line" => $exception->getLine(),
                "status" => 406
            ]);
        }
    }

    //Logout function Not working right now
    public function LogOut(Request $request)
    {
        try {

            $token = $request->user()->token();
            $token->revoke();
            Log::channel('custom')->info($request->user().':logout:'.'User log out successfully');

            return response()->json([
                "message" => "You have been successfully logged out",
                "status" => 200
            ]);
        } catch (Exception $exception) {
            Log::channel('custom')->error($request->user().':logout:'.$exception->getMessage());

            return response()->json([
                "message" => $exception->getMessage(),
                "status" => 406
            ]);
        }
    }
}
