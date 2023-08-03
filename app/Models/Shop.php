<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model{

     use HasFactory;

   /* protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true; */

    protected $fillable = [
        'name',  'address', 'location','shopCode','choice'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function rep(){
        return $this->belongsTo(Rep::class);
    }

}
