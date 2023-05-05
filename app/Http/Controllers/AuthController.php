<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Spatie\Permission\Models\Role;

class AuthController extends BaseController
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
                return $this->sendError('Invalid username or password');
            }

            return $this->respondWithToken($token, $guard);
    }

    public function refresh(){
        return $this->respondWithToken(auth('admin')->refresh());
    }

    public function authAdmin(){
        $admin = auth('admin')->user();

        return $this->sendResponse($admin,true);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
    */

    public function adminLogout(){
        auth('admin')->logout();
        return $this->sendResponse('Logged out successfully',200);
    }

    /**
     *
     * Get a Partner JWT via given credentials.
     *
     *
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

    public function authPartner(){
       $partner = auth()->user();
       return $this->sendResponse($partner,true);
    }

        /**
          * Log the Partner out (Invalidate the token).
          *
          * @return \Illuminate\Http\JsonResponse
        */
    public function partnerLogout(){
        auth()->logout();
        return $this->sendResponse('Logged out successfully', 200);
    }

     /**
     *
     * Get a Rep JWT via given credentials.
     *
     *
     *
     * @return \Illuminate\Http\JsonResponse
    */

    public function repLogin(Request $request, $guard='rep'){
        $credentials =     $this->validate($request,[
                 'email' => 'required|email',
                 'password' => 'required'
             ]);

             if(!$token = auth('rep')->attempt($credentials)){
                 return $this->sendError('Invalid credentials');

             }

        return $this->respondWithToken($token, $guard);

    }

    public function authRep(){
        $rep =   auth('rep')->user();
        return $this->sendResponse($rep,200);
    }

        /**
          * Log the Partner out (Invalidate the token).
          *
          * @return \Illuminate\Http\JsonResponse
        */
    public function repLogout(){
        auth('rep')->logout();
        return $this->sendResponse('Logged out successful',true);
    }



    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
    */
    protected function respondWithToken($token, $guard=''){

        $user = auth($guard)->user();

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => $user,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions(),
            'expires_in' => auth('admin')->factory()->getTTL() * 60
        ]);

       }

}
