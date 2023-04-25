<?php

namespace App\Http\Controllers;

use App\Models\Rep;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
        $details['rep'] = Rep::orderBy('id', 'DESC')->paginate(2);
        return $this->sendResponse($details, 'Successful');

       }

       /**
     * Create an Rep record.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveRep(Request $request){

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

        $rep =   Rep::create($input);

        return $this->sendResponse($rep, 'Created successfully.');

       }

       public function showRep($id){
        try {
            $rep = Rep::findOrFail($id);
            return $this->sendResponse($rep, 'successful');
        } catch (ModelNotFoundException $th) {
            return $this->sendError('Operation Failed', $th->getMessage());
        }
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
