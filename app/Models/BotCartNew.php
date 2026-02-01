<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class BotCartNew extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_cart_new';
    public $timestamps = false;

    public static function getProductsByUserId($user_id)
    {
        return self::where('user_id', $user_id)->orderBy('id', 'asc')->get();
    }

    public static function getProductByUserIdProductIdCombinationId($user_id, $product_id, $combination_id)
    {
        return self::where('user_id', $user_id)->where('product_id', $product_id)->where('combination_id', $combination_id)->get();
    }

    public static function getProductByUserIdAndId($user_id, $id)
    {
        return self::where('user_id', $user_id)->where('id', $id)->first();
    }

    public static function deleteProductFromCart($user_id, $id)
    {
        return self::where('user_id', $user_id)
            ->where(function ($query) use ($id) {
                $query->where('id', $id)
                    ->orWhere('parent_id', $id);
            })
            ->delete();
    }

    public static function getChildrenProducts($id)
    {
        return self::where('parent_id', $id)->get();
    }

    public static function addProductPresentToCart($data)
    {
        $product_in_cart = self::where('user_id', $data['user_id'])
            ->where('product_id', $data['product_id'])
            ->where('combination_id', $data['combination_id'])
            ->where('product_present', 1)
            ->first();

        if (!$product_in_cart || $product_in_cart->count() == 0) {
            $cart = new BotCartNew;
            $cart->user_id = $data['user_id'];
            $cart->category_id = 41;
            $cart->product_id = $data['product_id'];
            $cart->combination_id = $data['combination_id'];
            $cart->product_name = $data['product_name'];
            $cart->quantity = 1;
            $cart->price = $data['price'];
            $cart->price_all = $data['price'];
            $cart->price_with_ingredients = $data['price'];
            $cart->product_present = 1;
            $cart->save();
            Log::info($cart);
            return $cart;
        }
//        elseif ($product_in_cart->quantity < 2) {
//            $quantity = $product_in_cart->quantity + 1;
//            $price_all = bcmul($data['price'], $quantity, 2);
//            $product_in_cart_update = self::where('id', $product_in_cart->id)->update(['quantity' => $quantity, 'price_all' => $price_all, 'price_with_ingredients' => $price_all]);
//            return $product_in_cart_update;
//        }
        return null;
    }

    public static function countCherryInCartByUserId($user_id)
    {
        return self::where('user_id', $user_id)->where('product_cherry', 1)->count();
    }

}
