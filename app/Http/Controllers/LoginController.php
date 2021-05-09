<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use  App\Models\User;
use Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
        //$this->middleware('auth');
    }

    public function login(Request $request)
    {
          //validate incoming request 
        $this->validate($request, [
            'user_id' => 'required|string',
            'password' => 'required|string',
        ]);
        $request['disabled']="0";
        $credentials = $request->only(['user_id', 'password','disabled']);
        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $user_type=User::select('user_type')->where('user_id',$request['user_id'])->first();
        return $this->respondWithToken($token,$user_type); 
    }

    public function getCurrentUserId()
    {
        $token = JWTAuth::getToken();
        $apy = JWTAuth::getPayload($token)->toArray();
        return $apy['user_id'];
    }

    public function passwordChangeVerifier($request)
    {   
        $credentials = $request;

        if (! $token = Auth::attempt($credentials)) {
            return 'Unauthorized';
        }
        return  'Authorized';
    }

    public function changePassword(Request $request)
    {
        if($request->input('newPassword')!=$request->input('confrimPassword')){
            return response()->json("confrim Password doesnot match");
        }
        $userId= $this->getCurrentUserId();
        $values=[
            "user_id" => $this->getCurrentUserId(),
            "password" => $request->input('oldPassword')
        ];
        $response=$this->passwordChangeVerifier($values);
        if($response==='Authorized'){
           $password=app('hash')->make($request->input('newPassword'));
           try{
            User::where("user_id",$userId)->update(array('password' => $password));
            return response()->json("password changed successfully");
           } 
           catch (\Exception $e) {
                return $e;
           }
        }
        else {
            return response()->json("Wrong old password!!");
        }
        echo $request->input('newPassword');
        return app('hash')->make($request->input('newPassword'));
        
    }

  

    public function test()
    {
        return "yesah";
    }

    //
}
