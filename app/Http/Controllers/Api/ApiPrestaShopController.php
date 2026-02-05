<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Controllers\JsonValidatorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiPrestaShopController extends BaseController
{

    public static $key = 'NXUAQSSLS82SFTVBMXCBMCFIFRPVWJZR';

    public static function getUrlApi() {
        return str_replace('https://', '', config('services.prestashop.url')) . '/api';
    }

    public static function get($url)
    {

    }


    public static function sendResponse($input = [])
    {
        if (!isset($input['resource']) && $input['resource'] == null)
            return null;

        $resource = $input['resource'];

        if (!isset($input['url']) && $input['url'] == null)
            return null;

        $url = 'https://'.self::$key.'@'.self::getUrlApi().'/'.$input['url'];
//        if ($id !== null){
//            $url .= '/'.$id;
//        }

        $data = [
            'ws_key' => self::$key,
            'output_format' => 'JSON',
            'language' => 2,
        ];

        if  (isset($input['filter']) && $input['filter'] !== null && is_array($input['filter'])) {
            foreach ($input['filter'] as $key => $value) {
                $data['filter['.$key.']'] = '['.$value.']';
            }
        }

        if (isset($input['display']) && $input['display'] !== null && is_array($input['display']))
            $data['display'] = '['.implode(",", $input['display']).']';
        else $data['display'] = 'full';

//        if ($input['resource'] == 'categories') {
//            $data['filter[active]'] = '[1]';
//        }
        if ($input['resource'] == 'products') {
            if (isset($input['filter_array']) && is_array($input['filter_array'])) {
                $filter = '[';
                foreach ($input['filter_array'] as $product_id) {
                    if($product_id == end($input['filter_array'])) {
                        $filter .= $product_id.']';
                    }
                    else {
                        $filter .= $product_id.'|';
                    }
                }
                $data['filter[id]'] = $filter;
            }
//            else $data['filter[id_category_default]'] = '['.$input['category_id'].']';
            $data['sort'] = '[id_DESC]';
//            $data['display'] = '[id, id_default_image, position_in_category, active, available_for_order, name, associations]';
        }
        $response = Http::get($url, $data);
        if ($response->ok() && $response->successful()) {
            $response = JsonValidatorController::jsonValidate($response) ? json_decode($response) : $response;
            if (isset($response->$resource))
                $response = $response->$resource;
            $response = collect($response);
            return $response;
        }
        return 'Response bad';
    }

    public static function getCategories()
    {

    }

//    public static function test_presta()
//    {
//
////        $url_api = 'ecopizza.forforce.com/api';
//        $url_categories = $url.'/categories';
//        $url_products = $url.'/products';
//        $url_product_features = $url.'/product_feature_values';
//        $url_product_combinations = $url.'/combinations';
//        $product_id = 14;
//        $url_product = $url_products.'/'.$product_id;
//
////        $response_categories = Http::get($url_categories, [
////            'ws_key' => $key,
////            'output_format' => 'JSON',
////            'display' => 'full',
////            'language' => 2,
////        ]);
////        $categories = json_decode($response_categories);
////        $categories = collect($categories->categories);
////        dd($categories->where('id_parent', 2)->where('active', 1));
//
//        $response_products_features = Http::get($url_product_features, [
//            'ws_key' => $key,
//            'output_format' => 'JSON',
//            'display' => 'full',
//            'language' => 2,
//        ]);
//
//        $response_product_combinations = Http::get($url_product_combinations, [
//            'ws_key' => $key,
//            'output_format' => 'JSON',
//            'display' => 'full',
//            'language' => 2,
//        ]);
//
//        $response_products = Http::get($url_products, [
//            'ws_key' => $key,
//            'output_format' => 'JSON',
//            'display' => 'full',
//            'language' => 2,
//        ]);
//
//        $response_product = Http::get($url_product, [
//            'ws_key' => $key,
//            'output_format' => 'JSON',
//            'display' => 'full',
//            'language' => 2,
//        ]);
//
//        if (
//            $response_products_features->ok() && $response_products_features->successful()
//            && $response_product_combinations->ok() && $response_product_combinations->successful()
//            && $response_products->ok() && $response_products->successful()
//        ) {
//            $products_features = json_decode($response_products_features);
//            $products_features = $products_features->product_feature_values;
//            $products_features = collect($products_features);
//
//            $combinations = json_decode($response_product_combinations);
//            $combinations = $combinations->combinations;
//            $combinations = collect($combinations);
//
//            $result = json_decode($response_products);
//            dd($result);
//            $product = $result->products[0];
//            $name = $product->name;
//            $associations = $product->associations;
//            $id_default_image = $product->id_default_image;
//            $id_feature_value = $associations->product_features[0]->id_feature_value;
//            $description = $products_features->where('id', $id_feature_value)->first()->value;
//            $product_combinations = $combinations->where('id_product', $product_id);
//            $image = 'https://ecopizza.forforce.com/api/images/products/'.$product_id.'/'.$id_default_image;
//            dd($product, $name, $image, $description, $product_combinations, $associations);
//        }
//        return $response_products;
//
//    }

}
