<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

     /**
     * Get a Partner JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function login(Request $request){
   $credentials =     $this->validate($request,[
            'email' => 'required|email',
            'password' => 'required'
        ]);


        if(!$token = auth()->attempt($credentials)){
            return response()->json(['error'=>'Invalid username or password'], 401);

        }

     return $this->respondWithToken($token);

    }

    /**
     * Log the Partner out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(){
        auth()->logout();
        return response()->json(['message'=> 'Logout successful']);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => auth()->user(),
            'expires_in' => auth()->factory()->getTTL() * 60 * 24
        ]);
    }

    public function getTest(){
        return response()->json(['message'=>'It is  working']);
    }
}
