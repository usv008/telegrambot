<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimplaOrdersDuble extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 's_orders_duble';
    public $timestamps = false;
}
