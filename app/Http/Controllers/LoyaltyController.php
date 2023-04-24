<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Withdrawal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\LoyaltySetting;
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
        $repId = $request->repId;
        $shopId = $request->shopId;

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
                $trans->rep_id = $repId;
                $trans->shop_id = $shopId;
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
            // Create a loyalty account if customer has none or new.
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
                $trans->rep_id = $repId;
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


    /**
     * Make Claims or withdrawals.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function makeClaims(Request $request){

        $customerId = $request->id;
        $invoiceNum = $request->invoiceNum;
        $shopId = $request->shopId;
        $repId = $request->repId;
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

                // Withdrawals

                $withdrawal = new Withdrawal();
                $withdrawal->customer_id = $customerId;
                $withdrawal->shop_id = $request->shopId;
                $withdrawal->rep_id = $repId;
                $withdrawal->pointsRedeemed = $claim;

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
}
