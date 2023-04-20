<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model{

    /* use HasUuids;

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true; */

    protected $fillable = [
        'name',  'address', 'location','status',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

}
