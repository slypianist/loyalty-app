<?php

namespace App\Http\Controllers;

use App\Models\Rep;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\BaseController;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
    public function adminLogin(Request $request){
        $this->validate($request,[
            'email' => 'required|email',
            'password' => 'required'

        ]);
       $credentials = $request->only('email', 'password');

            if(!$token = auth('admin')->attempt($credentials)){
                return $this->sendError('Invalid username or password');
            }

            return $this->getToken($token);
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

    public function partnerLogin(Request $request, $guard='api'){
        $credentials =     $this->validate($request,[
                 'email' => 'required|email',
                 'password' => 'required'
             ]);

             if(!$token = auth($guard)->attempt($credentials)){
                 return response()->json(['error'=>'Invalid username or password'], 401);

             }

        return $this->respondWithToken($token, $guard);

    }

    public function authPartner(){
       $id = auth('api')->user()->id;

       try {
        $partner = User::findOrFail($id);

    } catch (ModelNotFoundException $th) {
        return $this->sendError('User not found', $th->getMessage());
    }

    // Get a single partner entity.
    $data['partner'] =  DB::table('users')
    ->where('users.id', $id)
   ->select('users.id','users.firstName AS firstName', 'users.lastName AS lastName', 'users.address AS address',
         'users.phoneNum AS phoneNumber', 'users.email AS email')
    ->get();

    // Get Assigned Shops...
    $data['assignedShops'] = Shop::where('user_id', $partner->id)
                             ->select('id','shopCode', 'name', 'address', 'location', 'status')
                            ->get();

    return $this->sendResponse($data, 'Successful');

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
        $id =   auth('rep')->user()->id;

        try {
            $rep = Rep::findOrFail($id);


        } catch (ModelNotFoundException $th) {
            return $this->sendError('User does not exist', $th->getMessage());
        }
        $data['rep'] = $rep;
        $data['center'] = Shop::where('rep_id', $id)
                            ->select('id','shopCode', 'name', 'address')
                            ->get();


        return $this->sendResponse($data,200);
    }

        /**
          * Log the Partner out (Invalidate the token).
          *
          * @return \Illuminate\Http\JsonResponse
        */
    public function repLogout(){
        auth()->logout();
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
            'expires_in' => auth($guard)->factory()->getTTL() * 60
        ]);

    }

    protected function getToken($token, $guard='admin'){
        $user = auth($guard)->user();
        return response()->json([

            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => $user,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions(),
            'expires_in' => auth($guard)->factory()->getTTL() * 60
        ]);

    }



}
