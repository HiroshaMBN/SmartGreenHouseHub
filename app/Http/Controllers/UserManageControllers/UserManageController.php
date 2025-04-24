<?php

namespace App\Http\Controllers\UserManageControllers;

use App\Http\Controllers\Controller;
use App\Jobs\TestQueue;
use App\Models\User;
use App\Models\contact;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UserManageController extends Controller
{

    public function showUsersEmail()
    {
        try {
            $result = User::get();
            return response()->json(["message" => $result, "status" => 200]);
        } catch (Exception $exception) {
            return response()->json(["message" => $exception->getMessage(), "status" => 406]);
        }
    }
    public function showUserDetails(Request $request)
    {
        $email = $request->email;

        $users = DB::table('users')
            ->select('users.first_name', 'users.last_name', 'users.email', 'contacts.mobile', 'contacts.email')
            ->join('contacts', 'users.id', '=', 'contacts.user_id')
            ->where('users.email', $email)
            ->get();
        return response()->json(["message" => $users, "status" => 200]);
    }


    public function updateUserProfile(Request $request)
    {
        $firstName = $request->first_name;
        $lastName = $request->last_name;
        $email = $request->email;
        $mobile = $request->mobile;

        try {
            // Check if the user is logged in
            if (!$request->user()) {
                Log::channel('custom')->error(Auth::user()->email . ':update user profile:' . 'Empty User');
                return response()->json(["message" => "Empty User"]);
            }

            // Check if the user has permission to update profile
            if ($request->user()->tokenCan('read-profile')) {
                Log::channel('custom')->info(Auth::user()->email . ':update user profile:' . 'User Profile Updated Successfully');

                // Get the user ID from the email
                $user = User::where('email', $email)->first();
                if (!$user) {
                    return response()->json(["message" => "User not found"], 404);
                }

                // Update query using parameter binding to prevent SQL injection
                DB::statement("
                UPDATE users
                INNER JOIN contacts ON users.id = contacts.user_id
                SET
                    users.first_name = ?, 
                    users.last_name = ?, 
                    contacts.mobile = ?, 
                    contacts.email = ?
                    WHERE users.id = ?
            ", [
                    $firstName,
                    $lastName,
                    $mobile,
                    $email,
                    $user->id
                ]);

                return response()->json(["message" => "User Profile Updated Successfully"], 200);
            } else {
                abort(403, 'Unauthorized');
            }
        } catch (Exception $exception) {
            return response()->json(["message" => $exception->getMessage()], 406);
        }
    }



    public function updateUserStatus(Request $request)
    {
        try {
            $email = $request->email;
            $activeState = $request->activation;
            $state = User::where('email', $email)->first();
            if ($activeState == 1) {
                if ($state['is_active'] == $activeState) {
                    Log::channel('custom')->warning(Auth::user()->email . ':User activation:' . 'User is already active');
                    return response()->json(["message" => $email." is already active","status" => "alActive"]);
                    exit();
                }
                User::where('email', $email)->update(['is_active' => $activeState]);
                Log::channel('custom')->info(Auth::user()->email . ':User activation:' . 'User activated successfully');
                return response()->json(["message" => $email." is activated successfully", "status" => "active"]);
            } else if ($activeState == 0) {
                if ($state['is_active'] == $activeState) {

                    Log::channel('custom')->warning(Auth::user()->email . ':User activation:' . 'User is already deactivated');
                    return response()->json(["message" => $email." is already deactivated", "status" => "AlDeactivate"]);
                    exit();
                }
                User::where('email', $email)->update(['is_active' => $activeState]);
                Log::channel('custom')->info(Auth::user()->email . ':User activation:' . 'User deactivated successfully');
                return response()->json(["message" => $email." is deactivated successfully", "status" => "deactivated"]);
            }
        } catch (Exception $exception) {
            Log::channel('custom')->error(Auth::user()->email . ':User activation:' . $exception->getMessage());
            return response()->json(["message" => $exception->getMessage(), "line" => $exception->getLine()], 406);
        }
    }

    //use activation status
    public function activationStatus(Request $request){
        try{
            $email = $request->email;
            $result = User::where('email', $email)->first();
            $status = ($result->is_active ==1)?"Activated":"Deactivated";
            return response()->json(["message"=>$email." is currently ".$status,"status"=>200]);
        }catch(Exception $exception){
            return response()->json(["message"=>$exception->getMessage()]);

        }
    }

    public function deactivateUser(Request $request)
    {
        try {
            $email = $request->email;
            $state = User::where('email', $email)->first();
            if ($state['is_active'] == 0) {
                Log::channel('custom')->warning(Auth::user()->email . ':User deactivation:' . 'User is already deactivated');

                return response()->json(["message" => "User is already deactivated"], 200);
                exit();
            }
            User::where('email', $email)->update(['is_active' => 0]);
            Log::channel('custom')->info(Auth::user()->email . ':User deactivation:' . 'User deactivated successfully');

            return response()->json(["message" => "User deactivated successfully"], 200);
        } catch (Exception $exception) {
        }
    }
}
