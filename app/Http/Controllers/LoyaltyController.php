<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Account;
use App\Models\Invoice;
use App\Mail\AwardPoint;
use App\Models\Customer;
use App\Models\Withdrawal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\LoyaltySetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\BaseController;

class LoyaltyController extends BaseController
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
     * Award Loyalty Points.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addLoyaltyPoints(Request $request){

        $customerId = $request->id;
        $invoiceNum = $request->invoiceNum;
        $repId = auth('rep')->user()->id;
        $repName = auth('rep')->user()->firstName;
        $shopId = $request->shopId;


        // Check if rep is assigned to shop
        $center = Shop::where('rep_id', $repId)->first();
       // dd($center->name);

        if($center->count() == NULL){
            return $this->sendError('Operation failed. Center is not assigned to rep: '.$repName);
        }

        //Check if invoice is already used.
        $invoice = Invoice::where('invoiceCode', $invoiceNum)->first();

        $customer = Customer::findorFail($customerId);



            if($invoice){
                return $this->sendError('This invoice has been used.');
            }
            $acc = Account::where('customer_id', $customerId)->first();
            $loyaltyConfig = LoyaltySetting::where('status', 'ACTIVE')->first();
         //  dd($loyaltyConfig);
            if($loyaltyConfig == NULL){
               return $this->sendError('Loyalty rule is not set or disabled');
            }
            $rule = $loyaltyConfig->rule;

            // Update loyalty points if customer is already registered.
            if($acc){
               // $initialPoints = $request->amount*($rule/100);
                $points = $request->amount*($rule/100);
                $acc->point +=$points;
                $acc->visit = $acc->visit+1;
                $acc->update();

                //Save Invoice details
                $invoice = new Invoice();
                $invoice->invoiceCode = $request->invoiceNum;
                $invoice->customer_id = $customerId;
                $invoice->amount = $request->amount;
                $invoice->save();

                // Transaction
                $trans = new Transaction();
                $trans->transId = uniqid("CAP-");
                $trans->customer_id = $customerId;
                $trans->amount = $invoice->amount;
                $trans->rep_id = $repId;
                $trans->shop_id = $shopId;
                $trans->awardedPoints = $points;
                $trans->save();


                //Get transaction details.
                $data['customerName'] = $customer->firstName.' '. $customer->lastName;
                $data['customerPhone'] = $customer->phoneNum;
                $data['amountPurchased'] = $invoice->amount;
                $data['awardedPoint'] = $points;
                $data['center'] = $center->name;
                $data['totalPoints'] = $acc->point;

                //Send Email to group and SMS to customer.
                Mail::to($customer->email)->send(new AwardPoint($data));

                return $this->sendResponse($data, 'Accrued Points have been updated');


            }
            // Create a loyalty account if customer has none or new.
            elseif ($acc === NULL) {
                $points = $request->amount*($rule/100);

                $acc = new Account();
                $acc->customer_id = $customer->id;
                $acc->point = $points;
                $acc->visit = $acc->visit+1;
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
                $trans->rep_id = $repId;
                $trans->shop_id = $request->shopId;
                $trans->awardedPoints = $points;
                $trans->save();

                $data['customerName'] = $customer->firstName.' '. $customer->lastName;
                $data['customerPhone'] = $customer->phoneNum;
                $data['amountPurchased'] = $invoice->amount;
                $data['awardedPoint'] = $points;
                $data['center'] = $center->name;
                $data['totalPoints'] = $acc->point;

                // Send Email to group and SMS to customer.
                Mail::to($customer->email)->send(new AwardPoint($data));

                return $this->sendResponse($data, 'Loyalty account created & points awarded.');

            }
            else{
                return response()->json(['error'=>'An error occured adding points'],500);
            }

      //  return response()->json(['message'=>'No record for this customer'],404);

    }


    /**
     * Make Claims or withdrawals.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function makeClaims(Request $request){

        $customerId = $request->id;
        $invoiceNum = $request->invoiceNum;
        $shopId = $request->shopId;
        $repId = auth('rep')->user()->id;
        $repName = auth('rep')->user()->firstName;
        $claim = request('claim',0);

        // Check if rep is assigned to shop.
        $center = DB::table('shops')->where('shops.id', $shopId)
                                    ->join('reps', 'reps.id', '=', 'shops.rep_id')
                                    ->count();

        //Check if invoice is already used.
        $invoice = Invoice::where('invoiceCode', $invoiceNum)->first();

        // Get customer's details.
        $customer = Customer::findorFail($customerId);

        if($center == NULL){
            return $this->sendError('Operation failed. Center is not assigned to rep: '.$repName);
        }


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
                $totalPoints = $acc->point+$points;
                $balance = $totalPoints - $claim;
             //   dd($balance);

                //Check if claim is more than accumulated points.
                if($balance < 0){
                    return $this->sendError('Your claims cannot be more than your accumulated points');
                }
                $acc->point = $balance;
                $acc->visit = $acc->visit+1;
                $acc->update();

                //Save Invoice details
                $invoice = new Invoice();
                $invoice->invoiceCode = $request->invoiceNum;
                $invoice->customer_id = $customerId;
                $invoice->amount = $request->amount;
                $invoice->save();

                //Save transaction
                $trans = new Transaction();
                $trans->transId = "SH-". substr(md5(uniqid(rand(), true)),0,7);
                $trans->customer_id = $customerId;
                $trans->amount = $request->amount;
                $trans->rep_id = $repId;
                $trans->shop_id = $request->shopId;
                $trans->awardedPoints = $points;
                $trans->save();


                // Withdrawals
                $withdrawal = new Withdrawal();
                $withdrawal->customer_id = $customerId;
                $withdrawal->shop_id = $shopId;
                $withdrawal->rep_id = $repId;
                $withdrawal->pointsRedeemed = $claim;
                $withdrawal->save();

                // Get details
                $data['customerName'] = $customer->firstName.' '. $customer->lastName;
                $data['customerPhone'] = $customer->phoneNum;
                $data['amount'] = $invoice->amount;
                $data['awardedPoint'] = $points;
                $data['claims'] = $claim;
                $data['balance'] = $balance;

                //Send Email to group and SMS to customer.

                //Log transactions to Activity table

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
                $acc->visit = $acc->visit+1;
                $acc->save();

                // Save Invoice details.
                $invoice = new Invoice();
                $invoice->invoiceCode = $request->invoiceNum;
                $invoice->customer_id = $customerId;
                $invoice->amount = $request->amount;
                $invoice->save();

                //Save Transactions
                $trans = new Transaction();
                $trans->transId = "SH-". substr(md5(uniqid(rand(), true)),0,7);
                $trans->customer_id = $customerId;
                $trans->amount = $request->amount;
                $trans->rep_id = $repId;
                $trans->shop_id = $request->shopId;
                $trans->awardedPoints = $points;
                $trans->save();

                // Save claim details
                $withdrawal = new Withdrawal();
                $withdrawal->customer_id = $customerId;
                $withdrawal->shop_id = $shopId;
                $withdrawal->rep_id = $repId;
                $withdrawal->pointsRedeemed = $claim;
                $withdrawal->save();

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
}
