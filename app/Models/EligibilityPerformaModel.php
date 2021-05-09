<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

//use Tymon\JWTAuth\Contracts\JWTSubject;

class EligibilityPerformaModel extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table='eligibility_performa';

    protected $fillable = [
        'name', 'father_name','mother_name','dob','communication_address','mobile','permanent_address','month_year_passing','current_course','current_year','current_branch','registration_no','ug_course','ug_college','ug_year_admission','ug_year_completion','peroid_break','earlier_representation_in_university_team','details_of_participation_in_national_international_tournaments','uniform_size','attested12','attested_marksheet','attested_marksheet','attested_degree','attested_form','candidate_photo','college_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_at','updated_at','created_by','updated_by'
    ];
}
