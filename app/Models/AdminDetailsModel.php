<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

//use Tymon\JWTAuth\Contracts\JWTSubject;

class AdminDetailsModel extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table='admin_details';

    protected $fillable = [
        'employee_id', 'name','position','email','contact_number',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_by','created_at','updated_by','updated_at'
    ];

    

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
   /*  public function getJWTIdentifier()
    {
        return $this->getKey();
    } */

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
   /*  public function getJWTCustomClaims()
    {
        return [
            'user_type' => $this->user_type,
        ];
    } */
}
