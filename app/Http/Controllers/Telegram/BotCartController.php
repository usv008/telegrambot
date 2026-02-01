<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotCart;
use App\Models\BotCartNew;
use App\Models\BotOrder;
use App\Models\BotOrderContent;
use App\Models\BotOrders;
use App\Models\BotPizzaNoBortiks;
use App\Models\BotSettings;
use App\Models\BotSettingsDelivery;
use App\Models\BotUser;
use App\Models\BotUsers;
use App\Models\BotUsersNav;
use App\Models\Simpla_Categories;
use App\Models\Simpla_Complect_Products;
use App\Models\Simpla_Products;
use App\Models\Simpla_Variants;
use Illuminate\Http\Request as LRequest;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Request;
use App\Http\Controllers\Controller;
use App\Models\BotSettingsButtons;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use PHPUnit\ExampleExtension\Comparable;

class BotCartController extends Controller
{

    public static function findProductInCart($user_id, $product_id, $variant_id)
    {

        $cart = BotCart::where('id_user', $user_id)->where('id_tovar', $product_id)->where('id_size', $variant_id)->where('action_pizza', 0)->where('product_present', 0)->first();

        if ($cart !== null) return $cart;
        else return null;

    }

    public static function getBortikProductInCart($user_id, $parent_id)
    {

        $cart = BotCart::where('id_user', $user_id)->where('parent_product_id', $parent_id)->get();

        if ($cart !== null) return $cart;
        else return null;

    }

    public static function findBortikProductInCart($user_id, $parent_id, $product_id, $variant_id)
    {

        $cart = BotCart::where('id_user', $user_id)->where('parent_product_id', $parent_id)->where('id_tovar', $product_id)->where('id_size', $variant_id)->first();

        if ($cart !== null) return $cart;
        else return null;

    }

    public static function findBortikInCart($user_id, $parent_product_id, $bortik_product_id, $variant_id)
    {

        $cart = BotCart::where('id_user', $user_id)->where('parent_product_id', $parent_product_id)->where('id_tovar', $bortik_product_id)->where('id_size', $variant_id)->first();

        if ($cart !== null) return $cart;
        else return null;

    }

    public static function findBortiksInCartFromProductCardId($user_id, $parent_product_id)
    {

        $cart = BotCart::where('id_user', $user_id)->where('parent_product_id', $parent_product_id)->get();

        if ($cart !== null) return $cart;
        else return null;

    }

    public static function findBortiksInOrderFromProductCardId($user_id, $parent_product_id)
    {

        $cart = BotOrderContent::where('user_id', $user_id)->where('parent_product_id', $parent_product_id)->get();

        if ($cart !== null) return $cart;
        else return null;

    }

    public static function addToCart($user_id, $product_id, $variant_id, $message_id, $mc)
    {

        $date_z = date("Y-m-d H:i:s");

        $cart_old = self::findProductInCart($user_id, $product_id, $variant_id);
        $variant = self::getProductVariant($variant_id);
        $product = BotMenuController::get_product_sql($user_id, $product_id);

        if ($product['visible'] == 1) {

            $quantity = $cart_old['quantity'] == null ? 1 : $cart_old['quantity'];
            $price = $variant['price'];
            $price_all = round($price * $quantity, 2);

            if ($cart_old !== null) {

                $quantity = $cart_old['quantity'] + 1;
                $price = $variant['price'];
                $price_all = round($price * $quantity, 2);
                BotCart::where('id_user', $user_id)->where('id_tovar', $product_id)->where('id_size', $variant_id)->where('action_pizza', 0)->where('product_present', 0)->update(['quantity' => $quantity, 'price' => $price, 'price_all' => $price_all, 'date_edit' => $date_z]);

            } else {

                $cart = new BotCart;
                $cart->id_user = $user_id;
                $cart->category = $product['url'];
                $cart->id_tovar = $product_id;
                $cart->id_size = $variant_id;
                $cart->product_name = $product['name'];
                $cart->quantity = 1;
                $cart->price = $variant['price'];
                $cart->price_all = $variant['price'];
                $cart->date_reg = $date_z;
                $cart->date_edit = $date_z;
                $cart->save();

            }

        }

        BotCashbackController::clearCashback($user_id);

        if ($mc == 'menu' || $mc == 'mailing') {
            self::updateMessageProduct($user_id, $product_id, null, $message_id, $mc);
        }
        elseif ($mc == 'cart') {
            self::updateMessageProduct($user_id, $product_id, $variant_id, $message_id, $mc);
//            self::edit_cart_product($user_id, $cart_old['id'], $message_id);
        }
        CartCommandController::execute($user_id, 'edit', null);

//         $data_t = ['chat_id' => $user_id];
//         $data_t['text'] = 'debug: '.$message_id;
//         $send_t = Request::sendMessage($data_t);

    }

    public static function removeFromCart($user_id, $product_id, $variant_id, $message_id, $mc)
    {

        $date_z = date("Y-m-d H:i:s");

        $cart_old = self::findProductInCart($user_id, $product_id, $variant_id);
        $variant = self::getProductVariant($variant_id);
        $product = BotMenuController::get_product_sql($user_id, $product_id);

        $quantity = $cart_old['quantity'] == null ? 1 : $cart_old['quantity'];
        $price = $variant['price'];
        $price_all = round($price * $quantity, 2);

        if ($cart_old !== null) {

            $quantity = $cart_old['quantity'] - 1;
            $price = $variant['price'];
            $price_all = round($price * $quantity, 2);
            $cart_id = self::getProductInCartFromProductIdAndVariantId($user_id, $product_id, $variant_id)['id'];
            if ($quantity <= 0) {
                BotCart::where('id_user', $user_id)->where('parent_product_id', $cart_id)->where('action_pizza', 0)->where('product_present', 0)->delete();
                BotCart::where('id_user', $user_id)->where('id_tovar', $product_id)->where('id_size', $variant_id)->where('action_pizza', 0)->where('product_present', 0)->delete();
            } else {
                BotCart::where('id_user', $user_id)->where('id_tovar', $product_id)->where('id_size', $variant_id)->where('action_pizza', 0)->where('product_present', 0)->update(['quantity' => $quantity, 'price' => $price, 'price_all' => $price_all, 'date_edit' => $date_z]);

            }

        }

        BotCashbackController::clearCashback($user_id);

        if ($mc == 'menu') {
            self::updateMessageProduct($user_id, $product_id, null, $message_id, $mc);
        }
        elseif ($mc == 'cart') {
            self::updateMessageProduct($user_id, $product_id, $variant_id, $message_id, $mc);
//            self::edit_cart_product($user_id, $cart_old['id'], $message_id);
        }
        CartCommandController::execute($user_id, 'edit', null);

    }

    public static function updateMessageProduct($user_id, $product_id, $variant_id, $message_id, $act)
    {

        $inline_keyboard = new InlineKeyboard([]);
        if ($act == 'menu' || $act == 'mailing') {
            $inline_keyboard = BotMenuButtonsController::get_buttons_buy($user_id, $product_id);
        }
        elseif ($act == 'cart') {
            $inline_keyboard = BotMenuButtonsController::get_buttons_edit_buy($user_id, $product_id, $variant_id);
        }
        $inline_keyboard = BotMenuButtonsController::get_other_buttons($user_id, $inline_keyboard);

        $product = BotMenuController::get_product_sql($user_id, $product_id);
        $text = BotTextsController::getText($user_id, 'Menu', 'ask_product_add');
        $ins = $act == 'cart' ? ' ('.BotCartController::getProductVariant($variant_id)['name'].')' : '';
        $text = str_replace("___PRODUCT_NAME___", '<b>'.$product['name'].$ins.'</b>', $text);

        $text_sum = BotTextsController::getText($user_id, 'Cart', 'sum_order');
        $sum = BotCartController::count_sum($user_id);
        $currency = BotTextsController::getText($user_id, 'System', 'currency');

        if ($act === 'menu' || $act == 'mailing') {

            if (self::checkProductIdInCart($user_id, $product_id) > 0) {

                $text = BotTextsController::getText($user_id, 'Menu', 'update_message_add').':'.PHP_EOL;
                $cart_products = self::getProductInCartFromProductId($user_id, $product_id);
                $text = MenuCommandController::get_foreach_product_from_id($user_id, $cart_products, $text);
                $text .= '' . $text_sum . ': ' . $sum . ' ' . $currency . '';

            }

        }
        elseif ($act === 'cart') {

            if (self::checkProductAndVariantIdInCart($user_id, $product_id, $variant_id) > 0) {

                $text = BotTextsController::getText($user_id, 'Menu', 'message_edit').':'.PHP_EOL;
                $cart_products = self::getProductAndVariantInCartFromProductId($user_id, $product_id, $variant_id);
                $text = MenuCommandController::get_foreach_product_from_id($user_id, $cart_products, $text);
                $text .= '' . $text_sum . ': ' . $sum . ' ' . $currency . '';

            }

        }

        $data_edit = ['chat_id' => $user_id];
        $data_edit['reply_markup'] = $inline_keyboard;
        $data_edit['text'] = $text;
        $data_edit['parse_mode'] = 'html';

        if ($act == 'mailing') {
            Request::sendMessage($data_edit);
        }
        else {
            $data_edit['message_id'] = $message_id;
            Request::editMessageText($data_edit);
        }


    }

    public static function addBortikToCart($user_id, $product_cart_id, $bortik_id, $bortik_variant_id, $message_id, $mc)
    {

        $date_z = date("Y-m-d H:i:s");

        $cart_old = self::findBortikProductInCart($user_id, $product_cart_id, $bortik_id, $bortik_variant_id);
        $variant = self::getProductVariant($bortik_variant_id);
        $product = BotCartController::getProductInCartFromId($user_id, $product_cart_id);

        $bortik_num = 0;
        $bs_in_cart = BotCartController::findBortiksInCartFromProductCardId($user_id, $product_cart_id);
        foreach ($bs_in_cart as $b_in_cart) {
            $bortik_num += $b_in_cart['quantity'];
        }
        if ($bortik_num >= $product['quantity']) return null;

        $product_bortik = BotMenuController::get_product_sql($user_id, $bortik_id);

        $quantity = $cart_old['quantity'] == null ? 1 : $cart_old['quantity'];
        $price = $variant['price'];
        $price_all = round($price * $quantity, 2);

        if ($cart_old !== null) {

            $quantity = $cart_old['quantity'] + 1;
            $price = $variant['price'];
            $price_all = round($price * $quantity, 2);
            BotCart::where('id_user', $user_id)->where('parent_product_id', $product_cart_id)->where('id_tovar', $bortik_id)->where('id_size', $bortik_variant_id)->update(['quantity' => $quantity, 'price' => $price, 'price_all' => $price_all, 'date_edit' => $date_z]);

        } else {

            $cart = new BotCart;
            $cart->id_user = $user_id;
            $cart->category = $product_bortik['url'];
            $cart->parent_product_id = $product_cart_id;
            $cart->id_tovar = $bortik_id;
            $cart->id_size = $bortik_variant_id;
            $cart->product_name = $product_bortik['name'];
            $cart->quantity = 1;
            $cart->price = $variant['price'];
            $cart->price_all = $variant['price'];
            $cart->date_reg = $date_z;
            $cart->date_edit = $date_z;
            $cart->save();

        }

        BotCashbackController::clearCashback($user_id);

        $product_id = $product['id_tovar'];
        $variant_id = $product['id_size'];

//        if ($mc == 'menu') self::selectBortik($user_id, $product_id, $bortik_id, $message_id);
//        elseif ($mc == 'cart') self::edit_cart_product($user_id, $cart_old['id'], $message_id);

        if ($mc == 'menu') {
            self::updateSelectBortik($user_id, $product_id, null, $bortik_id, $message_id, $mc);
        }
        elseif ($mc == 'cart') {
            self::updateSelectBortik($user_id, $product_id, $variant_id, $bortik_id, $message_id, $mc);
        }
        CartCommandController::execute($user_id, 'edit', null);

    }

    public static function removeBortikFromCart($user_id, $product_cart_id, $bortik_id, $bortik_variant_id, $message_id, $mc)
    {

        $date_z = date("Y-m-d H:i:s");

        $cart_old = self::findBortikProductInCart($user_id, $product_cart_id, $bortik_id, $bortik_variant_id);
        $variant = self::getProductVariant($bortik_variant_id);
        $product = BotCartController::getProductInCartFromId($user_id, $product_cart_id);

        $quantity = $cart_old['quantity'] == null ? 1 : $cart_old['quantity'];
        $price = $variant['price'];
        $price_all = round($price * $quantity, 2);

        if ($cart_old !== null) {

            $quantity = $cart_old['quantity'] - 1;
            $price = $variant['price'];
            $price_all = round($price * $quantity, 2);
            if ($quantity <= 0) {
                BotCart::where('id_user', $user_id)->where('parent_product_id', $product_cart_id)->where('id_tovar', $bortik_id)->where('id_size', $bortik_variant_id)->delete();
            } else BotCart::where('id_user', $user_id)->where('parent_product_id', $product_cart_id)->where('id_tovar', $bortik_id)->where('id_size', $bortik_variant_id)->update(['quantity' => $quantity, 'price' => $price, 'price_all' => $price_all, 'date_edit' => $date_z]);

        }

        BotCashbackController::clearCashback($user_id);

        $product_id = $product['id_tovar'];
        $variant_id = $product['id_size'];

//        if ($mc == 'menu') self::selectBortik($user_id, $product_id, $bortik_id, $message_id);
//        elseif ($mc == 'cart') self::edit_cart_product($user_id, $cart_old['id'], $message_id);

        if ($mc == 'menu') {
            self::updateSelectBortik($user_id, $product_id, null, $bortik_id, $message_id, $mc);
        }
        elseif ($mc == 'cart') {
            self::updateSelectBortik($user_id, $product_id, $variant_id, $bortik_id, $message_id, $mc);
        }
        CartCommandController::execute($user_id, 'edit', null);

    }

    public static function getTextCart($user_id, $act)
    {

        $command = 'Cart';
        $name = 'show_cart';

        $text_cart = BotTextsController::getText($user_id, $command, $name);
        $products = BotCartController::getProductsInCart($user_id);
        $pcs = BotTextsController::getText($user_id, 'System', 'pcs');
        $currency = BotTextsController::getText($user_id, 'System', 'currency');

        $price_all = 0;
        $text = $text_cart . PHP_EOL;
        $i = 0;
        foreach ($products as $product) {

            $i++;
            $price_all += $product['price_all'];
            $variant = BotCartController::getProductVariant($product['id_size']);
//            $price = $product['action_pizza'] == 0 && $product['product_present'] == 0 ? number_format(round($variant['price'], 2), 2) : number_format(0, 2);
            $price = number_format(round($variant['price'], 2), 2);
//            $product_price_all = $product['action_pizza'] == 0 && $product['product_present'] == 0 ? number_format(round($product['quantity'] * $variant['price'], 2), 2) : number_format(0, 2);
            $product_price_all = number_format(round($product['quantity'] * $variant['price'], 2), 2);
//            $text .= PHP_EOL.'<code><b>'.$i.'.</b> '.$product['product_name'].' ('.$variant['name'].') '.PHP_EOL.$price.' x '.$product['quantity'].' = '.$product_price_all.' '.$currency.'</code>'.PHP_EOL;
            $ins = $act == 'edit' ? self::replace_digit($i) : $i.'.';
            $text .= PHP_EOL . '<b>' . $ins . ' ' . $product['product_name'] . ' (' . $variant['name'] . ') </b>' . PHP_EOL . '<code>' . $price . ' × ' . $product['quantity'] . ' = ' . $product_price_all . ' ' . $currency . '</code>' . PHP_EOL;

            $bortiks_in_cart = BotCartController::findBortiksInCartFromProductCardId($user_id, $product['id']);
            foreach ($bortiks_in_cart as $bortik_in_cart) {
                $text .= '  + ' . $bortik_in_cart['product_name'] . ' (' . self::getProductVariant($bortik_in_cart['id_size'])['name'] . ') ' . PHP_EOL . '<code> ' . $bortik_in_cart['price'] . ' × ' . $bortik_in_cart['quantity'] . ' = ' . $bortik_in_cart['price_all'] . ' ' . $currency . '</code>' . PHP_EOL;
            }

        }

//        $cart_products = self::getProductInCartFromProductId($user_id, $product_id);
//        foreach ($cart_products as $cart_product) {
//            $text .= '<pre>' . self::replace_pizza_emoji($cart_product['category']) . $cart_product['product_name'] . ' (' . self::getProductVariant($cart_product['id_size'])['name'] . ') ' . PHP_EOL . $cart_product['price'] . ' x ' . $cart_product['quantity'] . ' = ' . $cart_product['price_all'] . ' ' . $currency . '</pre>' . PHP_EOL;
//            $bortiks_in_cart = BotCartController::findBortiksInCartFromProductCardId($user_id, $cart_product['id']);
//            foreach ($bortiks_in_cart as $bortik_in_cart) {
//                $text .= '<pre>' . self::replace_emoji($bortik_in_cart['product_name']).$bortik_in_cart['product_name'] . ' (' . self::getProductVariant($bortik_in_cart['id_size'])['name'] . ') ' . PHP_EOL . $bortik_in_cart['price'] . ' x ' . $bortik_in_cart['quantity'] . ' = ' . $bortik_in_cart['price_all'] . ' ' . $currency . '</pre>' . PHP_EOL;
//            }
//            $text .= PHP_EOL;
//
//        }

        $text .= PHP_EOL . BotCartController::count_cart_total($user_id, $currency);

        return $text;

    }

    public static function getTextOrder($user_id, $simpla_id, $order_id)
    {

        $command = 'Cart';
        $name = 'show_cart';

        $text_cart = BotTextsController::getText($user_id, 'MyOrders', 'my_orders');
        $products = BotCartController::getProductsInOrder($user_id, $order_id);
        $pcs = BotTextsController::getText($user_id, 'System', 'pcs');
        $currency = BotTextsController::getText($user_id, 'System', 'currency');

        $order = BotOrder::where('id', $order_id)->where('user_id', $user_id)->first();

        $price_all = 0;
        $text = '<b>' . $text_cart . $simpla_id . '</b>' . PHP_EOL;
        $i = 0;
        foreach ($products as $product) {

            if ($product['action_pizza'] == 0 && $product['product_present'] == 0) {

                $i++;
                $variant = BotCartController::getProductVariant($product['id_size']);
                $price = number_format(round($variant['price'], 2), 2);
                $product_price_all = number_format(round($product['quantity'] * $variant['price'], 2), 2);
                $price_all += $product_price_all;
                $ins = $i.'.';
                $text .= PHP_EOL . '<b>' . $ins . ' ' . $product['product_name'] . ' (' . $variant['name'] . ') </b>' . PHP_EOL . '<code>' . $price . ' × ' . $product['quantity'] . ' = ' . $product_price_all . ' ' . $currency . '</code>' . PHP_EOL;

                $bortiks_in_cart = BotCartController::findBortiksInOrderFromProductCardId($user_id, $product['id']);
                foreach ($bortiks_in_cart as $bortik_in_cart) {
                    $variant_bortik = BotCartController::getProductVariant($bortik_in_cart['id_size']);
                    $bortik_price_all = number_format(round($product['quantity'] * $variant_bortik['price'], 2), 2);
                    $price_all += $bortik_price_all;
                    $text .= '  + ' . $bortik_in_cart['product_name'] . ' (' . self::getProductVariant($bortik_in_cart['id_size'])['name'] . ') ' . PHP_EOL . '<code> ' . number_format(round($variant_bortik['price'], 2), 2) . ' × ' . $bortik_in_cart['quantity'] . ' = ' . $bortik_price_all . ' ' . $currency . '</code>' . PHP_EOL;
                }

            }

        }

        $text_sum = BotTextsController::getText($user_id, $command, 'sum_order');
        $text .= PHP_EOL . '' . $text_sum . ': ' . $price_all . ' ' . $currency . '' . PHP_EOL;

        $addr = $order['order_addr'];
        if ($addr !== '') {

            $text_addr = BotTextsController::getText($user_id, 'Order', 'order_addr');
            $text .= '' . $text_addr . '' . $addr . '' . PHP_EOL;

        }

        return $text;

    }

    public static function product_delete($user_id, $id, $message_id)
    {

        $data_edit = ['chat_id' => $user_id];
        $data_edit['reply_markup'] = BotCartButtonsController::get_buttons_product_delete($user_id, $id);
        $data_edit['message_id'] = $message_id;
        Request::editMessageReplyMarkup($data_edit);

    }

    public static function product_delete_yes($user_id, $id, $message_id)
    {

        BotCart::where('id_user', $user_id)->where('id', $id)->delete();

        $data_edit = ['chat_id' => $user_id];
        $data_edit['message_id'] = $message_id;
        Request::deleteMessage($data_edit);

        $currency = BotTextsController::getText($user_id, 'System', 'currency');
        self::show_cart_total($user_id, $currency, BotUsersNavController::getCartMessageId($user_id), 'edit');

    }

    public static function showBortiks($user_id, $product_id, $variant_id, $message_id, $act)
    {

        $product = BotMenuController::get_product_sql($user_id, $product_id);
        $text = BotTextsController::getText($user_id, 'Menu', 'ask_product_add');
        $text = str_replace("___PRODUCT_NAME___", '<b>' . self::replace_emoji($product['name']).$product['name'] . '</b>', $text);

        if ($act == 'menu') {

            if (BotCartController::checkProductIdInCart($user_id, $product_id) > 0) {

                $text = BotTextsController::getText($user_id, 'Menu', 'show_bortiks') . ':' . PHP_EOL;
                $cart_products = self::getProductInCartFromProductId($user_id, $product_id);
                $text = MenuCommandController::get_foreach_product_from_id($user_id, $cart_products, $text);
                $text .= '<b>' . BotTextsController::getText($user_id, 'Menu', 'show_bortiks2') . ':</b>';

            }

        }
        elseif ($act == 'cart') {

            if (BotCartController::checkProductAndVariantIdInCart($user_id, $product_id, $variant_id) > 0) {

                $text = BotTextsController::getText($user_id, 'Cart', 'cart_product_edit_text') . ':' . PHP_EOL;
                $cart_products = self::getProductAndVariantInCartFromProductId($user_id, $product_id, $variant_id);
                $text = MenuCommandController::get_foreach_product_from_id($user_id, $cart_products, $text);
                $text .= '<b>' . BotTextsController::getText($user_id, 'Menu', 'show_bortiks2') . ':</b>';

            }

        }

        $data_edit = ['chat_id' => $user_id];
        $data_edit['reply_markup'] = BotMenuButtonsController::get_buttons_bortik($user_id, $product_id, $variant_id, $act);
        $data_edit['text'] = $text;
        $data_edit['parse_mode'] = 'html';
        $data_edit['message_id'] = $message_id;
        Request::editMessageText($data_edit);

    }

    public static function getArrNoBortiks() {

        $arr = [];
        $no_bortiks = BotPizzaNoBortiks::all();
        foreach ($no_bortiks as $no_bortik) {
            $arr[] = $no_bortik['product_id'];
        }

        return $arr;

    }

    public static function selectBortik($user_id, $product_id, $variant_id, $bortik_product_id, $message_id, $act)
    {

        self::updateSelectBortik($user_id, $product_id, $variant_id, $bortik_product_id, $message_id, $act);

//        if ($act == 'menu') {

            $num = self::countProductInCartFromProductId($user_id, $product_id);
            if ($num == 1) {

                $cart_product = self::getFirstProductInCartFromProductId($user_id, $product_id);
                $bortiks = BotCartController::getBortiksFromProductId($user_id, $bortik_product_id);
                foreach ($bortiks as $bortik) {

                    $ckeck_products = BotCartController::checkProductInCartFromProductIdLikeVariant($user_id, $product_id, $bortik['variant_name']);

                    if ($ckeck_products > 0) {

                        $product_in_cart = BotCartController::getProductInCartFromProductIdLikeVariant($user_id, $product_id, $bortik['variant_name']);
                        $bortik_in_cart = BotCartController::findBortikInCart($user_id, $product_in_cart['id'], $bortik['id'], $bortik['variant_id']);
                        $bortik_num = 0;
                        $bs_in_cart = BotCartController::findBortiksInCartFromProductCardId($user_id, $product_in_cart['id']);
                        foreach ($bs_in_cart as $b_in_cart) {
                            $bortik_num += $b_in_cart['quantity'];
                        }

                        if ($bortik_num < $product_in_cart['quantity']) {

                            self::addBortikToCart($user_id, $cart_product['id'], $bortik_product_id, $bortik['variant_id'], $message_id, 'menu');

                        }

                    }

                }


            }

//        }

    }

    public static function updateSelectBortik($user_id, $product_id, $variant_id, $bortik_product_id, $message_id, $act)
    {

        $product = BotMenuController::get_product_sql($user_id, $product_id);
        $text = BotTextsController::getText($user_id, 'Menu', 'ask_product_add');
        $text = str_replace("___PRODUCT_NAME___", self::replace_emoji($product['name']).'<b>'.$product['name'] . '</b>', $text);

        $text_sum = BotTextsController::getText($user_id, 'Cart', 'sum_order');
        $sum = BotCartController::count_sum($user_id);
        $currency = BotTextsController::getText($user_id, 'System', 'currency');

        if ($act == 'menu') {

            if (BotCartController::checkProductIdInCart($user_id, $product_id) > 0) {

                $text = BotTextsController::getText($user_id, 'Menu', 'show_bortiks') . ':' . PHP_EOL;
                $cart_products = self::getProductInCartFromProductId($user_id, $product_id);
                $text = MenuCommandController::get_foreach_product_from_id($user_id, $cart_products, $text);

                $text .= '' . $text_sum . ': ' . $sum . ' ' . $currency . '' . PHP_EOL;
                $text .= '' . BotTextsController::getText($user_id, 'Menu', 'select_bortik') . ' ';
                $text .= '<b>' . self::getBortikFromProductId($user_id, $bortik_product_id)['name'] . '</b>' . PHP_EOL;
                $text .= self::countBortiksInCartFromProductId($user_id, $product_id, $bortik_product_id) == 0 ? '' . BotTextsController::getText($user_id, 'Menu', 'select_bortik2') . '' : '';

            }

        }
        elseif ($act == 'cart') {

            if (BotCartController::checkProductAndVariantIdInCart($user_id, $product_id, $variant_id) > 0) {

                $text = BotTextsController::getText($user_id, 'Menu', 'show_bortiks') . ':' . PHP_EOL;
                $cart_products = self::getProductAndVariantInCartFromProductId($user_id, $product_id, $variant_id);
                $text = MenuCommandController::get_foreach_product_from_id($user_id, $cart_products, $text);

                $text .= '' . $text_sum . ': ' . $sum . ' ' . $currency . '' . PHP_EOL;
                $text .= '' . BotTextsController::getText($user_id, 'Menu', 'select_bortik') . ' ';
                $text .= '<b>' . self::getBortikFromProductId($user_id, $bortik_product_id)['name'] . '</b>' . PHP_EOL;
                $text .= self::countBortiksInCartFromProductId($user_id, $product_id, $bortik_product_id) == 0 ? '' . BotTextsController::getText($user_id, 'Menu', 'select_bortik2') . '' : '';

            }

        }

        $data_edit = ['chat_id' => $user_id];
        $data_edit['reply_markup'] = BotMenuButtonsController::get_buttons_select_bortik($user_id, $product_id, $variant_id, $bortik_product_id, $act);
        $data_edit['text'] = $text;
        $data_edit['parse_mode'] = 'html';
        $data_edit['message_id'] = $message_id;
        Request::editMessageText($data_edit);

    }

    public static function getProductVariant($variant_id)
    {

        return Simpla_Variants::find($variant_id);

    }

    public static function getProductVariants($product_id)
    {

        return Simpla_Variants::where('product_id', $product_id)->get();

    }

    public static function getMaxSumFromVariantFromProductId($product_id)
    {

        $sum = 0;
        $name = '';
        $stock = 0;
        $variants = self::getProductVariants($product_id);
        foreach ($variants as $variant) {
            if ($variant['price'] > $sum) {
                $sum =  $variant['price'];
                $name = $variant['name'];
                $stock = $variant['stock'];
            }
        }

        return [$sum, $name, $stock];

    }

    public static function getBortiksFromProductId($user_id, $bortik_product_id)
    {

        $lang = BotUserSettingsController::getLang($user_id);
        $text_lang = '_'.$lang;
        return Simpla_Categories::leftJoin('s_products_categories', 's_products_categories.category_id', 's_categories.id')
            ->leftJoin('s_products', 's_products.id', 's_products_categories.product_id')
            ->leftJoin('s_variants', 's_variants.product_id', 's_products.id')
            ->where('s_categories.id', 50)
            ->where('s_products.visible', 1)
            ->where('s_products.id', $bortik_product_id)
            ->orderBy('s_variants.name', 'desc')
            ->get(['s_products.id', 's_variants.id as variant_id', 's_products.name'.$text_lang.' as name', 's_variants.name as variant_name', 's_variants.price']);

    }

    public static function getBortikFromProductId($user_id, $product_id)
    {

        $lang = BotUserSettingsController::getLang($user_id);
        $text_lang = '_'.$lang;
        return Simpla_Products::where('s_products.id', $product_id)
            ->first(['s_products.id', 's_products.name'.$text_lang.' as name']);

    }

    public static function getDistinctBortiks($user_id)
    {

        $lang = BotUserSettingsController::getLang($user_id);
        $text_lang = '_'.$lang;
        return Simpla_Categories::leftJoin('s_products_categories', 's_products_categories.category_id', 's_categories.id')
            ->leftJoin('s_products', 's_products.id', 's_products_categories.product_id')
            ->leftJoin('s_variants', 's_variants.product_id', 's_products.id')
            ->where('s_categories.id', 50)
            ->where('s_products.visible', 1)
            ->distinct('s_products.name')
            ->groupBy('s_products.name')
            ->get(['s_products.id', 's_products.name'.$text_lang.' as name']);

    }

    public static function getComplectProduct($user_id, $id)
    {

        $lang = BotUserSettingsController::getLang($user_id);
        $text_lang = '_'.$lang;
        $product_id = self::getProductInCartFromId($user_id, $id)['id_tovar'];
        return Simpla_Complect_Products::leftJoin('s_variants', 's_variants.id', 's_complect_products.complect_variant_id')
            ->leftJoin('s_products', 's_products.id', 's_variants.product_id')
            ->where('s_complect_products.product_id', $product_id)
            ->get(['s_products.name'.$text_lang.' as name']);

    }

    public static function getComplectProductCount($product_id)
    {

        return Simpla_Complect_Products::leftJoin('s_variants', 's_variants.id', 's_complect_products.complect_variant_id')
            ->leftJoin('s_products', 's_products.id', 's_variants.product_id')
            ->where('s_complect_products.product_id', $product_id)
            ->count();

    }

    public static function checkProductsInCart($user_id)
    {

        return BotCart::where('id_user', $user_id)->count();

    }

    public static function checkProductIdInCart($user_id, $product_id)
    {

        return BotCart::where('id_user', $user_id)->where('id_tovar', $product_id)->count();

    }

    public static function checkProductAndVariantIdInCart($user_id, $product_id, $variant_id)
    {

        return BotCart::where('id_user', $user_id)->where('id_tovar', $product_id)->where('id_size', $variant_id)->count();

    }

    public static function getProductsInCart($user_id)
    {

        return BotCart::where('id_user', $user_id)->where('parent_product_id', null)->orderBy('id', 'asc')->get();

    }

    public static function checkProductsSushiInCart($user_id)
    {

        return BotCart::where('id_user', $user_id)
            ->where(function($query) {
                $query->where('category', 'sushi')
                    ->orWhere('category', 'sushi-i-sety')
                    ->orWhere('category', 'roli')
                    ->orWhere('category', 'sushki')
                    ->orWhere('category', 'sety');
            })
            ->count();

    }

    public static function getProductsInOrder($user_id, $order_id)
    {

        return BotOrderContent::where('user_id', $user_id)->where('order_id', $order_id)->where('parent_product_id', null)->orderBy('id', 'asc')->get();

    }

    public static function getAllProductsInCart($user_id)
    {

        return BotCart::where('id_user', $user_id)->orderBy('date_edit', 'asc')->get();

    }

    public static function countProductsInCart($user_id)
    {

        return BotCart::where('id_user', $user_id)->count();

    }

    public static function countProductInCartFromId($user_id, $id)
    {

        return BotCart::where('id_user', $user_id)->where('id', $id)->count();

    }

    public static function getProductInCartFromId($user_id, $id)
    {

        return BotCart::where('id_user', $user_id)->where('id', $id)->first();

    }

    public static function countProductInCartFromProductId($user_id, $product_id)
    {

        return BotCart::where('id_user', $user_id)->where('id_tovar', $product_id)->count();

    }

    public static function getProductInCartFromProductId($user_id, $product_id)
    {

        return BotCart::where('id_user', $user_id)->where('id_tovar', $product_id)->get();

    }

    public static function getFirstProductInCartFromProductId($user_id, $product_id)
    {

        return BotCart::where('id_user', $user_id)->where('id_tovar', $product_id)->first();

    }

    public static function getProductAndVariantInCartFromProductId($user_id, $product_id, $variant_id)
    {

        return BotCart::where('id_user', $user_id)->where('id_tovar', $product_id)->where('id_size', $variant_id)->get();

    }

    public static function getProductInCartFromProductIdAndVariantId($user_id, $product_id, $variant_id)
    {

        return BotCart::where('id_user', $user_id)->where('id_tovar', $product_id)->where('id_size', $variant_id)->first();

    }

    public static function checkProductInCartFromProductIdLikeVariant($user_id, $product_id, $variant_name)
    {

        return BotCart::join('s_variants', 's_variants.id', 'bot_cart.id_size')
            ->where('bot_cart.id_user', $user_id)
            ->where('bot_cart.id_tovar', $product_id)
            ->where('s_variants.name', 'like', '%' . $variant_name . '%')
            ->count();

    }

    public static function checkProductInCartFromProductAndVariantIdLikeVariant($user_id, $product_id, $variant_id, $variant_name)
    {

        return BotCart::join('s_variants', 's_variants.id', 'bot_cart.id_size')
            ->where('bot_cart.id_user', $user_id)
            ->where('bot_cart.id_tovar', $product_id)
            ->where('bot_cart.id_size', $variant_id)
            ->where('s_variants.name', 'like', '%' . $variant_name . '%')
            ->count();

    }

    public static function getProductInCartFromProductIdLikeVariant($user_id, $product_id, $variant_name)
    {

        return BotCart::join('s_variants', 's_variants.id', 'bot_cart.id_size')
            ->where('bot_cart.id_user', $user_id)
            ->where('bot_cart.id_tovar', $product_id)
            ->where('s_variants.name', 'like', '%' . $variant_name . '%')
            ->first(['bot_cart.id', 'bot_cart.id_tovar', 'bot_cart.id_size', 'bot_cart.quantity']);

    }

    public static function countBortiksFromCartId($user_id, $cart_id)
    {

        return BotCart::where('bot_cart.id_user', $user_id)
            ->where('bot_cart.parent_product_id', $cart_id)
            ->count();

    }

    public static function countBortiksInCartFromProductId($user_id, $product_id, $bortik_product_id) {

        $num = 0;
        $products = BotCart::where('id_user', $user_id)->where('id_tovar', $product_id)->get();
        foreach ($products as $product) {

            $bortiks = BotCart::where('id_user', $user_id)->where('parent_product_id', $product['id'])->where('id_tovar', $bortik_product_id)->get();
            foreach ($bortiks as $bortik) {

                $num += $bortik['quantity'];

            }

        }
        return $num;

    }
//    public static function edit_cart_products($user_id)
//    {
//
//        $currency = BotTextsController::getText($user_id, 'System', 'currency');
//
//        $products = self::getProductsInCart($user_id);
//        foreach ($products as $product) {
//
//            self::show_cart_product($user_id, $product, $currency, 'null', 'send');
//
//        }
//
//    }

    public static function edit_cart_product($user_id, $cart_product_id, $message_id)
    {

        if (self::countProductInCartFromId($user_id, $cart_product_id) > 0) {

            $product = self::getProductInCartFromId($user_id, $cart_product_id);
            $variant = BotCartController::getProductVariant($product['id_size']);
            $currency = BotTextsController::getText($user_id, 'System', 'currency');

            $price = number_format(round($variant['price'], 2), 2);
            $product_price_all = number_format(round($product['quantity'] * $variant['price'], 2), 2);

            $text = self::getTextCart($user_id, 'edit');
            $text .= PHP_EOL . PHP_EOL . '<b>' . BotTextsController::getText($user_id, 'Cart', 'cart_product_edit_text') . ':</b>';
            $text .= PHP_EOL . '' . $product['product_name'] . ' (' . $variant['name'] . ') ' . PHP_EOL.'<pre>' . $price . ' x ' . $product['quantity'] . ' = ' . $product_price_all . ' ' . $currency . '</pre>' . PHP_EOL;

            $data_edit = ['chat_id' => $user_id];
            $data_edit['reply_markup'] = BotCartButtonsController::get_button_cart_product_edit($user_id, $product['id_tovar'], $product['id_size']);
            $data_edit['text'] = $text;
            $data_edit['parse_mode'] = 'html';
            $data_edit['message_id'] = $message_id;
//        Request::editMessageReplyMarkup($data_edit);
            Request::editMessageText($data_edit);

        }
        else CartCommandController::execute($user_id, 'edit', $message_id);

    }

    public static function show_cart_product($user_id, $product, $currency, $message_id, $act)
    {

        $variant = BotCartController::getProductVariant($product['id_size']);
        $text = '<pre>' . $product['product_name'] . ' (' . $variant['name'] . ') ' . PHP_EOL . $product['price'] . ' x ' . $product['quantity'] . ' = ' . $product['price_all'] . ' ' . $currency . '</pre>';

        $inline_keyboard = BotCartButtonsController::get_button_edit($user_id, $product['id']);

        $data_edit = ['chat_id' => $user_id];
        $data_edit['reply_markup'] = $inline_keyboard;
        $data_edit['text'] = $text;
        $data_edit['parse_mode'] = 'html';

        if ($act == 'send') {
            Request::sendMessage($data_edit);
        } elseif ($act == 'edit') {
            $data_edit['message_id'] = $message_id;
            Request::editMessageText($data_edit);
        }

    }

    public static function count_cart_total($user_id, $currency)
    {

        $command = 'Cart';
        $text = '';
        $text_sum = BotTextsController::getText($user_id, $command, 'sum_order');
        $text_birthday = BotTextsController::getText($user_id, $command, 'birthday');
        $text_total = BotTextsController::getText($user_id, $command, 'total');
        $sum = BotCartController::count_sum($user_id);
        $total = BotCartController::count_sum_total($user_id);

        $text_delivery_type = BotTextsController::getText($user_id, $command, 'delivery_type');
        list($check_delivery_type_selected, $delivery_type) = self::get_selected_delivery_type($user_id);

        $birthday_sum = self::count_birthday($user_id);
        $text_delivery = BotTextsController::getText($user_id, $command, 'delivery');
        $delivery = BotCartController::count_delivery($user_id, $sum - $birthday_sum);

        $text_cashback = BotTextsController::getText($user_id, $command, 'cashback');
        $cashback_pay = BotUsersController::getValueFromUsers($user_id, 'cashback_pay');
        $cashback = BotUsersController::getValueFromUsers($user_id, 'cashback_summa');

        $text .= '' . $text_sum . ': ' . $sum . ' ' . $currency . '' . PHP_EOL;

        if ($check_delivery_type_selected == 1) {

            $text .= '' . $text_delivery_type . ': ' . $delivery_type . ' ' . PHP_EOL;
            $text .= '' . $text_delivery . ': ' . $delivery . ' ' . $currency . '' . PHP_EOL;

            $text .= BotUsersNavController::getValue($user_id, 'birthday') == 1 ? '' . $text_birthday . ': -' . self::count_birthday($user_id) . ' ' . $currency . '' . PHP_EOL : '';
            $text .= $cashback_pay == 1 ? '' . $text_cashback . ': ' . $cashback . ' ' . $currency . '' . PHP_EOL : '';

            $text .= PHP_EOL;
            $text .= '<b>' . $text_total . ': ' . $total . ' ' . $currency . '</b>';

        }

        return $text;
    }

    public static function show_cart_total($user_id, $currency, $message_id, $act)
    {

        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = self::count_cart_total($user_id, $currency);
        $data_text['parse_mode'] = 'html';
        $data_text['reply_markup'] = BotCartButtonsController::get_buttons_cart_total($user_id);

        if ($act == 'send') {
            $send_text = Request::sendMessage($data_text);
            if ($send_text->getResult() !== null) {
                $message_id = $send_text->getResult()->getMessageId();

                $message_old_id = BotUsersNavController::getCartMessageId($user_id);
                if ($message_old_id !== null) {
                    $data_delete = ['chat_id' => $user_id];
                    $data_delete['message_id'] = $message_old_id;
                    Request::deleteMessage($data_delete);
                }
                BotUsersNavController::updateCartMessageId($user_id, $message_id);
            }
        } elseif ($act == 'edit') {
            $data_text['message_id'] = $message_id;
            Request::editMessageText($data_text);
        }

    }

    public static function edit_cart($user_id, $message_id, $n)
    {

        $text = self::getTextCart($user_id, 'edit');
        $text .= PHP_EOL . PHP_EOL . '<b>' . BotTextsController::getText($user_id, 'Cart', 'before_edit_text') . '</b>';

        $data_edit = ['chat_id' => $user_id];
        $data_edit['reply_markup'] = BotCartButtonsController::get_buttons_edit($user_id);
        $data_edit['text'] = $text;
        $data_edit['parse_mode'] = 'html';
        $data_edit['message_id'] = $message_id;
//        Request::editMessageReplyMarkup($data_edit);
        Request::editMessageText($data_edit);

    }

    public static function get_delivery_type($user_id)
    {

        $city_id = BotUser::getValue($user_id, 'city_id');
        $city_id = $city_id !== null && is_numeric($city_id) ? $city_id : 6;

        return BotSettingsDelivery::where('region_id', $city_id)->where('enabled', 1)->orderBy('position', 'asc')->get();

    }

    public static function get_selected_delivery_type($user_id)
    {

        $delivery_id = BotUsersNavController::get_delivery_from_user_id($user_id)['delivery_id'];

        $lang = BotUserSettingsController::getLang($user_id);
        $text_lang = 'text_value_'.$lang;

        return $delivery_id !== null && $delivery_id > 0 && is_int($delivery_id) ? [1, BotSettingsDelivery::where('id', $delivery_id)->first()->$text_lang] : [null, BotSettingsDelivery::where('id', 1)->first()->$text_lang];

    }

    public static function delivery_edit($user_id, $message_id)
    {

        $text = self::getTextCart($user_id, null);
        $text .= PHP_EOL . PHP_EOL . '<b>' . BotTextsController::getText($user_id, 'Cart', 'delivery_edit') . '</b>';

        $data_edit = ['chat_id' => $user_id];
        $data_edit['reply_markup'] = BotCartButtonsController::get_buttons_delivery_edit($user_id);
        $data_edit['text'] = $text;
        $data_edit['parse_mode'] = 'html';
        $data_edit['message_id'] = $message_id;
        Request::editMessageText($data_edit);

    }

    public static function clear_cart($user_id)
    {

        $cart = BotCart::where('id_user', $user_id)->where('action_pizza', 0)->where('product_present', 0)->delete();

    }

    public static function clear_cart_all($user_id)
    {

        $cart = BotCart::where('id_user', $user_id)->delete();

    }

    public static function clear_all_cart_without_action_pizza_and_product_present()
    {
        Log::info('-------------------------------- Начинаем очистку корзин всех пользователей -----------------------------------------');
//        $cart = BotCartNew::where('product_action', 0)->where('product_present', 0)->delete();
        $cart = BotCartNew::where('product_action', 0)->delete();
        Log::info('Всего удалено записей: '.$cart);
        Log::info('------------------------------- Закончили очистку корзин всех пользователей -----------------------------------------');

    }

    public static function change_delivery($user_id, $delivery_id, $message_id)
    {

        BotUsersNavController::update_delivery($user_id, $delivery_id);
        BotUsersNavController::updateValue($user_id, 'addr', null);
        BotCashbackController::clearCashback($user_id);
        CartCommandController::execute($user_id, 'edit', $message_id);

    }

    public static function cancel_order($user_id, $message_id)
    {

        $text = self::getTextCart($user_id, null);
        $text .= PHP_EOL . PHP_EOL . '<b>' . BotTextsController::getText($user_id, 'Cart', 'cancel_order') . '</b>';

        $data_edit = ['chat_id' => $user_id];
        $data_edit['reply_markup'] = BotCartButtonsController::get_buttons_cancel_order($user_id);
        $data_edit['text'] = $text;
        $data_edit['parse_mode'] = 'html';
        $data_edit['message_id'] = $message_id;
        Request::editMessageText($data_edit);

    }

    public static function cancel_order_yes($user_id, $message_id)
    {

        self::clear_cart($user_id);
        BotUsersNavController::clear_delivery($user_id);
        BotCashbackController::clearCashback($user_id);
        CartCommandController::execute($user_id, 'edit', $message_id);

    }

    public static function count_sum_products_no_paid($user_id) {

        $cats_pay = [];
        $cats = Simpla_Categories::where('paid_delivery', 1)->get();
        foreach ($cats as $cat) {
            $cats_pay[] = $cat['url'];
        }

        $no_actions = [];
        $products = Simpla_Products::where('no_actions', 1)->get();
        foreach ($products as $product) {
            $no_actions[] = $product['id'];
        }

        $total = 0;
        $products = self::getAllProductsInCart($user_id);
        foreach ($products as $product) {

            $total += !in_array($product->category, $cats_pay) && !in_array($product->id_tovar, $no_actions) ? $product->price_all : 0;

        }

        return $total;

    }

    public static function count_sum_products_no_discounts($user_id) {
        $no_discounts = [];
        $products = Simpla_Products::where('no_discounts', 1)->get();
        foreach ($products as $product) {
            $no_discounts[] = $product['id'];
        }

        $total = 0;
        $products = self::getAllProductsInCart($user_id);
        foreach ($products as $product) {
            $total += !in_array($product->id_tovar, $no_discounts) ? $product->price_all : 0;
        }

        return $total;
    }

    public static function count_sum_products_no_paid_for_cashback($user_id) {

        $cats_pay = [];
        $cats = Simpla_Categories::where('paid_delivery', 1)->get();
        foreach ($cats as $cat) {
            $cats_pay[] = $cat['url'];
        }

        $no_cashback = [];
        $products = Simpla_Products::where('no_cashback', 1)->get();
        foreach ($products as $product) {
            $no_cashback[] = $product['id'];
        }

        $total = 0;
        $products = self::getAllProductsInCart($user_id);
        foreach ($products as $product) {

            $total += !in_array($product->category, $cats_pay) && !in_array($product->id_tovar, $no_cashback) ? $product->price_all : 0;

        }

        return $total;

    }

    public static function count_sum_products_paid($user_id) {

        $cats_pay = [];
        $cats = Simpla_Categories::where('paid_delivery', 1)->get();
        foreach ($cats as $cat) {
            $cats_pay[] = $cat['url'];
        }

        $total = 0;
        $products = self::getAllProductsInCart($user_id);
        foreach ($products as $product) {

            $total += in_array($product->category, $cats_pay) ? $product->price_all : 0;

        }
        return $total;

    }

    public static function count_delivery($user_id, $sum)
    {

        $min_sum_order = (float)BotSettingsController::getSettings($user_id,'min_sum_order')['settings_value'];
        $max_sum_order = (float)BotSettingsController::getSettings($user_id,'max_sum_order')['settings_value'];
        $sum_delivery = (float)BotSettingsController::getSettings($user_id,'sum_delivery')['settings_value'];

        $check_discount = BotSettingsDeliveryController::checkDiscount($user_id);

        if ($check_discount !== null && (int)$check_discount == 1) {

            if (self::count_birthday($user_id) == 0) {
                $sum = self::count_sum_products_no_paid($user_id);
                $settings = BotSettings::where('settings_name', 'discount_delivery')->first();
                $discount = $settings['settings_value'];
                return  -1 * ($sum / 100 * $discount);
            }
            else return 0;

        }
        else {

            if ($sum < $max_sum_order) {

                if ($max_sum_order - $sum < $sum_delivery) return $max_sum_order - $sum;
                else return $sum_delivery;

            } else return 0;

        }

    }

    public static function count_sum($user_id)
    {

        $total = 0;
        $products = self::getAllProductsInCart($user_id);
        foreach ($products as $product) {

            $total += $product->price_all;

        }

        return $total;

    }

    public static function count_cashback($user_id)
    {

        $cashback_pay = BotUsersController::getValueFromUsers($user_id, 'cashback_pay');
        $cashback = BotUsersController::getValueFromUsers($user_id, 'cashback_summa');

        return $cashback_pay == 1 ? $cashback : 0;

    }

    public static function count_sum_total_without_cashback($user_id)
    {

//        $total = self::count_sum_products_no_paid($user_id);
        $total = self::count_sum_products_no_paid_for_cashback($user_id);
        $delivery = self::count_delivery($user_id, $total);
        $birthday = self::count_birthday($user_id);

        return $total + $delivery - $birthday;

    }

    public static function count_sum_total_without_delivery($user_id)
    {

        $total = self::count_sum($user_id);

        return $total;

    }

    public static function count_birthday($user_id)
    {

        $sum = self::count_sum_products_no_discounts($user_id);
        $total = BotUsersNavController::getValue($user_id, 'birthday') == 1 ? bcmul(bcdiv($sum, 100, 2), 20, 2) : 0;

        return $total;

    }

    public static function count_sum_total($user_id)
    {

        $sum = self::count_sum($user_id);
        $birthday = self::count_birthday($user_id);
        $delivery = self::count_delivery($user_id, $sum - $birthday);
        $cashback = self::count_cashback($user_id);

        if ($delivery > 0) {
            $total = bcsub(bcadd(bcsub($sum, $birthday, 2), $delivery, 2), $cashback, 2);
        }
        else {
            if ($birthday > 0) $total = bcsub(bcsub($sum, $birthday, 2), $cashback, 2);
            else $total = bcsub(bcadd($sum, $delivery, 2), $cashback, 2);
        }

        return $total;

    }

    public static function replace_digit($str)
    {

        $arr_num = [
            '0' => '0️⃣',
            '1' => '1️⃣',
            '2' => '2️⃣',
            '3' => '3️⃣',
            '4' => '4️⃣',
            '5' => '5️⃣',
            '6' => '6️⃣',
            '7' => '7️⃣',
            '8' => '8️⃣',
            '9' => '9️⃣',
        ];

        $arr_str = str_split($str);
        $new_str = '';
        foreach ($arr_str as $value) {
            $new_str .= is_numeric($value) ? $arr_num[$value] : '';
        }

        return $new_str;
//        return $str;

    }

    public static function replace_pizza_emoji($category) {

        return $category == 'pizza' ? '🍕 ' : '';

    }

    public static function replace_emoji($str) {

        $ins = '';
        $ins = strripos($str, 'Сыр') === false && strripos($str, 'Сир') === false && strripos($str, 'cheese') === false ? '' : '🧀 ';
        $ins = strripos($str, 'Сосиски') === false ? $ins : '🌭 ';

        return $ins;

    }

    public static function checkUsersCartForRiminder()
    {
        $minutes = 15;
        $products = BotCart::leftJoin('bot_users_nav', 'bot_users_nav.user_id', 'bot_cart.id_user')
            ->where('action_pizza', 0)
            ->where('product_present', 0)
            ->get();
        $users = $products->unique('id_user');

        foreach ($users as $user) {

//            $user_id = 522750680;
            $user_id = $user->id_user;

            $inline_keyboard = new InlineKeyboard([]);

            $time_last_edit = $products->where('id_user', $user->id_user)->sortByDesc('date_edit')->first()['date_edit'];
            $time_z = strtotime($time_last_edit."+".$minutes."minutes");
            if (time() > $time_z && $user->id_user == $user_id) {

                if (!isset($user->send_reminder) || $user->send_reminder == null) {
                    $text = BotTextsController::getText($user_id, 'Reminder', 'show1');
                    list($text_button, $data_button) = BotButtonsInlineController::getButtonInline($user_id, 'Cart', 'cart');
                    $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text_button, 'callback_data' => $data_button]) );
                    list($text_button, $data_button) = BotButtonsInlineController::getButtonInline($user_id, 'Menu', 'menu');
                    $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text_button, 'callback_data' => $data_button]) );
                    BotUsersNavController::updateValue($user_id, 'send_reminder', 1);
                    BotUsersNavController::updateValue($user_id, 'send_reminder_datetime', date("Y-m-d H:i:s"));
                    BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'order_message_id', null);
                    BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'cart_message_id', null);
                    $data = ['chat_id' => $user_id];
                    $data['text'] = $text;
                    $data['reply_markup'] = $inline_keyboard;
                    $result = Request::sendMessage($data);
                }
                elseif (isset($user->send_reminder) && $user->send_reminder == 1) {
                    $time_reminder_z = strtotime($user->send_reminder_datetime."+".$minutes."minutes");
                    if (time() > $time_reminder_z) {
                        $text = BotTextsController::getText($user_id, 'Reminder', 'show2');
                        $clear_cart = BotCart::where('id_user', $user_id)->where('action_pizza', 0)->where('product_present', 0)->delete();
                        BotUsersNavController::updateValue($user_id, 'send_reminder', null);
                        BotUsersNavController::updateValue($user_id, 'send_reminder_datetime', null);
                        BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'order_message_id', null);
                        BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'cart_message_id', null);
                        $data = ['chat_id' => $user_id];
                        $data['text'] = $text;
                        $data['reply_markup'] = $inline_keyboard;
                        $result = Request::sendMessage($data);
                    }
                }
            }

        }
//        dd($products, $users);
        return true;
    }

}
