<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogisticsOrders extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'logistics_orders';
    public $timestamps = false;
}
