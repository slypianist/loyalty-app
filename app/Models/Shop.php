<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model{

    use HasUuids;

    protected $hidden = [
        'name',  'address', 'location','status',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

}
