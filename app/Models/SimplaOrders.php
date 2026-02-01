<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimplaOrders extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 's_orders';
    public $timestamps = false;
}
