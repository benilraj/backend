<?php

namespace App\Http\Controllers;

use App\Models\CollegeModel;
use App\Models\EligibilityPerformaModel;
use App\Models\EventModel;
use App\Models\GameModel;
use App\Models\ResultModel;
use App\Models\StudentGameEnrollmentModel;
use App\Models\ZonalCoordinatorModel;
use App\Models\ZonalCouncilMembersModel;
use App\Models\ZoneModel;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ZonalCoordinatorController extends Controller
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
        if ($apy['user_type'] != 'zonalCoordinator') {
            echo "user is not a Zonal Coordinator";
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

    public function findZone()
    {
        $userId = $this->getCurrentUserId();
        $zoneId = ZonalCoordinatorModel::select('zone_id')->where('email', $userId)->first();
        return $zoneId['zone_id'];
    }

    public function findZoneNoName()
    {
        $userId = $this->getCurrentUserId();
        $zoneId = ZoneModel::select('zone_no','zone_name')->where('id', $this->findZone())->first();
        return $zoneId;
    }

    public function allColleges()
    {
        $zoneId = $this->findZone();
        $colleges = CollegeModel::where('zone_id', $zoneId)->get();
        return $colleges;
        //return response()->json(CollegeModel::all());
    }

    public function createEvent(Request $request)
    {
        $this->validate($request, [
            'game_id' => 'required|',
            'period_from' => 'required|',
            'period_to' => 'required|',
            'last_date_enrollment' => 'required|',
            'venue' => 'required',
            'local_sports_coordinator_name' => 'required|string',
            'local_sports_coordinator_number' => 'required|numeric',
            'local_sports_secetary_name' => 'required|string',
            'local_sports_secetary_contact_number' => 'required|numeric',
            'selection_commity1_name' => 'required|string',
            'selection_commity1_contact_number' => 'required|numeric',
            'selection_commity2_name' => 'required|string',
            'selection_commity2_contact_number' => 'required|numeric',
            'selection_commity3_name' => 'required|string',
            'selection_commity3_contact_number' => 'required|numeric',

        ]);

        try {
            $event = new EventModel;
            $event->game_id = $request->input('game_id');
            $event->period_from = $request->input('period_from');
            $event->period_to = $request->input('period_to');
            $event->last_date_enrollment = $request->input('last_date_enrollment');
            $event->venue = $request->input('venue');
            $event->local_sports_coordinator_name = $request->input('local_sports_coordinator_name');
            $event->local_sports_coordinator_number = $request->input('local_sports_coordinator_number');
            $event->local_sports_secetary_name = $request->input('local_sports_secetary_name');
            $event->local_sports_secetary_contact_number = $request->input('local_sports_secetary_contact_number');
            $event->selection_commity1_name = $request->input('selection_commity1_name');
            $event->selection_commity1_contact_number = $request->input('selection_commity1_contact_number');
            $event->selection_commity2_name = $request->input('selection_commity2_name');
            $event->selection_commity2_contact_number = $request->input('selection_commity2_contact_number');
            $event->selection_commity3_name = $request->input('selection_commity3_name');
            $event->selection_commity3_contact_number = $request->input('selection_commity3_contact_number');
            $event->zone_id = $this->findZone();

            $event->created_by = $this->getCurrentUserId();

            $event->save();
            return response()->json(['event' => $event, 'message' => 'CREATED'], 201);

        } catch (\Exception $e) {

            return response()->json(['exception' => $e, 'message' => 'Event Registration Failed!'], 409);
        }
    }

    public function sendEligiblilityPerforma($id) //the function used for qr verify

    {
        try {
            $id =Crypt::decrypt($id);
            $enrollment = StudentGameEnrollmentModel::select('eligibility_performa_id', 'college_id', 'event_id')->where('id', $id)->first();
            $event = EventModel::select('game_id')->where('id', $enrollment['event_id'])->first();
            $game = GameModel::select('name')->where('id', $event['game_id'])->first();
            $eligibility_performa = EligibilityPerformaModel::where('id', $enrollment['eligibility_performa_id'])->first();
            $college = CollegeModel::select('zone_id', 'name')->where('id', $eligibility_performa['college_id'])->first();
            $zone_no = ZoneModel::select('zone_no')->where('id', $college['zone_id'])->first();
            $eligibility_performa['college_name'] = $college['name'];
            $eligibility_performa['zone_no'] = $zone_no['zone_no'];
            $eligibility_performa['game_name'] = $game['name'];
            return $eligibility_performa;
        } catch (DecryptException $e) {
            $result['result']="Invalid QR code \n please scan again";
            return $result;
        }

    }

    public function gameData()
    {
        $game = GameModel::select('id', 'name')->get();
        return $game;
    }

    public function gameName($id)
    {
        $game = GameModel::select('name')->where('id', $id)->first();
        return $game;
    }

    public function allEvents()
    {
        $event = EventModel::where('zone_id', $this->findZone())->get();
        foreach ($event as $e) {
            $game = $this->gameName($e['game_id']);
            $e['game_name'] = $game['name'];
        }
        return $event;
    }

    public function eventRegistrationDetails()
    {
        $result = array();
        $event = $this->allEvents();
        foreach ($event as $e) {
            $eventCount = StudentGameEnrollmentModel::select('college_id')->where('event_id', $e['id'])->get()->groupBy('college_id')->count();
            $value = array("id" => $e['id'], "event" => $e['game_name'], "count" => $eventCount);
            array_push($result, $value);
        }
        return $result;
    }

    public function eventColleges($id)
    {
        $enrollment = StudentGameEnrollmentModel::select('college_id', 'event_id')->where('event_id', $id)->groupBy('college_id')->get();
        $college = array();
        foreach ($enrollment as $e) {
            $collegeData = CollegeModel::select('id', 'name')->where('id', $e['college_id'])->first();
            array_push($college, $collegeData);
        }
        return $college;

    }

    public function todayEvents()
    {
        $events = EventModel::select('id', 'game_id', 'period_from', 'period_to', 'venue')->where("zone_id", $this->findZone())->get();
        foreach ($events as $e) {
            $game = GameModel::select('name')->where('id', $e['game_id'])->first();
            $e['game_name'] = $game['name'];
        }
        return $events;
    }

    public function publishResult(Request $request)
    {
        $this->validate($request, [
            'first_college_id' => 'required|numeric',
            'second_college_id' => 'required|numeric',
            'third_college_id' => 'required|numeric',
            'fourth_college_id' => 'required|numeric',
            'event_id' => 'required|numeric|unique:zonal_results',

        ]);
        try {
            $result = new ResultModel;
            $result->first_college_id = $request->input('first_college_id');
            $result->second_college_id = $request->input('second_college_id');
            $result->third_college_id = $request->input('third_college_id');
            $result->fourth_college_id = $request->input('fourth_college_id');
            $result->event_id = $request->input('event_id');

            $result->zone_id = $this->findZone();
            $result->created_by = $this->getCurrentUserId();

            $result->save();
            return response()->json(['result' => $result, 'message' => 'CREATED'], 201);

        } catch (\Exception $e) {

            return response()->json(['exception' => $e, 'message' => 'User Registration Failed!'], 409);
        }
    }

    public function viewResults()
    {

        //  $results=DB::select("SELECT zr.* FROM `zonal_results` zr,`event` ev WHERE zr.event_id=ev.id AND zone_id='9'")->get();
        //$results = json_decode(json_encode($r), true);
        /*  foreach($results as $result)
        {
        $first_college_name=CollegeModel::select('name')->where('id',$result['first_college_id'])->first();
        array_push($result,$first_college_name);
        } */
        $results = ResultModel::where('zone_id', $this->findZone())->get();
        foreach ($results as $result) {
            $event = EventModel::select('game_id')->where('id', $result['event_id'])->first();
            $game = $this->gameName($event['game_id']);
            $result['event_name'] = $game['name'];
            $first_college_name = CollegeModel::select('name')->where('id', $result['first_college_id'])->first();
            $result['first_college_name'] = $first_college_name['name'];
            $second_college_name = CollegeModel::select('name')->where('id', $result['second_college_id'])->first();
            $result['second_college_name'] = $second_college_name['name'];
            $third_college_name = CollegeModel::select('name')->where('id', $result['third_college_id'])->first();
            $result['third_college_name'] = $third_college_name['name'];
            $fourth_college_name = CollegeModel::select('name')->where('id', $result['fourth_college_id'])->first();
            $result['fourth_college_name'] = $fourth_college_name['name'];
        }
        return $results;
    }

    public function addCouncilMembers(Request $request)
    {
        //validate the count of council members
        $this->validate($request, [
            'member1_name' => 'required',
            'member1_contact_number' => 'required|numeric',
            'member1_email' => 'required',
            'member2_name' => 'required',
            'member2_contact_number' => 'required|numeric',
            'member2_email' => 'required',
            'member3_name' => 'required',
            'member3_contact_number' => 'required|numeric',
            'member3_email' => 'required',
            'member4_name' => 'required',
            'member4_contact_number' => 'required|numeric',
            'member4_email' => 'required',
        ]);
        try {
            $zonalCouncilMembers = new ZonalCouncilMembersModel;
            $zonalCouncilMembers->member1_name = $request->input('member1_name');
            $zonalCouncilMembers->member1_contact_number = $request->input('member1_contact_number');
            $zonalCouncilMembers->member1_email = $request->input('member1_email');
            $zonalCouncilMembers->member2_name = $request->input('member2_name');
            $zonalCouncilMembers->member2_contact_number = $request->input('member2_contact_number');
            $zonalCouncilMembers->member2_email = $request->input('member2_email');
            $zonalCouncilMembers->member3_name = $request->input('member3_name');
            $zonalCouncilMembers->member3_contact_number = $request->input('member3_contact_number');
            $zonalCouncilMembers->member3_email = $request->input('member3_email');
            $zonalCouncilMembers->member4_name = $request->input('member4_name');
            $zonalCouncilMembers->member4_contact_number = $request->input('member4_contact_number');
            $zonalCouncilMembers->member4_email = $request->input('member4_email');

            $zonalCouncilMembers->zone_id = $this->findZone();
            $zonalCouncilMembers->created_by = $this->getCurrentUserId();

            $zonalCouncilMembers->save();
            return response()->json(['result' => $zonalCouncilMembers, 'message' => 'CREATED'], 201);

        } catch (\Exception $e) {

            return response()->json(['exception' => $e, 'message' => 'User Registration Failed!'], 409);
        }

    }

    public function councilMembers()
    {
        $councilMembers = ZonalCouncilMembersModel::where('zone_id', $this->findZone())->first();
        return $councilMembers;
    }

    public function editCouncilMembers(Request $request)
    {
        $this->validate($request, [
            'member1_name' => 'required',
            'member1_contact_number' => 'required|numeric',
            'member1_email' => 'required',
            'member2_name' => 'required',
            'member2_contact_number' => 'required|numeric',
            'member2_email' => 'required',
            'member3_name' => 'required',
            'member3_contact_number' => 'required|numeric',
            'member3_email' => 'required',
            'member4_name' => 'required',
            'member4_contact_number' => 'required|numeric',
            'member4_email' => 'required',
        ]);
        try {
        $councilMembers = ZonalCouncilMembersModel::where('zone_id', $this->findZone())->update(
            [
                'member1_name'=>$request->input('member1_name'),
                'member1_contact_number'=>$request->input('member2_contact_number'),
                'member1_email'=>$request->input('member1_email'),
                'member2_name'=>$request->input('member2_name'),
                'member2_contact_number'=>$request->input('member2_contact_number'),
                'member2_email'=>$request->input('member2_email'),
                'member3_name'=>$request->input('member3_name'),
                'member3_contact_number'=>$request->input('member3_contact_number'),
                'member3_email'=>$request->input('member3_email'),
                'member4_name'=>$request->input('member4_name'),
                'member4_contact_number'=>$request->input('member4_contact_number'),
                'member4_email'=>$request->input('member4_email'),
                'updated_by' => $this->getCurrentUserId()
            ]);
            return response()->json([ 'message' => 'UPDATED'], 201);

        } catch (\Exception $e) {

            return response()->json(['exception' => $e, 'message' => 'Update Failed!'], 409);
        }
    }

    public function collegeInfrastructre($id)
    {
        $collegeId = $id;
        $data=CollegeModel::select('infrastructure_info','infrastructure_files')->where('id', $collegeId)->first();
        $infrastructureInfo=explode(",", $data['infrastructure_info']);
        $infrastructureFiles = explode(",",$data['infrastructure_files']);
        $finalData=array();
        for($i=0;$i<count($infrastructureInfo);$i++)
        {
            $temp2=explode("^",$infrastructureFiles[$i]);
            $temp=array("infrastructure_info"=>$infrastructureInfo[$i],"files"=>$temp2);
            array_push($finalData,$temp);
        }
        return $finalData;
    }

    //

}
