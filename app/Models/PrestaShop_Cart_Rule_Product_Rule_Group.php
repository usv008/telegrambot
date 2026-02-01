<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestaShop_Cart_Rule_Product_Rule_Group extends Model
{
    protected $connection = 'mysql_ecopizza_stage';
    protected $table = 'ps_cart_rule_product_rule_group';
//    protected $primaryKey = 'id_cart';
    public $timestamps = false;

    public static function findRuleByIdCartRule($id)
    {
        return self::where('id_cart_rule', $id)
            ->join('ps_cart_rule_product_rule', 'ps_cart_rule_product_rule.id_product_rule_group', 'ps_cart_rule_product_rule_group.id_product_rule_group')
            ->join('ps_cart_rule_product_rule_value', 'ps_cart_rule_product_rule_value.id_product_rule', 'ps_cart_rule_product_rule.id_product_rule')
            ->groupBy('ps_cart_rule_product_rule_value.id_item')
            ->select('ps_cart_rule_product_rule_value.id_item')
            ->get();
    }


}
