<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestaShop_Feature_Product extends Model
{
    protected $connection = 'mysql_ecopizza_stage';
    protected $table = 'ps_feature_product';
    public $timestamps = false;
    public static $id_lang = 2;

    public static function getFeatureByProductId($id)
    {
        return self::join('ps_feature_lang', 'ps_feature_lang.id_feature', 'ps_feature_product.id_feature')
            ->join('ps_feature_value', 'ps_feature_value.id_feature_value', 'ps_feature_product.id_feature_value')
            ->join('ps_feature_value_lang', 'ps_feature_value_lang.id_feature_value', 'ps_feature_product.id_feature_value')
            ->where('ps_feature_product.id_product', $id)
            ->where('ps_feature_lang.id_lang', self::$id_lang)
            ->where('ps_feature_value_lang.id_lang', self::$id_lang)
            ->get();
    }

    public static function getFeatures()
    {
        return self::join('ps_feature_lang', 'ps_feature_lang.id_feature', 'ps_feature_product.id_feature')
            ->join('ps_feature_value', 'ps_feature_value.id_feature_value', 'ps_feature_product.id_feature_value')
            ->join('ps_feature_value_lang', 'ps_feature_value_lang.id_feature_value', 'ps_feature_product.id_feature_value')
            ->where('ps_feature_lang.id_lang', self::$id_lang)
            ->where('ps_feature_value_lang.id_lang', self::$id_lang)
            ->get();
    }

}
