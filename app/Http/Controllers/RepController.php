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
        //
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
            $data =    $rep->delete();
    return $this->sendResponse($data, 'Record deleted successfully.');
        } catch (ModelNotFoundException $th) {
            return $this->sendError('Operation failed.', $th->getMessage());
        }

       }

}
