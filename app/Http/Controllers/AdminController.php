<?php

namespace App\Http\Controllers;

use App\Models\AdminDetailsModel;
use App\Models\CollegeModel;
use App\Models\GameModel;
use App\Models\User;
use App\Models\ZonalCoordinatorModel;
use App\Models\ZoneModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminController extends Controller
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
        if ($apy['user_type'] != 'admin') {
            echo "user is not a admin";
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

    public function sendMail($userId, $password, $to)
    {
        Mail::raw('Greeetings from AUSB your login credentials are
        User ID:' . $userId . '
        Password: ' . $password . '    ', function ($message) use ($to) {
            $message->to($to)
                ->subject("AUSB User Login Credentials");
        });
    }

    public function getAllZones()
    {
        $zone = ZoneModel::select('id', 'zone_no', 'zone_name')->get();
        return $zone;
    }

    public function createZone(Request $request)
    {
        $this->validate($request, [
            'zone_no' => 'required|unique:zone',
            'zone_name' => 'required|string',
        ]);

        try {

            $zone = new ZoneModel;
            $zone->zone_no = $request->input('zone_no');
            $zone->zone_name = $request->input('zone_name');
            $zone->created_by = $this->getCurrentUserId();

            $zone->save();
            return response()->json(['zone' => $zone, 'message' => 'CREATED'], 201);

        } catch (\Exception $e) {

            return response()->json(['exception' => $e, 'message' => 'User Registration Failed!'], 409);
        }
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

    public function createUser(Request $request, $user_type)
    {
        $this->validate($request, [
            'email' => 'required|email|unique:login,user_id',
        ]);
        try {

            $user = new User;
            $user->user_id = $request->input('email');
            $plainPassword = $this->generateRandomString();
            $user->password = app('hash')->make($plainPassword);
            $user->user_type = $user_type;
            $user->disabled = 0;
            $user->created_by = $this->getCurrentUserId();
            $user->save();

            return $plainPassword;

        } catch (\Exception $e) {

            return response()->json(['exception' => $e, 'message' => 'User Registration Failed!'], 409);
        }
    }


    public function createZonalCoordinator(Request $request)
    {
        $this->validate($request, [
            'zone_id' => 'required|unique:zonal_coordinator',
            'name' => 'required|string',
            'contact_number' => 'required|numeric|unique:zonal_coordinator',
            'email' => 'required|email|unique:zonal_coordinator|unique:login,user_id',
        ]);
        try {

            $zonalCoordinator = new ZonalCoordinatorModel;
            $zonalCoordinator->zone_id = $request->input('zone_id');
            $zonalCoordinator->name = $request->input('name');
            $zonalCoordinator->contact_number = $request->input('contact_number');
            $zonalCoordinator->email = $request->input('email');
            $zonalCoordinator->created_by = $this->getCurrentUserId();

            $zonalCoordinator->save();
            $password = $this->createUser($request, "zonalCoordinator");
            //mail password to the zonal coordinator
            $this->sendMail($request->input('email'), $password, $request->input('email'));

            return response()->json(['zone' => $zonalCoordinator, 'login' => $password, 'message' => 'CREATED'], 201);

        } catch (\Exception $e) {

            return response()->json(['exception' => $e, 'message' => 'User Registration Failed!'], 409);
        }
    }

    public function viewZonalCoordinator()
    {
        $zones = ZoneModel::select('id', 'zone_no', 'zone_name')->get();
        foreach ($zones as $zone) {
            $zonalCoordinator = ZonalCoordinatorModel::select('id', 'name', 'contact_number', 'email')->where('zone_id', $zone['id'])->first();
            if ($zonalCoordinator) {
                $zone['zonalcoordinator_name'] = $zonalCoordinator['name'];
                $zone['zonalcoordinator_contact_number'] = $zonalCoordinator['contact_number'];
                $zone['zonalcoordinator_email'] = $zonalCoordinator['email'];
                $zone['zonalcoordinator_id'] = $zonalCoordinator['id'];
            } else {
                $zone['zonalcoordinator_name'] = "";
                $zone['zonalcoordinator_contact_number'] = "";
                $zone['zonalcoordinator_email'] = "";
                $zone['zonalcoordinator_id'] = "";
            }
        }
        return $zones;
    }

    public function zonalCoordinatorInfo($id)
    {
        $info = ZonalCoordinatorModel::where('zone_id', $id)->first();
        return $info;
    }

    public function changeZonalCoordinator(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:zonal_coordinator|unique:login,user_id',
            'contact_number' => 'required|numeric|unique:zonal_coordinator',
            'old_email' => 'required',
            'zone_id' => 'required',
        ]);
        try {
            $plainPassword = $this->generateRandomString();
            $encPassword = app('hash')->make($plainPassword);

            $login = User::where('user_id', $request->input("old_email"))->update(['user_id' => $request->input("email"), "password" => $encPassword, "updated_by" => $this->getCurrentUserId()]);
            $zonalCoordinator = ZonalCoordinatorModel::where('zone_id', $request->input("zone_id"))->update(['name' => $request->input("name"), "contact_number" => $request->input("contact_number"), "email" => $request->input("email")]);
            $this->sendMail($request->input("email"), $plainPassword, $request->input("email"));

            return response()->json(['message' => 'UPDATED'], 201);

        } catch (\Exception $e) {

            return response()->json(['exception' => $e, 'message' => 'User Registration Failed!'], 409);
        }
    }

    public function createUserCollege(Request $request, $user_type)
    {
        $this->validate($request, [
            'code' => 'required|unique:login,user_id',
        ]);
        try {

            $user = new User;
            $user->user_id = $request->input('code');
            $plainPassword = $this->generateRandomString();
            $user->password = app('hash')->make($plainPassword);
            $user->user_type = $user_type;
            $user->disabled = 0;
            $user->created_by = $this->getCurrentUserId();
            $user->save();
            $this->sendMail($request->input('code'), $plainPassword, $request->input('email'));
            return response()->json(['user' => $user, 'password' => $plainPassword, 'message' => 'CREATED'], 201);

        } catch (\Exception $e) {

            return response()->json(['exception' => $e, 'message' => 'User Registration Failed!'], 409);
        }
    }

    public function createCollege(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|unique:college|unique:login,user_id',
            'name' => 'required|string',
            'contact_number' => 'required|numeric|unique:college',
            'email' => 'required|email|unique:college',
            'type' => 'required',
            'zone_id' => 'required',
        ]);
        try {

            $college = new CollegeModel;
            $college->code = $request->input('code');
            $college->name = $request->input('name');
            $college->contact_number = $request->input('contact_number');
            $college->email = $request->input('email');
            $college->zone_id = $request->input('zone_id');
            $college->address = $request->input('address');
            $college->type = $request->input('type');
            $college->created_by = $this->getCurrentUser();

            $college->save();
            $collegeLogin = $this->createUserCollege($request, "college");
            //mail password to the college
            return response()->json(['college' => $college, 'login' => $collegeLogin, 'message' => 'CREATED'], 201);

        } catch (\Exception $e) {

            return response()->json(['exception' => $e, 'message' => 'User Registration Failed!'], 409);
        }
    }

    public function updateCollege(Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
            'name' => 'required|string',
            'contact_number' => 'required|numeric|',
            'email' => 'required|email|',
            'type' => 'required',
            'zone_id' => 'required',
            'id' => 'required',
        ]);
        try {
            $college = CollegeModel::where('id', $request->input("id"))->update(['code' => $request->input("code"), 'name' => $request->input("name"), "contact_number" => $request->input("contact_number"), "email" => $request->input("email"), "type" => $request->input("type"), "zone_id" => $request->input("zone_id")]);
            return response()->json(['message' => 'UPDATED'], 201);
        } catch (\Exception $e) {

            return response()->json(['exception' => $e, 'message' => 'User Registration Failed!'], 409);
        }

    }

    public function resetCollegePassword($id)
    {
        $plainPassword = $this->generateRandomString();
        $encPassword = app('hash')->make($plainPassword);
         try {
            //$login = User::where('user_id', $id)->update(["password" => $encPassword, "updated_by" => $this->getCurrentUserId()]);
            $college=CollegeModel::select('email')->where('code',$id)->first();
            $this->sendMail($id,$plainPassword,$college['email']);
            return response()->json(['message' => 'UPDATED'], 201);
        } catch (\Exception $e) {

            return response()->json(['exception' => $e, 'message' => 'User Registration Failed!'], 409);
        } 
        
    }

    public function allCollege()
    {
        $college = CollegeModel::get();
        foreach ($college as $c) {
            $zone = ZoneModel::select('zone_no', 'zone_name')->where('id', $c['zone_id'])->first();
            $c['zone_no'] = $zone['zone_no'];
            $c['zone_name'] = $zone['zone_name'];
        }
        return $college;
    }

    public function createGame(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|unique:game',
            'number_of_players' => 'required|numeric',
            'score1' => 'required|numeric',
            'score2' => 'required|numeric',
            'score3' => 'required|numeric',
        ]);
        try {

            $game = new GameModel;
            $game->name = $request->input('name');
            $game->number_of_players = $request->input('number_of_players');
            $game->score1 = $request->input('score1');
            $game->score2 = $request->input('score2');
            $game->score3 = $request->input('score3');
            $game->instructions = $request->input('instructions');
            $game->created_by = $this->getCurrentUserId();

            $game->save();
            return response()->json(['game' => $game, 'message' => 'CREATED'], 201);

        } catch (\Exception $e) {

            return response()->json(['exception' => $e, 'message' => 'User Registration Failed!'], 409);
        }
    }

    public function allGames()
    {
        $games = GameModel::get();
        return $games;
    }

    public function basicInformation()
    {
        $employee_id=$this->getCurrentUserId();
        $admin=AdminDetailsModel::where('employee_id',$employee_id)->first();
        return $admin;
    }

    public function updateBasicInformation(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'contact_number' => 'required|numeric',
            'email' => 'required|email|'
        ]);
        $employee_id=$this->getCurrentUserId();
        try {
            $admin=AdminDetailsModel::where('employee_id',$employee_id)->update(['name'=>$request->input('name'),'contact_number'=>$request->input('contact_number'),'email'=>$request->input('email')]);
            return response()->json(['message' => 'UPDATED'], 201);
        } catch (\Exception $e) {
            return response()->json(['exception' => $e, 'message' => 'Failed!'], 409);
        }
    }

    public function editGame(Request $request)
    {
          $this->validate($request, [
            'id'=>'required|numeric',
            'name' => 'required|string',
            'number_of_players' => 'required|numeric',
            'score1' => 'required|numeric',
            'score2' => 'required|numeric',
            'score3' => 'required|numeric',
            'instructions' => 'required|string'
        ]);
        try {
            $admin=GameModel::where('id',$request->input('id'))->update([
                'name'=>$request->input('name'),
                'number_of_players'=>$request->input('number_of_players'),
                'score1'=>$request->input('score1'),
                'score2'=>$request->input('score2'),
                'score3'=>$request->input('score3'),
                'instructions'=>$request->input('instructions'),
                'updated_by'=>$this->getCurrentUserId()
                ]);
            return response()->json(['message' => 'UPDATED'], 201);
        } catch (\Exception $e) {
            return response()->json(['exception' => $e, 'message' => 'Failed!'], 409);
        }  
    }

    //

}
