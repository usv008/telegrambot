<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestaShop_Order_History extends Model
{
    protected $connection = 'mysql_ecopizza_stage';
    protected $table = 'ps_order_history';
    public $timestamps = false;

}
