<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

//use Tymon\JWTAuth\Contracts\JWTSubject;

class ZonalCouncilMembersModel extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table='zonal_council_members';

    protected $fillable = [
        'zone_id', 'member1_name','member1_contact_numer','member2_name','member2_contact_numer','member3_name','member3_contact_numer','member4_name','member4_contact_numer',
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
