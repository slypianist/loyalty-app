<?php

namespace App\Http\Controllers;

use App\Models\Admin;

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

     /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */

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

    public function refresh(){
        return $this->respondWithToken(auth('admin')->refresh());
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function logout(){
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

       public function index(){
        $admin = Admin::all();
        return response()->json(['admin'=> $$adim]);

       }

       public function save(Request $request){
        $this->validate($request,[
            'firstName' => 'required',
            'lastName' => 'required',
            'phoneNum' => 'required|string',
            'address'   => 'required',
            'gender' => 'required'
        ]);

        $input = $request->all();

        Admin::create($input);
       }

       public function destroy(Admin $admin){

       }

       public function assignShop(){

       }

       public function unassignShop(){

       }
}
