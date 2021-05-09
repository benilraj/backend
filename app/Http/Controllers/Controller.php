<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    //
    protected function respondWithToken($token,$user_type)
    {
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'user_type' => $user_type['user_type'],
            'expires_in' => Auth::factory()->getTTL() * 60
        ], 200);
    }
}
