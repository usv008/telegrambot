<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestaShop_Accessory_Group_Product extends Model
{
    protected $connection = 'mysql_ecopizza_stage';
    protected $table = 'ps_accessory_group_product';
    public $timestamps = false;
    public static $id_lang = 2;

    public static function getAccessoriesByProductId($id)
    {
        $result = self::leftJoin('ps_accessory_group', 'ps_accessory_group.id_accessory_group', 'ps_accessory_group_product.id_accessory_group')
            ->leftJoin('ps_accessory_group_lang', 'ps_accessory_group_lang.id_accessory_group', 'ps_accessory_group.id_accessory_group')
            ->leftJoin('ps_product', 'ps_product.id_product', 'ps_accessory_group_product.id_accessory')
            ->leftJoin('ps_product_lang', 'ps_product_lang.id_product', 'ps_product.id_product')
            ->leftJoin('ps_stock_available', 'ps_stock_available.id_product', 'ps_product.id_product')
            ->where('ps_stock_available.id_product_attribute', '!=', 0)
            ->where('ps_stock_available.quantity', '>', 0)
            ->where('ps_accessory_group_product.id_product', $id)
            ->where('ps_accessory_group_lang.id_lang', self::$id_lang)
            ->where('ps_product_lang.id_lang', self::$id_lang)
            ->orderBy('ps_product_lang.name', 'asc')
            ->get([
                'ps_accessory_group_product.id_product as id_product',
                'ps_accessory_group_lang.name as category_name',
                'ps_product_lang.name as product_name',
                'ps_accessory_group_product.id_accessory as id_accessory',
                'ps_accessory_group_product.id_accessory_group',
            ]);

//        dd($result);
        return $result;
    }

}
