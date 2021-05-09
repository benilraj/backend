<?php

namespace App\Http\Controllers;

use App\Models\CollegeModel;
use App\Models\EligibilityPerformaModel;
use App\Models\EventModel;
use App\Models\GameModel;
use App\Models\StudentGameEnrollmentModel;
use App\Models\ZoneModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Tymon\JWTAuth\Facades\JWTAuth;



class CollegeController extends Controller
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
    if ($apy['user_type'] != 'college') {
    echo "user is not a college";
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

    public function getCollegeId()
    {
        $userId = $this->getCurrentUserId();
        $collegeId = CollegeModel::select('id')->where('code', $userId)->first();
        return $collegeId['id'];
    }

    public function findZone()
    {
        $userId = $this->getCurrentUserId();
        $zoneId = CollegeModel::select('zone_id')->where('code', $userId)->first();
        return $zoneId['zone_id'];
    }

    public function allEvents()
    {
        $zoneId = $this->findZone();
        $date=date("Y-m-d");
        $events = EventModel::where('zone_id', $zoneId)->whereDate('last_date_enrollment', '>=', $date)->get();
        $eventValues = array();
        foreach ($events as $event) {
            $gameName = GameModel::select('name')->where('id', $event['game_id'])->first();
            $value = [
                "event_id" => $event['id'],
                "game_name" => $gameName['name'],
                "period_from" => $event['period_from'],
                "period_to" => $event['period_to'],
                "venue" => $event['venue'],
            ];
            array_push($eventValues, $value);
        }
        return $eventValues;
    }

    public function eventStudentDetails($id)
    {

        $userId = $this->getCollegeId();
        $events = StudentGameEnrollmentModel::where('event_id', $id)->where('college_id', $userId)->get();
        foreach ($events as $e) {
            $student = EligibilityPerformaModel::select('name', 'registration_no', 'candidate_photo')->where('id', $e['eligibility_performa_id'])->first();
            $e['student_name'] = $student['name'];
            $e['registration_no'] = $student['registration_no'];
            $e['candidate_photo'] = $student['candidate_photo'];
        }
        return $events;
    }

    public function eventGameDetails($id)
    {
        $gameId = EventModel::select('game_id','last_date_enrollment')->where('id', $id)->first();
        $game = GameModel::select('name', 'number_of_players', 'instructions')->where('id', $gameId['game_id'])->first();
        $game['last_date']=$gameId['last_date_enrollment'];
        return $game;
    }
    public function EligibilityPerformaEventGameDetails($id)
    {
        $gameId = EventModel::select('game_id')->where('id', $id)->first();
        $game = GameModel::select('name')->where('id', $gameId['game_id'])->first();
        $college = CollegeModel::select('id', 'name', 'zone_id')->where("code", $this->getCurrentUserId())->first();
        $zone = ZoneModel::select('zone_no')->where('id', $college['zone_id'])->first();
        $game['college_id'] = $college['id'];
        $game['college_name'] = $college['name'];
        $game['zone_no'] = $zone['zone_no'];
        return $game;
    }

    public function insertStudentGameEnrollment($eligibilityPerformaId, $collegeId, $eventId)
    {
        $StudentGameEnrollment = new StudentGameEnrollmentModel;
        try {
            $StudentGameEnrollment->eligibility_performa_id = $eligibilityPerformaId;
            $StudentGameEnrollment->college_id = $collegeId;
            $StudentGameEnrollment->event_id = $eventId;
            $StudentGameEnrollment->created_by = $this->getCurrentUserId();
            $StudentGameEnrollment->save();
            return $StudentGameEnrollment['id'];
        } catch (\Exception $e) {
            return response()->json(['exception' => $e, 'message' => 'User Registration  Failed!'], 409);

        }
    }

    public function EligibilityPerforma(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'fatherName' => 'required|string',
            'motherName' => 'required|string',
            'dob' => 'required|date',
            'communicationAddress' => 'required|string',
            'mobile' => 'required|numeric',
            'landline' => 'string',
            'permanentAddress' => 'required|string',
            'monthYearPassing' => 'required',
            'currentCourse' => 'required|string',
            'currentYear' => 'required|numeric',
            'currentBranch' => 'required|string',
            'regNo' => 'required',
            'ugCourse' => 'string',
            'ugCollege' => 'string',
            'ugYearAdmission' => 'digits:4',
            'ugYearCompletion' => 'digits:4',
            'periodBreak' => 'numeric',
            'earlierRepresentationInUniversityTeam' => 'string',
            'detailsOfParticipation' => 'string',
            'uniformSize' => 'numeric',
            'attested12' => 'mimes:jpg,png|max:2048',
            'attestedMarksheet' => 'mimes:jpg,png|max:2048',
            'attestedDegree' => 'mimes:jpg,png|max:2048',
            'attestedForm' => 'mimes:jpg,png|max:2048',
            'candidatePhoto' => 'mimes:jpg,png|max:2048',
        ]);
        try {
            $eligibility_performa = new EligibilityPerformaModel;
            $eligibility_performa->name = $request->input('name');
            $eligibility_performa->father_name = $request->input('fatherName');
            $eligibility_performa->mother_name = $request->input('motherName');
            $eligibility_performa->dob = $request->input('dob');
            $eligibility_performa->communication_address = $request->input('communicationAddress');
            $eligibility_performa->mobile = $request->input('mobile');
            $eligibility_performa->landline = $request->input('landline');
            $eligibility_performa->permanent_address = $request->input('permanentAddress');
            $eligibility_performa->month_year_passing = $request->input('monthYearPassing');
            $eligibility_performa->current_course = $request->input('currentCourse');
            $eligibility_performa->current_year = $request->input('currentYear');
            $eligibility_performa->current_branch = $request->input('currentBranch');
            $eligibility_performa->registration_no = $request->input('regNo');
            $eligibility_performa->ug_course = $request->input('ugCourse');
            $eligibility_performa->ug_college = $request->input('ugCollege');
            $eligibility_performa->ug_year_admission = $request->input('ugYearAdmission');
            $eligibility_performa->ug_year_completion = $request->input('ugYearCompletion');
            $eligibility_performa->period_break = $request->input('periodBreak');
            $eligibility_performa->earlier_representation_in_university_team = $request->input('earlierRepresentationInUniversityTeam');
            $eligibility_performa->details_of_participation_in_national_international_tournaments = $request->input('detailsOfParticipation');
            $eligibility_performa->uniform_size = $request->input('uniformSize');

            $eligibility_performa->college_id = $this->getCollegeId();
            $eligibility_performa->created_by = $this->getCurrentUserId();

            //upload
            $fileName = $request->input('regNo') . "-" . $request->input('name') . ".jpg";
            $attested12 = $request->attested12->move('uploads/eligibility_performa_files/attested12', $fileName);
            $attestedMarksheet = $request->attestedMarksheet->move('uploads/eligibility_performa_files/attested_marksheet', $fileName);
            if ($request->input('currentCourse') === "PG") {
            $attestedDegree = $request->attestedDegree->move('uploads/eligibility_performa_files/attested_degree', $fileName);
            $attestedForm = $request->attestedForm->move('uploads/eligibility_performa_files/attested_form', $fileName);
            }
            $candidatePhoto = $request->candidatePhoto->move('uploads/eligibility_performa_files/candidate_photo', $fileName);

            $eligibility_performa->attested12 = $attested12;
            $eligibility_performa->attested_marksheet = $attestedMarksheet;
            if ($request->input('currentCourse') === "PG") {
                $eligibility_performa->attested_degree = $attestedDegree;
                $eligibility_performa->attested_form = $attestedForm;
            }

            $eligibility_performa->candidate_photo = $candidatePhoto;

            $eligibility_performa->save();

            $student = $this->insertStudentGameEnrollment($eligibility_performa['id'], $eligibility_performa['college_id'], $request->input('eventId'));

            return response()->json(['eligibility_performa' => $eligibility_performa, "student" => $student, 'message' => 'CREATED'], 201);

        } catch (\Exception $e) {

            return response()->json(['exception' => $e, 'message' => 'User Registration  Failed!'], 409);
        }
    }

    /*  public function file_upload_test(Request $request)
    {
    $this->validate($request,[
    'current_file' => 'mimes:jpg,png|max:2048'
    ]);

    $fileName = time().'.'.$request->current_file->extension();

    $upload=$request->current_file->move('uploads', $fileName);

    return $upload;

    } */

    public function sendEligiblilityPerforma($id)
    {
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

    }


    public function collegeInfo()
    {
        $userId = $this->getCurrentUserId();
        $college = CollegeModel::where('code', $userId)->first();
        $zone = ZoneModel::select('zone_no','zone_name')->where('id', $college['zone_id'])->first();
        $college['zone_no']=$zone['zone_no'];
        $college['zone_name']=$zone['zone_name'];
        return $college;
    }

    public function generateQrCode($id)
    {
        $id = Crypt::encrypt($id);
        return QrCode::size(200)->generate($id);
    }

   public function infrastructureUpload(Request $request)
   {
    $this->validate($request, [
        'heading' => 'required|string',
        'image' => 'required'
    ]);
    $collegeId = $this->getCollegeId();
    $userId = $this->getCurrentUserId(); 
    $oldData = CollegeModel::select('infrastructure_info','infrastructure_files')->where('id', $collegeId)->first();
    $info=explode(",",$oldData['infrastructure_info']);
    $allFiles=explode(",",$oldData['infrastructure_files']);
    $newFiles=array();
    $heading=$request->heading;
    $heading=str_replace(",","and",$heading);
    $heading=str_replace("^","caret",$heading);
    $fileHeading=str_replace(" ","",$heading);
    foreach($request->image as $f)
    {
        $fileName=$userId.$fileHeading.rand().".".$f->extension();
        $upload=$f->move("uploads/infrastructure_files",$fileName);
        array_push($newFiles,$upload);   
    }
    $newValues=implode("^",$newFiles);
    array_push($allFiles,$newValues);
    array_push($info,$heading);
    $infrastructureInfo=implode(",",$info);
    $infrastructureFiles=implode(",",$allFiles);
    $college = CollegeModel::where('id',$collegeId)->update(['infrastructure_info'=>$infrastructureInfo,'infrastructure_files'=>$infrastructureFiles]);
    return response()->json(['message' => 'CREATED'], 201);
   }

   public function infrastructure()
   {
    $collegeId = $this->getCollegeId();
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




    public function formQr(Request $request)
    {
        echo $request->value;
        $request->disabled=1;
        echo $request->disabled;
       // return QrCode::size(300)->generate($request);
    }

    public function showDateTime()
    {
        /*   $carbonDate = new Carbon();
        $carbonDate->timezone = 'Asia/Calcutta';
        return $carbonDate->now(); */
        //echo "The time is " . date("h:i:sa");
        echo date("Y-m-d");
    }

    public function updateBasicInformation(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'contact_number' => 'required|numeric',
            'email' => 'required|email|',
            'address' => 'required|string'

        ]);
        $college_id=$this->getCurrentUserId();
         try {
            $college=CollegeModel::where('code',$college_id)->update(['name'=>$request->input('name'),'contact_number'=>$request->input('contact_number'),'email'=>$request->input('email'),'address'=>$request->input('address')]);
            return response()->json(['message' => 'UPDATED'], 201);
        } catch (\Exception $e) {
            return response()->json(['exception' => $e, 'message' => 'Failed!'], 409);
        }  
        return $request;
    }


    

}
