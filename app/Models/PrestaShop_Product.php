<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestaShop_Product extends Model
{
    protected $connection = 'mysql_ecopizza_stage';
    protected $table = 'ps_product';
    public $timestamps = false;
    public static $id_lang = 2;

    public static function getProductById($id)
    {
        return self::join('ps_product_lang', 'ps_product_lang.id_product', 'ps_product.id_product')
            ->leftJoin('ps_feature_product', 'ps_feature_product.id_product', 'ps_product.id_product')
            ->leftJoin('ps_feature_value_lang', 'ps_feature_value_lang.id_feature_value', 'ps_feature_product.id_feature_value')
            ->where('ps_product.id_product', $id)
            ->where('ps_product.active', 1)
            ->where('ps_product_lang.id_lang', self::$id_lang)
            ->where('ps_feature_value_lang.id_lang', self::$id_lang)
            ->first();
    }

    public static function getProductsAll()
    {
        return self::join('ps_product_lang', 'ps_product_lang.id_product', 'ps_product.id_product')
            ->where('ps_product_lang.id_lang', self::$id_lang)
            ->get();
    }

    public static function getProductsByCategoryId($id, $categories_array = [])
    {
        $result = collect();
        $products = self::join('ps_product_lang', 'ps_product_lang.id_product', 'ps_product.id_product')
            ->join('ps_category_product', 'ps_category_product.id_product', 'ps_product.id_product')
            ->leftJoin('ps_category', 'ps_category.id_parent', 'ps_product.id_category_default')
            ->leftJoin('ps_category_lang', 'ps_category_lang.id_category', 'ps_product.id_category_default')
            ->where('ps_product.active', 1)
            ->where('ps_product_lang.id_lang', self::$id_lang)
            ->where('ps_category_lang.id_lang', self::$id_lang)
            ->where(function ($query) use ($id) {
                $query->where('ps_category_product.id_category', $id);
            })
            ->orderBy('ps_category_product.position', 'asc')
            ->groupBy('ps_product.id_product')
            ->get([
                'ps_category_lang.name as category_name',
                'ps_product.id_product',
                'ps_product_lang.name',
                'ps_product.id_category_default',
                'ps_category_product.id_category',
                'ps_category_product.position',
            ]);
        foreach ($products as $product) {
            $result->push($product);
        }
        if (count($categories_array) > 0) {
            foreach ($categories_array as $category) {
                $products = self::join('ps_product_lang', 'ps_product_lang.id_product', 'ps_product.id_product')
                    ->join('ps_category_product', 'ps_category_product.id_product', 'ps_product.id_product')
                    ->leftJoin('ps_category', 'ps_category.id_parent', 'ps_product.id_category_default')
                    ->leftJoin('ps_category_lang', 'ps_category_lang.id_category', 'ps_product.id_category_default')
                    ->where('ps_product.active', 1)
                    ->where('ps_product_lang.id_lang', self::$id_lang)
                    ->where('ps_category_lang.id_lang', self::$id_lang)
                    ->where(function ($query) use ($id, $category) {
                        $query->where('ps_category_product.id_category', $category);
                    })
                    ->orderBy('ps_category_product.position', 'asc')
                    ->groupBy('ps_product.id_product')
                    ->get([
                        'ps_category_lang.name as category_name',
                        'ps_product.id_product',
                        'ps_product_lang.name',
                        'ps_product.id_category_default',
                        'ps_category_product.id_category',
                        'ps_category_product.position',
                    ]);
                foreach ($products as $product) {
                    $result->push($product);
                }
            }
//            $result = self::join('ps_product_lang', 'ps_product_lang.id_product', 'ps_product.id_product')
//                ->join('ps_category_product', 'ps_category_product.id_product', 'ps_product.id_product')
//                ->leftJoin('ps_category', 'ps_category.id_parent', 'ps_product.id_category_default')
//                ->leftJoin('ps_category_lang', 'ps_category_lang.id_category', 'ps_product.id_category_default')
//                ->where('ps_product.active', 1)
//                ->where('ps_product_lang.id_lang', self::$id_lang)
//                ->where('ps_category_lang.id_lang', self::$id_lang)
//                ->where(function ($query) use ($id, $categories_array) {
//                    $query->where('ps_category_product.id_category', $id)
//                        ->orWhereIn('ps_category_product.id_category', $categories_array);
//                })
//                ->orderBy('ps_category_product.position', 'asc')
//                ->groupBy('ps_product.id_product')
//                ->get([
//                    'ps_category_lang.name as category_name',
//                    'ps_product.id_product',
//                    'ps_product_lang.name',
//                    'ps_product.id_category_default',
//                    'ps_category_product.id_category',
//                    'ps_category_product.position',
//                ]);
        }
//        dd($result);
        return $result;
    }

}
