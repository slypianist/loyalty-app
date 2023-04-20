<?php

namespace App\Http\Controllers;

use App\Models\Shop;

use App\Models\Admin;
use App\Models\Partner;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends BaseController
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
        $details['admin'] = Admin::orderBy('id', 'DESC')->paginate(2);
        return $this->sendResponse($details, 'Successful');

       }

       /**
     * Create an Admin record.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveAdmin(Request $request){

        $validation = Validator::make($request->all(),[
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed',
        ],

        $messages = [
            'firstName.required' => 'Please enter your first name.',
            'lastName.required' => 'Please enter your last name.',
            'email.required' => 'Please enter your email.',
            'password.required' => 'Please enter your password.',
           // 'password.confirmed' => 'Please confirm your password'

        ]);

        if($validation->fails()){
            return $this->sendError('All required fields are compulsory.', $validation->errors());
        }

        $input = $request->all();
   //     dd($input);
        $input['password'] = Hash::make($request->password);

        $admin =   Admin::create($input);

        return $this->sendResponse($admin, 'Created successfully.');

       }

       public function showAdmin($id){
        try {
            $admin = Admin::findOrFail($id);
            return $this->sendResponse($admin, 'successful');
        } catch (ModelNotFoundException $th) {
            return $this->sendError('Operation Failed', $th->getMessage());
        }
       }


    /**
     * Update an admin details.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function updateAdmin($id, Request $request){
        $validation = Validator::make($request->all(),[
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email',

        ],

        $messages = [
            'firstName.required' => 'Please enter your first name',
            'lastName.required' => 'Please enter your last name',
            'email.required' => 'Please enter your email',
            'password.required' => 'Please enter your password',

        ]);

        if($validation->fails()){
            return $this->sendError('All required fields are compulsory', $validation->errors());
        }
        $data = $request->all();

        if($request->has('password')){
            $data['password'] = Hash::make($request->password) ;

        }else {
         $data =   $request->except('password');
         dd($data);
        }

        try {
            $admin = Admin::findOrFail($id);

            $admin->update($data);
            return $this->sendResponse($admin, 'Admin user updated successfully.');

        } catch (ModelNotFoundException $th) {
            return $this->sendError('Update failed', $th->getMessage());
        }
    }

    /**
     * Delete an Admin.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyAdmin($id){
        try {
            $admin = Admin::findOrFail($id);
            $data =    $admin->delete();
    return $this->sendResponse($data, 'Record deleted successfully.');
        } catch (ModelNotFoundException $th) {
            return $this->sendError('Operation failed.', $th->getMessage());
        }

       }



}
