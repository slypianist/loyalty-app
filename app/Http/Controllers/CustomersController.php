<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class CustomersController extends BaseController
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

    public function index(){
        $customer = Customer::orderBy('id', 'DESC')->paginate(5);
       return $this->sendResponse($customer, 'All customer');

    }

    public function store(Request $request){
        $this->validate($request,[
            'firstName' => 'required',
            'lastName' => 'required',
            'phoneNum' => 'required|string',
            'address'   => 'required',
            'gender' => 'required'
        ]);

        $input =    $request->all();

        if($request->hasFile('image')){
            $fileNameExtension = $request('image')->getClientOriginalName();
            $fileExtension = $request('image')->getClientExtension();
            $fileName = pathinfo($fileNameExtension, PATHINFO_FILENAME);
            $fileNameToStore = $fileName.'.'.time().'.'.$fileExtension;
            $path = $request->file('image')->move(public_path('profile'), $fileName);
            $input['image'] = $fileNameToStore;
        } else{
            $input['image'] = 'default.jpg';
        }

    $customer = Customer::create($input);
    return $this->sendResponse($customer, 'Record has been created');

    }
        // Update Customer Details
    public function update(Request $request, $id){

        $this->validate($request,[
            'firstName' => 'required',
            'lastName' => 'required',
            'phoneNum' => 'required|string',
            'address'   => 'required',
            'gender' => 'required'
        ]);

        $input = $request->all();

        if(!empty($request->image)){
            if($request->hasFile('image')){
            $fileNameExtension = $request('image')->getClientOriginalName();
            $fileExtension = $request('image')->getClientExtension();
            $fileName = pathinfo($fileNameExtension, PATHINFO_FILENAME);
            $fileNameToStore = $fileName.'.'.time().'.'.$fileExtension;
            $path = $request->file('image')->move(public_path('profile'), $fileName);
            $input['image'] = $fileNameToStore;
            }
        }

        try {
            $customer = Customer::findOrFail($id);
            $customer->update($input);
            return $this->sendResponse($customer, 'Successful');
           // return response()->json(['message'=> 'Update successful'],200);
        } catch (\Throwable $th) {
           return $this->sendError('An error occured', $th->getMessage());
        }

    }

        // Delete Customer Details

    public function destroy(Customer $customer){
        $customer->delete();

        return response()->json(['message'=>'Customer deleted successfully'],200);

    }

    /**
     * Get a customer details.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getCustomer(Request $request){
        $phone = $request->phone;
        try {
            $customer = Customer::where('phoneNum', $phone);
            $points = $customer->loyaltyaccount();
            $data['customerDetails'] = $customer;
            $data['customerPoints'] = $points;
        } catch (ModelNotFoundException $e) {
           $e->getMessage();
           return $this->sendError('An error has occured', $e->getMessage());
           //return response()->json(['No record found.']);
        }

            return $this->sendResponse($data, 'Customer details with points');
       // return response()->json(['customer'=>$data],200);
    }

    /**
     * Get a customer phone number.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomerPhoneNum(Request $request){
        $phone = $request->phoneNum;
        try {
            $customer = Customer::where('phoneNum', $phone)->firstorFail();
        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occured', $th->getMessage());

        }
        return $this->sendResponse($customer, 'Successful');

    }


}
