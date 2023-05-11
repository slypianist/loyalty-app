<?php

namespace App\Http\Controllers;

use App\Models\Shop;

use App\Models\Admin;
use App\Models\Partner;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
        $details['admin'] = Admin::orderBy('id', 'DESC')->get();
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
            'roles'    => 'required'
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
        try {
            $admin =   Admin::create($input);
        } catch (QueryException $th) {
            return $this->sendError('Duplicate Entry detected. Email already exist');
        }

        $admin->assignRole($request->roles);

        return $this->sendResponse($admin, 'Record created successfully.');

       }

       public function showAdmin($id){
        try {
            $admin = Admin::findOrFail($id);
        } catch (ModelNotFoundException $th) {
            return $this->sendError('Operation Failed', $th->getMessage());
        }
        $data['admin'] = $admin;
        $data['role'] = $admin->getRoleNames();

        return $this->sendResponse($data, 'successful');
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
            'roles' => 'required',
            'dept' => 'required',

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
        }

        try {
            $admin = Admin::findOrFail($id);

        } catch (ModelNotFoundException $th) {
            return $this->sendError('Update failed.', $th->getMessage());
        }
        $admin->update($data);
        $admin->syncRoles($request->input('roles'));
        return $this->sendResponse($admin, 'Admin user updated successfully.');
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
