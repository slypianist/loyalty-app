<?php

namespace App\Http\Controllers;

use App\Models\Rep;
use App\Models\Shop;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RepController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:list-reps|create-rep|update-rep|delete-rep|view-rep', ['only'=> ['index']]);
        $this->middleware('permission:create-rep', ['only'=> ['createRep']]);
        $this->middleware('permission:view-rep', ['only'=> ['showRep']]);
        $this->middleware('permission:update-rep', ['only'=> ['updateRep']]);
        $this->middleware('permission:delete-rep', ['only'=> ['destroyRep']]);
    }

       /**
     * All Rep user List.
     *
     * @return \Illuminate\Http\JsonResponse
     */

       public function index(){
        $details['rep'] = Rep::all();
        if(count($details['rep'])==NULL){
            return $this->sendResponse('No center reps have been registered.',200);
        }
        $details['total'] = $details['rep']->count();
        return $this->sendResponse($details, 'Successful');

       }

       /**
     * Create an Rep record.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createRep(Request $request){

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
            return $this->sendError('Please required fields are compulsory.', $validation->errors());
        }

        $input = $request->all();
   //     dd($input);
        $input['password'] = Hash::make($request->password);

        $rep =   Rep::create($input);

        return $this->sendResponse($rep, 'Rep created successfully.');

       }

       public function showRep($id){
        try {
            $rep = Rep::findOrFail($id);


        } catch (ModelNotFoundException $th) {
            return $this->sendError('Operation Failed', $th->getMessage());
        }
        $data['rep'] = $rep;
        $data['center'] = Shop::where('rep_id', $id)
                            ->select('id','shopCode', 'name', 'address')
                            ->get();
        return $this->sendResponse($data, 'successful');
    }


    /**
     * Update an rep details.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function updateRep($id, Request $request){
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
            return $this->sendError('Please required fields are compulsory', $validation->errors());
        }
        $data = $request->all();

        if($request->has('password')){
            $data['password'] = Hash::make($request->password) ;

        }else {
         $data =   $request->except('password');

        }

        try {
            $rep = Rep::findOrFail($id);

            $rep->update($data);
            return $this->sendResponse($rep, 'Rep user updated successfully.');

        } catch (ModelNotFoundException $th) {
            return $this->sendError('Update failed', $th->getMessage());
        }
    }

    /**
     * Delete an Rep.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyRep($id){
        try {
            $rep = Rep::findOrFail($id);
        } catch (ModelNotFoundException $th) {
            return $this->sendError('Operation failed.', $th->getMessage());
        }
        $shop = Shop::where('rep_id', $rep->id)->first();
        if($shop->count() != 1){
            $shop->rep()->dissociate($rep);
            $shop->status2 = "UNASSIGNED";
            $shop->save();
        }
        $data =    $rep;
        return $this->sendResponse($data, 'Rep disabled successfully.');

       }

       public function changePassRep(Request $request){
        $this->validate($request,[
            'password'=> 'required|confirmed'

        ]);

        $email = auth('rep')->user()->email;


        $rep = Rep::where('email', $email)->first();

        if($rep !== NULL){
            $rep->password = Hash::make($request->password);
            $rep->update();
            return $this->sendResponse($rep, 'Password change successful.');
        }

        return $this->sendError('No record found for this user', 'Invalid user identifier');

       }

}
