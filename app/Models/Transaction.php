<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model{

    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    /* public function addTrans(Request $request, Customer $customer){
        $trans = new Transaction();
                $trans->customer_id = $customerId;
                $trans->amount = $request->amount;
                $trans->partner_id = $request->partnerId;
                $trans->shop_id = $request->shopId;
                $trans->pointsAwarded = $points;
    } */

}
