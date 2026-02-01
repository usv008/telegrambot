<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotCart;
use App\Models\BotRaffle;
use App\Models\BotRaffleTest;
use App\Models\BotRaffleUsers;
use App\Models\BotRaffleUsersHistory;
use App\Models\BotSettingsRaffleImages;
use App\Http\Controllers\Controller;
use App\Models\Simpla_Products;
use App\Models\Simpla_Variants;
use Longman\TelegramBot\Request;


class BotPresentController extends Controller
{

    public static function addPresentToCart($user_id, $variant_id) {

        $date_z = date("Y-m-d H:i:s");

        $variant = Simpla_Variants::where('id', $variant_id)->first();

        $simpla_product = BotMenuController::get_product_sql($user_id, $variant->product_id);

        $cart = new BotCart;
        $cart->id_user = $user_id;
        $cart->category = 'pizza';
        $cart->id_tovar = $variant->product_id;
        $cart->id_size = $variant_id;
        $cart->product_name = $simpla_product['name'];
        $cart->quantity = 1;
        $cart->price = $variant->price;
        $cart->price_all = $variant->price;
        $cart->action_pizza = 0;
        $cart->product_present = 1;
        $cart->date_reg = $date_z;
        $cart->date_edit = $date_z;
        $cart->save();

    }

}
