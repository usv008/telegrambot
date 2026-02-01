<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestaShop_Cart_Cart_Rule extends Model
{
    protected $connection = 'mysql_ecopizza_stage';
    protected $table = 'ps_cart_cart_rule';
//    protected $primaryKey = 'id_cart';
    public $timestamps = false;
}
