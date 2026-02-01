<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestaShop_Cart_Rule_Product_Rule_Value extends Model
{
    protected $connection = 'mysql_ecopizza_stage';
    protected $table = 'ps_cart_rule_product_rule_value';
//    protected $primaryKey = 'id_cart';
    public $timestamps = false;
}
