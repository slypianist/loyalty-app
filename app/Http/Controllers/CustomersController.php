<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class CustomersController extends Controller
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
        $customer = Customer::orderBy('id', 'DESC')->withCount('withdrawal')->paginate(5);

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

    return response()->json(['message'=>'Customer created successfully'],200);

    }
        // Update Customer Details
    public function update(Request $request,Customer $customer){

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
            $customer->update($input);
            return response()->json(['message'=> 'Update successful'],200);
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

    public function getCustomer($phone){
        try {
            $customer = Customer::findOrFail($phone)->get();
            $points = $customer->loyaltyaccount()->point;
            $data['customerDetails'] = $customer;
            $data['customerPoints'] = $points;
        } catch (ModelNotFoundException $e) {
           $e->getMessage();
           return response()->json(['No record found.']);
        }

        return response()->json(['customer'=>$data],200);
    }

    /**
     * Get a customer phone number.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomerPhoneNum(Request $request){
        $phone = $request->phoneNum;
        try {
            $phoneNum = Customer::findOrFail($phone)->first();
        } catch (ModelNotFoundException $th) {
            $th->getMessage();
            return response()->json(['message'=>'Phone Number does not exist.'],404);

        }
        return response()->json(['customer_phoneNum'=>$phoneNum],200);

    }


}
