<?php

namespace App\Http\Controllers;

use App\Models\AdminDetailsModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;

class SuperAdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
        $this->middleware('auth');
        $token = JWTAuth::getToken();
        $apy = JWTAuth::getPayload($token)->toArray();
        if ($apy['user_type'] != 'super_admin') {
            echo "user is not a super admin";
            auth()->logout(true);
        }
    }
    public function getCurrentUser()
    {
        $token = JWTAuth::getToken();
        $apy = JWTAuth::getPayload($token)->toArray();
        return $apy['user_type'];
    }

    public function getCurrentUserId()
    {
        $token = JWTAuth::getToken();
        $apy = JWTAuth::getPayload($token)->toArray();
        return $apy['user_id'];
    }

    public function sendMail($userId,$password, $to)
    {
        Mail::raw('Greeetings from AUSB your login credentials are       
        User ID:'.$userId.'
        Password: '.$password.'    ', function ($message) use ($to) {
            $message->to($to)
              ->subject("AUSB User Login Credentials");
          });
    }

    public function getUsers()
    {
        return response()->json(User::all());
    }

    public function generateRandomString($length = 6)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function createUserLogin(Request $request)
    {
        $this->validate($request, [
            'employee_id' => 'required|string',
        ]);
        try {

            $user = new User;
            $user->user_id = $request->input('employee_id');
            $plainPassword = $this->generateRandomString();
            $user->password = app('hash')->make($plainPassword);
            $user->user_type = "admin";
            $user->disabled = 0;
            $user->created_by = $this->getCurrentUser();
            $user->save();

            return $plainPassword;

        } catch (\Exception $e) {

            return response()->json(['exception' => $e, 'message' => 'User Registration Failed!'], 409);
        }
    }

    public function createUser(Request $request)
    {
        $this->validate($request, [
            'employee_id' => 'required|string|unique:admin_details|unique:login,user_id',
            'name' => 'required|string',
            'position' => 'required|string',
            'email' => 'required|email|unique:admin_details',
            'contact_number' => 'required|numeric|unique:admin_details',
            // 'password' => 'required|confirmed',
        ]);

        try {

            $admin = new AdminDetailsModel;
            $admin->employee_id = $request->input('employee_id');
            $admin->name = $request->input('name');
            $admin->position = $request->input('position');
            $admin->email = $request->input('email');
            $admin->contact_number = $request->input('contact_number');
            $admin->created_by = "super_admin";

            $admin->save();
            $create_login = $this->createUserLogin($request);
            $this->sendMail($request->input('employee_id'),$create_login,$request->input('email'));
          
           return response()->json(['Admin' => $admin, "login" => $create_login, 'message' => 'CREATED'], 201);

        } catch (\Exception $e) {

            return response()->json(['exception'=>$e, 'message' => 'User Registration Failed!'], 409);
        }
    }
   

    public function editUser(Request $request)
    {
        $this->validate($request, [
            'id'=>'required|numeric',
            'employee_id' => 'required|string',
            'name' => 'required|string',
            'position' => 'required|string',
            'email' => 'required|email',
            'contact_number' => 'required|numeric',
            // 'password' => 'required|confirmed',
        ]);

         try {

            $councilMembers = AdminDetailsModel::where('id', $request->input('id'))->update(
                [
                    'name'=>$request->input('name'),
                    'position'=>$request->input('position'),
                    'email'=>$request->input('email'),
                    'contact_number'=>$request->input('contact_number'),
                    'updated_by' => $this->getCurrentUserId()
                ]);
          
           return response()->json(['message' => 'UPDATED'], 201);

        } catch (\Exception $e) {

            return response()->json(['exception'=>$e, 'message' => 'Update Failed!'], 409);
        } 
    }
    public function allAdmin()
    {
        $admin = AdminDetailsModel::get();
        foreach($admin as $a)
        {
            $disabled=User::where('user_id',$a['employee_id'])->first();
            $a['disabled']=$disabled['disabled'];
        }
        return $admin;
    }

    public function disableAdmin($id)
    {
        $admin=AdminDetailsModel::select('employee_id')->where('id',$id)->first();
        $login = User::where('user_id',$admin['employee_id'])->update(['disabled'=>1]);
        if($login==1)
       {
           return response()->json(['message' => 'DISABLED'], 201); 
       } 
       else {
           return response()->json(['message' => 'ERROR'], 409); 
       }
    }
    public function enableAdmin($id)
    {
        $admin=AdminDetailsModel::select('employee_id')->where('id',$id)->first();
        $login = User::where('user_id',$admin['employee_id'])->update(['disabled'=>0]);
        if($login==1)
       {
           return response()->json(['message' => 'ENABLED'], 201); 
       } 
       else {
           return response()->json(['message' => 'ERROR'], 409); 
       }
    }



    public function disableUser($id)
    {
        $login = User::where('id',$id)->update(['disabled'=>1]);
         if($login==1)
        {
            return response()->json(['message' => 'DISABLED'], 201); 
        } 
        else {
            return response()->json(['message' => 'ERROR'], 409); 
        }
    }

    public function enableUser($id)
    {
        $login = User::where('id',$id)->update(['disabled'=>0]);
         if($login==1)
        {
            return response()->json(['message' => 'ENABLED'], 201); 
        } 
        else {
            return response()->json(['message' => 'ERROR'], 409); 
        }
    }


  



}
