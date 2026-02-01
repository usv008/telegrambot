<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestaShop_Cart_Rule_Lang extends Model
{
    protected $connection = 'mysql_ecopizza_stage';
    protected $table = 'ps_cart_rule_lang';
//    protected $primaryKey = 'id_cart';
    public $timestamps = false;
}
