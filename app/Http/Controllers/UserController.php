<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\User;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\LoyaltyRule;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltySetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\BaseController;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function index(){
        $partner = User::orderBy('id', 'ASC')->paginate(5);
        if ($partner->count() == NULL) {
            return $this->sendResponse($partner, 'No records');
        }
        return $this->sendResponse($partner, 'Successful');

    }

    public function createPartner(Request $request){
        $this->validate($request,[
            'firstName' => 'required',
            'lastName'=> 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed',
            'phoneNum' => 'required',
            'address' => 'nullable',
        ]);

        $data = $request->all();

        if($request->has('password')){

            $data['password'] = Hash::make($request->password);

        }else{

            $data = $request->except('password');
        }

        $data = $request->all();
      $user =  User::create($data);
        return $this->sendResponse($user, 'Partner created successfully');
       // return response()->json(['message'=>'Partner created successfully.']);
    }



    // Show Partners details.

    public function showPartner($id){
        try {
            $partner = User::findOrFail($id);

        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occurred', $th->getMessage());
        }
        $data['partner'] =  DB::table('users')
        ->where('users.id', $id)
       ->select('users.firstName AS firstName', 'users.lastName AS lastName', 'users.address AS address',
             'users.phoneNum AS phoneNumber', 'users.email AS email')
        ->get();
        $data['assignedShops'] = Shop::where('user_id', $partner->id)->get();

        return $this->sendResponse($data, 'Successful');

    }
        // Update a Partner Info
    public function updatePartner(Request $request, $id){

        $this->validate($request,[
            'firstName' => 'required',
            'lastName'=> 'required',
            'email' => 'required|email',
            'phoneNum' => 'required',
            'address' => 'nullable',
        ]);
        $data = $request->all();
        try {
            $partner = User::findOrFail($id);
        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occurred', $th->getMessage());
        }
        $partner->update($data);

        return $this->sendResponse($partner, 'Update successful');

    }

    public function deletePartner($id){
        try {
            $partner = User::findOrFail($id);
        } catch (ModelNotFoundException $th) {
            return $this->sendError('An error occurred', $th->getMessage());
        }
        if($partner->shops()->count()>= 1){
            return $this->sendError('You cannot delete a partner that is currently assigned to a shop(s).');
        }

        return $this->sendResponse($partner, 'Record successfully deleted.');

    }

    public function addLoyaltyPoints(Request $request){
        $customerId = $request->id;
        $invoiceNum = $request->invoiceNum;
        $partnerId = $request->partnerId;

        //Check if invoice is already used.
        $invoice = Invoice::where('invoiceCode', $invoiceNum)->first();

        $customer = Customer::findorFail($customerId)->first();


            if($invoice){
                return $this->sendError('This invoice has been used');
            }
            $acc = Account::where('customer_id', $customerId)->first();
            $loyaltyConfig = LoyaltySetting::where('status', 'ACTIVE')->first();
            $rule = $loyaltyConfig->rule;
            // Update loyalty points if customer is already registered.
            if($acc){
               // $initialPoints = $request->amount*($rule/100);
                $points = $request->amount*($rule/100);
                $acc->point = $acc->point+=$points;
                $acc->update();

                //Save Invoice details
                $invoice = new Invoice();
                $invoice->invoiceCode = $request->invoiceNum;
                $invoice->customer_id = $customerId;
                $invoice->amount = $request->amount;
                $invoice->save();

                // Transaction
                $trans = new Transaction();
                $trans->customer_id = $customerId;
                $trans->amount = $invoice->id;
                $trans->partner_id = $partnerId;
                $trans->shop_id = $request->shopId;
                $trans->pointsAwarded = $points;
                $trans->save();

                //Send Email to group and SMS to customer.

                $data['customerName'] = $customer->firstName.' '. $customer->lastName;
                $data['customerPhone'] = $customer->phoneNum;
                $data['amount'] = $invoice->amount;
                $data['awardedPoint'] = $points;
                $data['totalPoints'] = $acc->point;

                return $this->sendResponse($data, 'Accrued Points have been updated');


            }
            // Create a loyalty account if customer has none.
            elseif ($acc === NULL) {
                $points = $request->amount*($rule/100);

                $acc = new Account();
                $acc->customer_id = $customer->id;
                $acc->point = $points;
                $acc->save();

                // Save Invoice details.
                $invoice = new Invoice();
                $invoice->invoiceCode = $request->invoiceNum;
                $invoice->customer_id = $customerId;
                $invoice->amount = $request->amount;
                $invoice->save();

                // Transaction
                $trans = new Transaction();
                $trans->transId = "SH-". substr(md5(uniqid(rand(), true)),0,7);
                $trans->customer_id = $customerId;
                $trans->amount = $request->amount;
                $trans->partner_id = $partnerId;
                $trans->shop_id = $request->shopId;
                $trans->pointsAwarded = $points;
                $trans->save();

                $data['customerName'] = $customer->firstName.' '. $customer->lastName;
                $data['customerPhone'] = $customer->phoneNum;
                $data['amount'] = $invoice->amount;
                $data['points'] = $points;
                // Send Email to group and SMS to customer.
                return $this->sendResponse($data, 'Loyalty account created & points awarded.');

            }
            else{
                return response()->json(['error'=>'An error occured adding points'],500);
            }

      //  return response()->json(['message'=>'No record for this customer'],404);

    }

    public function makeClaims(Request $request){

        $customerId = $request->id;
        $invoiceNum = $request->invoiceNum;
        $claim = request('claim',0);

        //Check if invoice is already used.
        $invoice = Invoice::where('invoiceCode', $invoiceNum)->first();

        $customer = Customer::findorFail($customerId)->first();


            if($invoice){
                return $this->sendError('This invoice has been used');
            }
            $acc = Account::where('customer_id', $customerId)->first();
            $loyaltyConfig = LoyaltySetting::where('status', 'ACTIVE')->first();
            $rule = $loyaltyConfig->rule;

            // Update loyalty points if customer is already registered.
            if($acc){

               // $initialPoints = $request->amount*($rule/100);
                $points = $request->amount*($rule/100);
                $totalPoints = $acc->point+=$points;
                $balance = $totalPoints - $claim;

                //Check if claim is more than accumulated points.
                if($balance < 0){
                    return $this->sendError('Your claims cannot be more than your accumulated points');
                }
                $acc->point = $balance;
                $acc->update();

                //Save Invoice details
                $invoice = new Invoice();
                $invoice->invoiceCode = $request->invoiceNum;
                $invoice->customer_id = $customerId;
                $invoice->amount = $request->amount;
                $invoice->save();

                //Send Email to group and SMS to customer.

                $data['customerName'] = $customer->firstName.' '. $customer->lastName;
                $data['customerPhone'] = $customer->phoneNum;
                $data['amount'] = $invoice->amount;
                $data['awardedPoint'] = $points;
                $data['claims'] = $claim;
                $data['balance'] = $balance;

                return $this->sendResponse($data, 'Accrued Points have been updated');


            }
            // Create a loyalty account if customer has none.
            elseif ($acc === NULL) {
                // Get awarded points
                $points = request('amount')*($rule/100);
                $balance = $points - $claim;
                if($balance < 0){
                    return $this->sendError('Your claims cannot be more than your accumulated points');
                }
                $acc = new Account();
                $acc->customer_id = $customer->id;
                $acc->point = $balance;
                $acc->save();

                // Save Invoice details.
                $invoice = new Invoice();
                $invoice->invoiceCode = $request->invoiceNum;
                $invoice->customer_id = $customerId;
                $invoice->amount = $request->amount;
                $invoice->save();

                // Claim Table

                $data['customerName'] = $customer->firstName.' '. $customer->lastName;
                $data['customerPhone'] = $customer->phoneNum;
                $data['amount'] = $invoice->amount;
                $data['points'] = $points;
                $data['claim'] = $claim;
                $data['balance'] = $balance;
                // Send Email to group and SMS to customer.
                return $this->sendResponse($data, 'Loyalty account created & points awarded & your claims were successful.');

            }
            else{
                return response()->json(['error'=>'An error occured adding or claiming points'],500);
            }



    }

    public function getCustomerAccruedPoint(Request $request, $id){
        $id = $request->id;
        $point = Account::where('customer_id', $id)->first();
        return \response()->json(['message'=>$point],200);

    }


}
