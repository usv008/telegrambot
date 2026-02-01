<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestaShop_Orders extends Model
{
    protected $connection = 'mysql_ecopizza_stage';
    protected $table = 'ps_orders';
    public $timestamps = false;
}
