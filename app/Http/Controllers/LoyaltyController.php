<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Account;
use App\Models\Invoice;
use App\Mail\AwardPoint;
use App\Mail\ClaimPoint;
use App\Models\Customer;
use App\Models\Withdrawal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\LoyaltySetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\BaseController;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
        $this->validate($request,[
            'shopId' => 'required',
            'invoiceNum' => 'required',
            'amount' => 'required',
            'id' => 'required',
            'companyId'=> 'required'

        ],
        $messages = [
            'shopId.required' => 'Center ID is not provided.',
            'invoiceNum.required' => 'Invoice number is not provided.',
            'amount.required' => 'Amount purchased is not provided.',
            'id.required' => 'Customer ID is not provided',
            'companyId.required' => 'Company ID is not provided'
        ]
    );

        $customerId = $request->id;
        $invoiceNum = $request->invoiceNum;
        $repId = auth('rep')->user()->id;
        $repName = auth('rep')->user()->firstName;
        $shopId = $request->shopId;
        $compId = $request->companyId;
      //  $sageCode = $request->


        // Check if rep is assigned to shop
        $center = Shop::where('rep_id', $repId)->first();
       // dd($center->name);

        if($center->count() == NULL){
            return $this->sendError('Operation failed. Center is not assigned to rep: '.$repName);
        }

        //Check if center has company code

        if($center->companyCode == NULL){
            $center->companyCode = $compId;
            $center->update();
        }else{
            if($center->companyCode != $compId){
                return $this->sendError('Not allowed to award points from center not assigned to you.');
            }
        }

        //Check if invoice is already used.
        $invoice = Invoice::where('invoiceCode', $invoiceNum)
                            ->where('companyId', $compId)
                            ->first();

        try {
            $customer = Customer::findorFail($customerId);
        } catch (ModelNotFoundException $th) {
            return $this->sendError('Invalid customer identifier given.');

        }
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
                $points = floor($request->amount*($rule/100));
                $acc->point +=$points;
                $acc->visit = $acc->visit+1;
                $acc->update();

                //Save Invoice details
                $invoice = new Invoice();
                $invoice->invoiceCode = $request->invoiceNum;
                $invoice->customer_id = $customerId;
                $invoice->amount = $request->amount;
                $invoice->companyId = $request->companyId;
                $invoice->save();

                // Transaction
                $trans = new Transaction();
                $trans->transId = uniqid("CAP-");
                $trans->customer_id = $customerId;
                $trans->amount = $invoice->amount;
                $trans->rep_id = $repId;
                $trans->shop_id = $shopId;
                $trans->invoice_id = $invoice->id;
                $trans->awardedPoints = $points;
                $trans->save();


                //Get transaction details.
                $data['customerName'] = $customer->firstName.' '. $customer->lastName;
                $data['customerPhone'] = $customer->phoneNum;
                $data['amountPurchased'] = number_format($invoice->amount,2);
                $data['awardedPoint'] = $points;
                $data['center'] = $center->name;
                $data['totalPoints'] = $acc->point;

                //Send Email to group and SMS to customer.
                Mail::to($customer->email)->send(new AwardPoint($data));

                return $this->sendResponse($data, 'Accrued Points have been updated');


            }
            // Create a loyalty account if customer has none or new.
            elseif ($acc === NULL) {
                $points = floor($request->amount*($rule/100));

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
                $invoice->companyId = $request->companyId;
                $invoice->save();

                // Transaction
                $trans = new Transaction();
                $trans->transId = "SH-". substr(md5(uniqid(rand(), true)),0,7);
                $trans->customer_id = $customerId;
                $trans->amount = $request->amount;
                $trans->rep_id = $repId;
                $trans->invoice_id = $invoice->id;
                $trans->shop_id = $request->shopId;
                $trans->awardedPoints = $points;
                $trans->save();

                $data['customerName'] = $customer->firstName.' '. $customer->lastName;
                $data['customerPhone'] = $customer->phoneNum;
                $data['amountPurchased'] = number_format($invoice->amount,2);
                $data['awardedPoint'] =number_format($points,2);
                $data['center'] = $center->name;
                $data['invoiceNum'] = $invoice->invoiceCode;
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

        $this->validate($request,[
            'shopId' => 'required',
            'invoiceNum' => 'required',
            'amount' => 'required',
            'id' => 'required',
            'companyId' => 'required'

        ],
        $messages = [
            'shopId.required' => 'Center ID is not provided.',
            'invoiceNum.required' => 'Invoice number is not provided.',
            'amount.required' => 'Amount purchased is not provided.',
            'id.required' => 'Customer ID is not provided'
        ]
    );

        $customerId = $request->id;
        $invoiceNum = $request->invoiceNum;
        $shopId = $request->shopId;
        $repId = auth('rep')->user()->id;
        $repName = auth('rep')->user()->firstName;
        $claim = request('claim',0);
        $compId = $request->companyId;

        // Check if rep is assigned to shop.
       /*  $center = DB::table('shops')->where('shops.id', $shopId)
                                    ->join('reps', 'reps.id', '=', 'shops.rep_id')
                                    ->get(); */

            $center = Shop::where('rep_id', $repId)->first();

        //Check if invoice is already used.
         $invoice = Invoice::where('invoiceCode', $invoiceNum)
                            ->where('companyId', $compId)
                            ->first();

        // Get customer's details.
        try {
            $customer = Customer::findorFail($customerId);
        } catch (ModelNotFoundException $th) {
            return $this->sendError('Invalid customer ID', $th->getMessage());
        }


        if($center->count() == NULL){
            return $this->sendError('Operation failed. Center is not assigned to rep: '.$repName);
        }

        //Check if center has company code

        if($center->companyCode == NULL){
            $center->companyCode = $compId;
            $center->update();
        }else{
            if($center->companyCode != $compId){
                return $this->sendError('Not allowed to award points from center not assigned to you.');
            }
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
                $points = floor($request->amount*($rule/100));
                $totalPoints = $acc->point+$points;
                $balance = $totalPoints - $claim;

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
                $invoice->companyId = $request->companyId;
                $invoice->save();

                //Save transaction
                $trans = new Transaction();
                $trans->transId = "CAP-". substr(md5(uniqid(rand(), true)),0,7);
                $trans->customer_id = $customerId;
                $trans->amount = $request->amount;
                $trans->rep_id = $repId;
                $trans->shop_id = $request->shopId;
                $trans->invoice_id = $invoice->id;
                $trans->awardedPoints = $points;
                $trans->save();

                // Withdrawals
                $withdrawal = new Withdrawal();
                $withdrawal->customer_id = $customerId;
                $withdrawal->shop_id = $shopId;
                $withdrawal->rep_id = $repId;
                $withdrawal->invoice_id = $invoice->id;
                $withdrawal->pointsRedeemed = $claim;
                $withdrawal->save();

                // Get details
                $data['customerName'] = $customer->firstName.' '. $customer->lastName;
                $data['customerPhone'] = $customer->phoneNum;
                $data['amount'] = number_format($invoice->amount,2);
                $data['points'] = $points;
                $data['claims'] = number_format($claim,2);
                $data['balance'] = number_format($balance);

                //Send Email to group and SMS to customer.
                Mail::to($customer->email)->send(new ClaimPoint($data));

                //Log transactions to Activity table

                return $this->sendResponse($data, 'Accrued Points have been updated');


            }
            // Create a loyalty account if customer has none.
            elseif ($acc === NULL) {
                // Get awarded points
                $points = floor(request('amount')*($rule/100));
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
                $invoice->companyId = $request->companyId;
                $invoice->save();

                //Save Transactions
                $trans = new Transaction();
                $trans->transId = "SH-". substr(md5(uniqid(rand(), true)),0,7);
                $trans->customer_id = $customerId;
                $trans->amount = $request->amount;
                $trans->rep_id = $repId;
                $trans->shop_id = $request->shopId;
                $trans->invoice_id = $invoice->id;
                $trans->awardedPoints = $points;
                $trans->save();

                // Save claim details
                $withdrawal = new Withdrawal();
                $withdrawal->customer_id = $customerId;
                $withdrawal->shop_id = $shopId;
                $withdrawal->rep_id = $repId;
                $withdrawal->invoice_id = $invoice->id;
                $withdrawal->pointsRedeemed = $claim;
                $withdrawal->save();

                $data['customerName'] = $customer->firstName.' '. $customer->lastName;
                $data['customerPhone'] = $customer->phoneNum;
                $data['amount'] = number_format($invoice->amount,2);
                $data['points'] = $points;
                $data['claims'] = number_format($claim,2);
                $data['balance'] =number_format($balance);

                // Send Email to group and SMS to customer.
                Mail::to($customer->email)->send(new ClaimPoint($data));


                return $this->sendResponse($data, 'Loyalty account created & points awarded & your claims were successful.');

            }
            else{
                return $this->sendError('An error occured adding or claiming points');
            }



    }
}
