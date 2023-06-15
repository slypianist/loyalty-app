<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;

class CustomersController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:list-customers|create-customer|update-customer|delete-customer', ['only'=> ['index']]);
        $this->middleware('permission:create-customer', ['only'=> ['createCustomer']]);
        $this->middleware('permission:update-customer', ['only'=> ['updateCustomer']]);
        $this->middleware('permission:delete-customer', ['only'=> ['destroyCustomer']]);
    }

    public function index(){
        $customer = Customer::where('status', 'ACTIVE')->get();
       return $this->sendResponse($customer, 'All registered customers');

    }

    public function createCustomer(Request $request){
        $this->validate($request,[
            'firstName' => 'required',
            'lastName' => 'required',
            'phoneNum' => 'required|string',
            'address'   => 'nullable',
            'gender' => 'required'
        ]);

        $input =    $request->all();

        if($request->hasFile('image')){
            $fileNameExtension = request('image')->getClientOriginalName();
            $fileExtension = request('image')->getClientOriginalExtension();
            $fileName = pathinfo($fileNameExtension, PATHINFO_FILENAME);
            $fileNameToStore = $fileName.'.'.time().'.'.$fileExtension;
            $path = $request->file('image')->storeAs('profiles', $fileNameToStore);
            $input['image'] = $fileNameToStore;
        } else{
            $input['image'] = 'default.jpg';
        }
//dd($input);
    $customer = Customer::create($input);
    return $this->sendResponse($customer, 'Customer record created successfully.');

    }
        // Update Customer Details
    public function updateCustomer(Request $request, $id){

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
            $path = $request->file('image')->storeAs('profile', $fileNameToStore);
            $input['image'] = $fileNameToStore;
            }
        }

        try {
            $customer = Customer::findOrFail($id);

        } catch (ModelNotFoundException $th) {
           return $this->sendError('An error occured', $th->getMessage());
        }
            $customer->update($input);
            return $this->sendResponse($customer, 'Customer record updated successfully');

    }

        // Delete Customer Details

    public function destroyCustomer($id){
        try {
            $customer = Customer::findOrFail($id);
        } catch (ModelNotFoundException $th) {
           return $this->sendError('An error Occurred', $th->getMessage());
        }
        $customer->status = "INACTIVE";
        $customer->update();
        return $this->sendResponse($customer, 'Customer record deleted successfully');

      //  return response()->json(['message'=>'Customer deleted successfully'],200);

    }

    /**
     * Get a customer details.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getCustomer(Request $request, $id){
       // $phone = $request->phone;
        try {
            $customer = Customer::where('id', $id)->firstOrFail();

        } catch (ModelNotFoundException $e) {
           $e->getMessage();
           return $this->sendError('An error has occured', $e->getMessage());
        }
       // $points = $customer->account->point;

        $data['customerDetails'] = $customer;

        $data['balance'] = Account::where('customer_id', $customer->id)->select('point')->first();

    //
        $data['history'] = DB::table('transactions')
                            ->where('transactions.customer_id', '=', $customer->id)
                            ->get();

        $data['claimsHistory'] = DB::table('withdrawals')
                            ->where('withdrawals.customer_id', '=', $customer->id)
                            ->get();

            return $this->sendResponse($data, 'Customer details with points');
    }

    /**
     * Get a customer phone number.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomerPhoneNum(Request $request){
        $phone = $request->phoneNum;


           // $customer = Customer::where('phoneNum', $phone)->firstorFail();
        $customer = DB::table('customers')
                                ->where('customers.phoneNum', '=', $phone)
                                ->leftJoin('accounts', 'accounts.customer_id', '=', 'customers.id')
                                ->select('customers.id As id', 'customers.firstName AS firstName', 'customers.lastName AS lastName', 'customers.phoneNum AS phoneNum','customers.address AS address',
                                'customers.gender AS gender','customers.status AS status', 'accounts.point AS point', 'accounts.visit AS visit')
                                ->get();
            if($customer->count()==NULL){
                return $this->sendError('No match found in our record(s).');

            }

        return $this->sendResponse($customer, 'Successful');

    }

    public function getCustomerAccruedPoint(Request $request, $id){
        $id = $request->id;
        $point = Account::where('customer_id', $id)->first();
        return $this->sendResponse($point, 'Accrued Points');

    }

    public function blacklistCustomer($id){
        try {
            $customer = Customer::findOrFail($id);
        } catch (ModelNotFoundException $th) {
            return $this->sendError('No record found for your query', $th->getMessage());
        }

        $customer->status = 'INACTIVE';
        $customer->update();
        return $this->sendResponse('Customer blacklisted successfully.', 'successful');

    }

    public function showBlacklistCustomer(){
        $data = Customer::where('status', 'INACTIVE')->get();
        return $this->sendResponse($data, 'Backlisted customers');
    }

    // Bulk upload customers
    public function bulkUploadCustomer(Request $request){
        // Retrieve the uploaded file
        $this->validate($request, [
            'excel' => 'required'

        ]);
        $file = $request->file('excel');

        // Validate the uploaded file
        if (!$file->isValid()) {
            return response()->json(['error' => 'Invalid file'], 400);
        }

        // Process the Excel file and populate the customer database table
        try {
            $data = Excel::toArray([], $file);
           // dd($data);

            $customerData = $data[0]; // Assuming the customer data is in the first sheet
           array_shift($customerData);
            foreach ($customerData as $customer) {
                //dd($customer['lastName']);
                // Insert the customer data into the database
                Customer::create([
                    'firstName' => $customer[0],
                    'lastName' => $customer[1],
                    'phoneNum'  => $customer[2],
                    'address' => $customer[3],
                    'gender' => $customer[4],
                    'image' => "default.png",
                    'status' => $customer[5],
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]);
            }

            return response()->json(['message' => 'Customer data uploaded successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to process bulk upload.', 'message'=> $e->getMessage()], 500);
        }
    }

}
