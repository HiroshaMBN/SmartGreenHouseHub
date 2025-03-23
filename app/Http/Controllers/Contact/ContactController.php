<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\contact;
use Exception;

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
        return response()->json(["message"=>$userList,"status"=>200]);
     }catch(Exception $exception){
        return response()->json(["message"=>$exception->getMessage(),"status"=>401]);
     }
    }



    //update user's contact details\ not complete
    public function addUserContact(Request $request){
        $validation = Validator::make($request->all(),[
            "id" =>"",
            "fName" => 'required',
            "mobile" => 'min:10',
            "email" => '',
        ]);

        if($validation->fails()){
            return response()->json(["message"=>$validation->errors()->all(),"status"=>406]);
        }
        $update = contact::where('email' , $request->email)
                 ->update(['mobile'=>$request->mobile,'email'=>$request->email]);
        return $update;

    //   $update = thresholds::where('sensor_name', $request->sensor_name)->update(['is_enable_notify' => (int)$request->is_enable_notify]);


    }
}
