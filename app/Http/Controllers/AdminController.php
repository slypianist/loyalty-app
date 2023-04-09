<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;


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
    }

    public function login(Request $request){
        $this->validate($request,[
            'email' => 'required|email',
            'password' => 'required'

        ]);
       $credentials = $request->only('email', 'password');
      //  dd($credentials);


            if(!$token = auth('admin')->attempt($credentials)){
                return response()->json(['error'=>'Invalid username or password'],401);
            }

            return $this->respondWithToken($token);
    }

       protected function respondWithToken($token){

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
           'user' => auth('admin')->user(),
            'expires_in' => auth('admin')->factory()->getTTL() * 60 *24
        ]);

       }

       public function getTest(){
        return response()->json(['message'=>'Admin test is working']);
       }
}
