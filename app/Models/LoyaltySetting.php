<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltySetting extends Model{

public $timeStamps =false;

protected $fillable = ['name', 'rule', 'status'];



}
