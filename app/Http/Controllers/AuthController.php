<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
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

     /**
     * ==================Partners Authentication==================
     *
    */

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminLogin(Request $request, $guard='admin'){
        $this->validate($request,[
            'email' => 'required|email',
            'password' => 'required'

        ]);
       $credentials = $request->only('email', 'password');

            if(!$token = auth('admin')->attempt($credentials)){
                return response()->json(['error'=>'Invalid username or password'],401);
            }

            return $this->respondWithToken($token, $guard);
    }

    public function refresh(){
        return $this->respondWithToken(auth('admin')->refresh());
    }

    public function authAdmin(){
        $admin = auth('admin')->user();
        return response()->json(['admin'=>$admin],200);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
    */

    public function adminLogout(){
        auth('admin')->logout();
        return response()->json(['message'=>'You have logged out successfully']);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
    */
    protected function respondWithToken($token, $guard=''){

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => auth($guard)->user(),
            'expires_in' => auth('admin')->factory()->getTTL() * 60 *24
        ]);

       }

    public function getTest(){
        return response()->json(['message'=>'Admin test is working']);
       }


    /**
     *
     * ==================Partners Authentication==================
     *
     *
    */


    /**
     * Get a Partner JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
    */

    public function partnerLogin(Request $request){
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
    public function partnerLogout(){
        auth()->logout();
        return response()->json(['message'=> 'Logout successful']);
    }

}
