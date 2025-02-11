<?php

namespace App\Http\Controllers\UserManageControllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class UserManageController extends Controller
{
    public function updateUserProfile(Request $request){

      try{

        if(!$request->user() == "NULL"){
            return response()->json(["message"=>"Empty User"]);
            exit();
        }

        if($request->user()->tokenCan('read-profile')){
            return response()->json(["message"=>"User Profile Updated Successfully"],200);

            }else{
                abort(403, 'Unauthorized');
            }
      }catch(Exception $exception){
        return response()->json(["message"=>$exception->getMessage()],406);
      }

    }
}
