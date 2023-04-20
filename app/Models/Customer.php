<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model{
   /* protected $primaryKey = 'phoneNum';

   public $incrementing = false;

   protected $keyType = 'string';
 */

     protected $fillable = [
        'firstName', 'email','lastName','phoneNum','address','gender','image'

     ];

     protected $hidden = [
        'password',
    ];

    public function loyaltyaccount(){

        return $this->hasOne(LoyaltyAccount::class);
    }

    public function withdrawals(){

        return $this->hasMany(Withdrawal::class);

    }

}
