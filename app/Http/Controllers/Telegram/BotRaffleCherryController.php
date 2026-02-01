<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotCart;
use App\Models\BotCartNew;
use App\Models\BotRaffle;
use App\Models\BotRaffleCherry;
use App\Models\BotRaffleCherryTakeaway;
use App\Models\BotRaffleCherryTest;
use App\Models\BotRaffleTest;
use App\Models\BotRaffleUsers;
use App\Models\BotRaffleUsersHistory;
use App\Models\BotSettingsRaffleCherryImages;
use App\Models\BotSettingsRaffleImages;
use App\Http\Controllers\CashBackController;
use App\Http\Controllers\Controller;
use App\Models\PrestaShop_Product;
use App\Models\PrestaShop_Product_Attribute;
use App\Models\PrestaShop_Product_Attribute_Combination;
use App\Models\Simpla_Products;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Request;


class BotRaffleCherryController extends Controller
{

    private static string $command = 'Raffle_cherry';

    public static function getRaffleTry($user_id)
    {
        if (BotRaffleUsers::where('user_id', $user_id)->count() == 0) {
            $raffle_try = 2;
            $date_z = date("Y-m-d H:i:s");
            $raffle_users = new BotRaffleUsers;
            $raffle_users->user_id = $user_id;
            $raffle_users->raffle_cherry_try = $raffle_try;
            $raffle_users->date_reg = $date_z;
            $raffle_users->date_edit = $date_z;
            $raffle_users->save();
            return $raffle_try;
        }
        else {
            return self::getAllRaffleTry($user_id);
        }
    }

    public static function getAllRaffleTry($user_id) {
        $raffle = BotRaffleUsers::where('user_id', $user_id)->first();
        return $raffle['raffle_cherry_try'] + $raffle['raffle_try_guest'];
    }

    public static function getRaffleTryToday($user_id) {
        $date = date("Y-m-d");
        return BotRaffleCherry::where('user_id', $user_id)->where('date_reg', 'like', $date.'%')->count();
    }

    public static function getRaffleCherryTryToday($user_id) {
        $date = date("Y-m-d");
        return BotRaffleCherry::where('user_id', $user_id)->where('date_reg', 'like', $date.'%')->count();
    }

    public static function getTextChances($user_id, $n) {
        while ($n >= 30) $n = $n - 10;
        $text = BotTextsController::getText($user_id, self::$command, 'chances_text3');
        if (
            $n == 1
            || $n == 21
        ) $text = BotTextsController::getText($user_id, self::$command, 'chances_text1');
        elseif (
            $n == 2
            || $n == 3
            || $n == 4
            || $n == 22
            || $n == 23
            || $n == 24
        ) $text = BotTextsController::getText($user_id, self::$command, 'chances_text2');
        return $text;
    }

    public static function getTextAtempts($user_id, $n) {
        while ($n >= 30) $n = $n - 10;
        $text = BotTextsController::getText($user_id, self::$command, 'attempts_text3');
        if (
            $n == 1
            || $n == 21
        ) $text = BotTextsController::getText($user_id, self::$command, 'attempts_text1');
        elseif (
            $n == 2
            || $n == 3
            || $n == 4
            || $n == 22
            || $n == 23
            || $n == 24
        ) $text = BotTextsController::getText($user_id, self::$command, 'attempts_text2');
        return $text;
    }

    public static function newGame($user_id)
    {

        $arr_pizza = [];
        $i = 1;

        while ($i <= 6) {

            $rand = rand(1,12);
            if (!array_key_exists($rand, $arr_pizza)) {
                $arr_pizza[$rand] = 1;
                $i++;
            }

        }

        $date_z = date("Y-m-d H:i:s");

        $raffle_try = 0;

        $raffle = new BotRaffleCherry;
        $raffle->user_id = $user_id;
        $raffle->raffle_try = $raffle_try;

        for ($i = 1; $i <= 12; $i++) {
            $arr_pizza[$i] = isset($arr_pizza[$i]) && $arr_pizza[$i] !== null && $arr_pizza[$i] === 1 ? '🍒' : '😐';
            $p = 'p'.$i;
            $raffle->$p = $arr_pizza[$i];
        }

        $raffle->date_reg = $date_z;
        $raffle->date_edit = $date_z;

        if ($raffle->save()) return $raffle->id;
        else return null;

    }

    public static function updateRaffleMessageId($user_id, $raffle_id, $message_id) {

        return BotRaffleCherry::where('id', $raffle_id)->where('user_id', $user_id)->update(['message_id' => $message_id]);

    }

    public static function updateRaffleTry($user_id, $act) {

        if ($act == 'minus') {

            $raffle_user = BotRaffleUsers::where('user_id', $user_id)->first();
            if ($raffle_user['raffle_cherry_try'] > 0) {

                $raffle_try = $raffle_user['raffle_cherry_try'] - 1;
                return BotRaffleUsers::where('id', $raffle_user['id'])->where('user_id', $user_id)->update(['raffle_cherry_try' => $raffle_try]);

            }
            elseif ($raffle_user['raffle_try_guest'] > 0) {

                $raffle_try_guest = $raffle_user['raffle_try_guest'] - 1;
                return BotRaffleUsers::where('id', $raffle_user['id'])->where('user_id', $user_id)->update(['raffle_try_guest' => $raffle_try_guest]);

            }
            return null;

        }
        elseif ($act == 'update') {

            $raffle_user = BotRaffleUsers::where('user_id', $user_id)->first();
            if ($raffle_user && $raffle_user->raffle_try == 0) {
                $raffle_try = 2;
                BotRaffleUsers::where('id', $raffle_user->id)->where('user_id', $user_id)->update(['raffle_cherry_try' => $raffle_try]);
            }

        }

    }

    public static function updateRaffleCherryTry($user_id, $act) {

        if ($act == 'minus') {

            $raffle_user = BotRaffleUsers::where('user_id', $user_id)->first();
            if ($raffle_user['raffle_cherry_try'] > 0) {

                $raffle_try = $raffle_user['raffle_cherry_try'] - 1;
                return BotRaffleUsers::where('id', $raffle_user['id'])->where('user_id', $user_id)->update(['raffle_cherry_try' => $raffle_try]);

            }
            elseif ($raffle_user['raffle_try_guest'] > 0) {

                $raffle_try_guest = $raffle_user['raffle_try_guest'] - 1;
                return BotRaffleUsers::where('id', $raffle_user['id'])->where('user_id', $user_id)->update(['raffle_try_guest' => $raffle_try_guest]);

            }
            return null;

        }
        elseif ($act == 'update') {

            $raffle_user = BotRaffleUsers::where('user_id', $user_id)->first();
            if ($raffle_user && $raffle_user->raffle_cherry_try == 0) {
                $raffle_try = 2;
                BotRaffleUsers::where('id', $raffle_user->id)->where('user_id', $user_id)->update(['raffle_cherry_try' => $raffle_try]);
            }

        }

    }

    public static function selectBlock($user_id, $raffle_id, $p) {

        $raffle_p = BotRaffleCherry::where('id', $raffle_id)->where('user_id', $user_id)->first();
        $raffle_try = $raffle_p['raffle_try'];

        $guessed = 0;
        for ($i = 1; $i <= 12; $i++) {
            if ($raffle_p['p'.$i] == '___🍒') $guessed++;
        }

        $block = stripos($raffle_p['p'.$p], '___') !== false ? 1 : 0;
        if ($block == 0) {

            if ($raffle_try < 8){

                $raffle_try++;
                BotRaffleCherry::where('id', $raffle_id)->where('user_id', $user_id)->update(['p'.$p => '___'.$raffle_p['p'.$p], 'raffle_try' => $raffle_try]);
                if ($raffle_p['p'.$p] == '🍒') $guessed++;
                return [$guessed, $raffle_try, $raffle_p['p'.$p]];

            }
            else return null;

        }
        else {

            return [$guessed, $raffle_try, $raffle_p['p'.$p]];

        }

    }

    public static function updateRaffleWin($user_id, $raffle_id) {
        $check = BotRaffleCherry::where('id', $raffle_id)->where('user_id', $user_id)->where('win', 0)->count();
        if ($check > 0) {
            $update_raffle = BotRaffleCherry::where('id', $raffle_id)->where('user_id', $user_id)->update(['win' => 1]);
            $update_raffle_user = self::updateRaffleUsersWin($user_id);
            if ($update_raffle && $update_raffle_user)
                return true;
        }
        else return null;
    }

    public static function updateRaffleUsersWin($user_id) {

        $update_raffle_user = BotRaffleUsers::where('user_id', $user_id)->update(['win_cherry' => 1]);
        return $update_raffle_user;

    }

    public static function checkUserRaffleWin($user_id) {

        return BotRaffleUsers::where('user_id', $user_id)->where('win_cherry', 1)->count();

    }

    public static function checkUserCherryInCart($user_id) {

        return BotCartNew::where('user_id', $user_id)->where('product_action', 1)->count();

    }

    public static function checkUserWin($user_id) {

        $win = self::checkUserRaffleWin($user_id);
//        $in_cart = self::checkUserCherryInCart($user_id);
        $in_cart = 0;

        return $win + $in_cart;

    }

    public static function getRafflePizzas()
    {
        $products = Simpla_Products::join('s_products_categories', 's_products_categories.product_id', 's_products.id')
            ->join('s_variants', 's_variants.product_id', 's_products.id')
            ->where('s_products_categories.category_id', 52)
            ->where('s_products.visible', 1)
            ->orderBy('s_products.position', 'asc')
            ->get([
                's_products.id',
                's_products.name',
                's_products.name_ru',
                's_products.name_uk',
                's_products.name_en',
                's_variants.name as variant_name',
                's_variants.id as variant_id',
                's_variants.sku',
                's_variants.price'
            ]);
        return $products;
    }

    public static function addActionProductToCart($user_id, $product_id) {

//        BotRaffleUsers::where('user_id', $user_id)->update(['win_cherry' => 0]);

        $product_attributes = PrestaShop_Product_Attribute::all();
        $product = PrestaShop_Product::join('ps_product_lang', 'ps_product_lang.id_product', 'ps_product.id_product')
            ->where('ps_product.id_product', $product_id)
            ->where('ps_product.id_category_default', 39)
            ->where('ps_product_lang.id_lang', 2)
            ->first();
        $product_attribute = $product_attributes->where('id_product', $product->id_product)->where('default_on', 1)->first();
        if (!$product_attribute)
            $product_attribute = $product_attributes->where('id_product', $product->id_product)->first();
        $product->attribute = $product_attribute;
        $product->price = $product_attribute ? $product_attribute->price : 0;
        if ($product_attribute) {
            $product->price = $product_attribute->price;
            $id_product_attribute = $product_attribute->id_product_attribute;

            $cart = new BotCartNew;
            $cart->user_id = $user_id;
            $cart->category_id = $product->id_category_default;
            $cart->product_id = $product->id_product;
            $cart->combination_id = $id_product_attribute;
            $cart->product_name = $product->name;
            $cart->quantity = 1;
            $cart->price = $product->price;
            $cart->price_all = $product->price;
            $cart->price_with_ingredients = $product->price;
            $cart->product_cherry = 1;
            $cart->save();
            return $cart;
        }
        return null;
    }

    public static function updateCherryTryForGameToday($user_id) {
        $check = self::getRaffleCherryTryToday($user_id);
        if ($check == 0) self::updateRaffleCherryTry($user_id, 'update');
    }

    public static function updateRaffleGuest($user_id, $refer_id, $act) {

        StartCommandController::send_hello($user_id);

        if (BotRaffleUsersHistory::where('guest_user_id', $user_id)->count() == 0 && $user_id != $refer_id) {

            $date_z = date("Y-m-d H:i:s");

            $raffle_try_guest = BotRaffleUsers::where('user_id', $refer_id)->first()['raffle_try_guest'];
            $raffle_try_guest = $raffle_try_guest + 1;
            BotRaffleUsers::where('user_id', $refer_id)->update(['raffle_try_guest' => $raffle_try_guest]);

            $raffle_history = new BotRaffleUsersHistory;
            $raffle_history->user_id = $refer_id;
            $raffle_history->guest_user_id = $user_id;
            $raffle_history->raffle_try = $raffle_try_guest;
            $raffle_history->act = $act;
            $raffle_history->date_reg = $date_z;
            $raffle_history->save();

            RaffleCommandController::add_guest($refer_id);
            CashBackController::addCashbackReferral($refer_id, $user_id);

        }

//        if (BotRaffleUsersHistory::where('user_id', $user_id)->count() == 0) {
//
//        }

        //        if ($act == 'share') {
//            StartCommandController::send_hello($user_id);
//        }
//        elseif ($act == 'raffle') {
//            RaffleCommandController::execute($user_id, 'send', null);
//        }

    }

    public static function newTestGame($user_id)
    {

        $arr_pizza = [];
        $i = 1;

        while ($i <= 6) {

            $rand = rand(1,12);
            if (!array_key_exists($rand, $arr_pizza)) {
                $arr_pizza[$rand] = 1;
                $i++;
            }

        }

        $date_z = date("Y-m-d H:i:s");

        $raffle_try = 0;

        $raffle = new BotRaffleCherryTest;
        $raffle->user_id = $user_id;
        $raffle->raffle_try = $raffle_try;

        for ($i = 1; $i <= 12; $i++) {
            $arr_pizza[$i] = isset($arr_pizza[$i]) && $arr_pizza[$i] !== null && $arr_pizza[$i] === 1 ? '🍒' : '😐';
            $p = 'p'.$i;
            $raffle->$p = $arr_pizza[$i];
        }

        $raffle->date_reg = $date_z;
        $raffle->date_edit = $date_z;

        if ($raffle->save()) return $raffle->id;
        else return null;

    }

    public static function updateTestRaffleMessageId($user_id, $raffle_id, $message_id) {

        return BotRaffleCherryTest::where('id', $raffle_id)->where('user_id', $user_id)->update(['message_id' => $message_id]);

    }

    public static function selectTestBlock($user_id, $raffle_id, $p) {

        $raffle_p = BotRaffleCherryTest::where('id', $raffle_id)->where('user_id', $user_id)->first();
        $raffle_try = $raffle_p['raffle_try'];

        $guessed = 0;
        for ($i = 1; $i <= 12; $i++) {
            if ($raffle_p['p'.$i] == '___🍒') $guessed++;
        }

        $block = stripos($raffle_p['p'.$p], '___') !== false ? 1 : 0;
        if ($block == 0) {

            if ($raffle_try < 8){

                $raffle_try++;
                BotRaffleCherryTest::where('id', $raffle_id)->where('user_id', $user_id)->update(['p'.$p => '___'.$raffle_p['p'.$p], 'raffle_try' => $raffle_try]);
                if ($raffle_p['p'.$p] == '🍒') $guessed++;
                return [$guessed, $raffle_try, $raffle_p['p'.$p]];

            }
            else return null;

        }
        else {

            return [$guessed, $raffle_try, $raffle_p['p'.$p]];

        }

    }

    public static function get_image ($n) {

        $image = BotSettingsRaffleCherryImages::where('n', $n)->first()->image;
        Log::info($image);
        return $image;

    }

    public static function clearWinByUserId($user_id)
    {
        BotRaffleCherryTakeaway::where('user_id', $user_id)->update(['received' => 1]);
        BotRaffleUsers::where('user_id', $user_id)->update(['win_cherry' => 0]);
        BotCartNew::where('user_id', $user_id)->where('product_cherry', 1)->delete();
    }

    public static function clearWinByUserIdAndTAkeawayId($user_id, $takeaway_id)
    {
        BotRaffleCherryTakeaway::where('id', $takeaway_id)->where('user_id', $user_id)->update(['received' => 1]);
        BotRaffleUsers::where('user_id', $user_id)->update(['win_cherry' => 0]);
        BotCartNew::where('user_id', $user_id)->where('product_cherry', 1)->delete();
    }

    public static function checkAndAddProductToCartByUserId($user_id)
    {
        $check = BotRaffleCherryTakeaway::countUnReceivedAndWinByUserId($user_id);
        if ($check > 0) {
            if (BotCartNew::countCherryInCartByUserId($user_id) == 0) {
                return self::addActionProductToCart($user_id, 549);
            }
            return null;
        }
        return null;
    }

}
