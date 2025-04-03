<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\contact;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class ContactController extends Controller
{

    //get register users list
    public function userList(){
     try{
        $userList = array();
        $result = User::all();
        foreach($result as $list){
            $data = [
                "id" => $list->id,
                "email" => $list->email,
                "fName" => $list->first_name
            ];
        }
        array_push($userList , $data);
        Log::channel('custom')->info(Auth::user()->email. ':contact:' . ' Show all users');
        return response()->json(["message"=>$userList,"status"=>200]);
     }catch(Exception $exception){
        Log::channel('custom')->info(Auth::user()->email. ':contact:' . ' Show all users');
        return response()->json(["message"=>$exception->getMessage(),"status"=>401]);
     }
    }



    //update user's contact details\ not complete
    public function addUserContact(Request $request){
       try{
        $validation = Validator::make($request->all(),[
            "id" =>"",
            "fName" => 'required',
            "mobile" => 'min:10',
            "email" => '',
        ]);

        if($validation->fails()){
        Log::channel('custom')->info(Auth::user()->email. ':contact:' . $validation->errors()->all());

            return response()->json(["message"=>$validation->errors()->all(),"status"=>406]);
        }
        $update = contact::where('email' , $request->email)
                 ->update(['mobile'=>$request->mobile,'email'=>$request->email]);
        Log::channel('custom')->info(Auth::user()->email. ':contact:' . 'Update user contact details');
        return $update;
    //   $update = thresholds::where('sensor_name', $request->sensor_name)->update(['is_enable_notify' => (int)$request->is_enable_notify]);
       }catch(Exception $exception){
        Log::channel('custom')->info(Auth::user()->email. ':contact:' . $exception->getMessage());
        return response()->json(["message"=>$exception->getMessage(),"status"=>406]);
       }

    }
}
