<?php

namespace App\Http\Controllers\UserManageControllers;

use App\Http\Controllers\Controller;
use App\Jobs\TestQueue;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserManageController extends Controller
{
    public function updateUserProfile(Request $request){

      try{
        if(!$request->user() == "NULL"){
        Log::channel('custom')->error(Auth::user()->email.':update user profile:'.'Empty User');

            return response()->json(["message"=>"Empty User"]);
            exit();
        }

        if($request->user()->tokenCan('read-profile')){
        Log::channel('custom')->info(Auth::user()->email.':update user profile:'.'User Profile Updated Successfully');
            return response()->json(["message"=>"User Profile Updated Successfully"],200);

            }else{
                abort(403, 'Unauthorized');
            }
      }catch(Exception $exception){
        return response()->json(["message"=>$exception->getMessage()],406);
      }

    }


    public function activeUser(Request $request){
        try{
            $email = $request->email;
            $state = User::where('email', $email)->first();
            if($state['is_active'] == 1){
        Log::channel('custom')->warning(Auth::user()->email.':User activation:'.'User is already active');

                return response()->json(["message"=>"User is already active"],200);
                exit();
            }
            User::where('email',$email)->update(['is_active'=> 1]);
        Log::channel('custom')->info(Auth::user()->email.':User activation:'.'User activated successfully');

            return response()->json(["message"=>"User activated successfully"],200);

        }catch(Exception $exception){
        Log::channel('custom')->error(Auth::user()->email.':User activation:'.'User activated successfully');

            return response()->json(["message"=>$exception->getMessage()],406);
        }
    }

    public function deactivateUser(Request $request){
        try{
            $email= $request->email;
            $state = User::where('email', $email)->first();
            if($state['is_active'] == 0){
        Log::channel('custom')->warning(Auth::user()->email.':User deactivation:'.'User is already deactivated');

                return response()->json(["message"=>"User is already deactivated"],200);
                exit();
            }
            User::where('email',$email)->update(['is_active' => 0]);
        Log::channel('custom')->info(Auth::user()->email.':User deactivation:'.'User deactivated successfully');

            return response()->json(["message"=>"User deactivated successfully"],200);
        }catch(Exception $exception){

        }
    }


}
