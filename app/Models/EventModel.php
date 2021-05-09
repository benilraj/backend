<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

//use Tymon\JWTAuth\Contracts\JWTSubject;

class EventModel extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'event';

    protected $fillable = [
        'game_id', 'period_from', 'period_to','last_date_enrollment','venue', 'local_sports_coordinator_name', 'local_sports_coordinator_number', 'local_sports_secetary_name', 'local_sports_secetary_contact_number', 'selection_commity1_name', 'selection_commity1_contact_number', 'selection_commity2_name', 'selection_commity2_contact_number', 'zone_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at', 'created_by', 'updated_by',
    ];
}
