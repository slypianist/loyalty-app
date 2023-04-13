<?php

namespace App\Http\Controllers;

use App\Models\Shop;

use App\Models\Admin;
use App\Models\Partner;
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
     * All Admin user List.
     *
     * @return \Illuminate\Http\JsonResponse
     */

       public function index(){
        $admin = Admin::all();
        return response()->json(['admin'=> $admin],200);

       }

       /**
     * Create an Admin record.
     *
     * @return \Illuminate\Http\JsonResponse
     */
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


    /**
     * Update an admin details.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function update(Admin $admin, Request $request){
        $data = $request->all();
        $admin->update($data);
        return response()->json(['message'=>'Update successful', 'status'=>true], 200);


    }

    /**
     * Delete an Admin.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Admin $admin){
        $admin->delete();
        return response()->json(['message'=>'Record deleted successfully'], 200);

       }


}
