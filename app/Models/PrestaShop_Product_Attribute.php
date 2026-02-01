<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestaShop_Product_Attribute extends Model
{
    protected $connection = 'mysql_ecopizza_stage';
    protected $table = 'ps_product_attribute';
    public $timestamps = false;
    public static $id_lang = 2;

    public static function getProductIdByAttributeId($id)
    {
        return self::where('id_product_attribute', $id)->first();
    }

    public static function getAttributesByProductId($id)
    {
        return self::leftJoin('ps_product_attribute_combination', 'ps_product_attribute_combination.id_product_attribute', 'ps_product_attribute.id_product_attribute')
            ->leftJoin('ps_attribute', 'ps_attribute.id_attribute', 'ps_product_attribute_combination.id_attribute')
            ->leftJoin('ps_attribute_lang', 'ps_attribute_lang.id_attribute', 'ps_attribute.id_attribute')
            ->where('ps_product_attribute.id_product', $id)
            ->where('ps_attribute_lang.id_lang', self::$id_lang)
            ->get();

    }

    public static function getAttributesAll()
    {
        return self::leftJoin('ps_product_attribute_combination', 'ps_product_attribute_combination.id_product_attribute', 'ps_product_attribute.id_product_attribute')
            ->leftJoin('ps_attribute', 'ps_attribute.id_attribute', 'ps_product_attribute_combination.id_attribute')
            ->leftJoin('ps_attribute_lang', 'ps_attribute_lang.id_attribute', 'ps_attribute.id_attribute')
            ->leftJoin('ps_stock_available', 'ps_stock_available.id_product_attribute', 'ps_product_attribute.id_product_attribute')
            ->where('ps_attribute_lang.id_lang', self::$id_lang)
            ->where('ps_stock_available.quantity', '>', 0)
            ->orderBy('ps_product_attribute.id_product_attribute', 'asc')
            ->get();
    }

}
