<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotCart;
use App\Models\BotCartNew;
use App\Models\BotSendingMessages;
use App\Models\BotSendingMessagesReactions;
use App\Models\BotSendingMessagesReactionsAnswers;
use App\Models\BotSendingMessagesReactionsHistory;
use App\Models\BotUser;
use App\Models\BotUserSettings;
use App\Http\Controllers\Controller;

use App\Http\Controllers\WayForPayController;
use App\Models\PrestaShop_Product;
use App\Models\PrestaShop_Product_Attribute;
use App\Models\Simpla_Variants;
use Illuminate\Http\Request as LRequest;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Request;

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Entities\Update;


class CallbackqueryCommandController extends Controller {

    public static function index($user_id, $callback_data, $telegram, $callback_query, $firstname) {

        $message_id = $callback_query->getMessage()->getMessageId();

        if (stripos($callback_data, 'addstat_begin___') !== false) {

            $arr = explode("___", $callback_data);
            $button = $arr[1];
            $post_id = $arr[2];
            BotSendingMessages::find($post_id)->increment('button'.$button);

            StartCommandController::begin_ask_action_pizza($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'begin___') !== false) {
            StartCommandController::begin_ask_action_pizza($user_id);
//            $arr = explode("___", $callback_data);
//            return ['🏁 Сделать заказ', true];
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'begin_yes___') !== false) {
            StartCommandController::begin($user_id);
//            $arr = explode("___", $callback_data);
//            return ['🏁 Сделать заказ', true];
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'addstat_gotostart___') !== false) {

            $arr = explode("___", $callback_data);
            $button = $arr[1];
            $post_id = $arr[2];
            BotSendingMessages::find($post_id)->increment('button'.$button);

            CmdObjController::execute('start', $telegram, $callback_query);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'gotostart___') !== false) {
            CmdObjController::execute('start', $telegram, $callback_query);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'addstat_gocontactus___') !== false) {

            $arr = explode("___", $callback_data);
            $button = $arr[1];
            $post_id = $arr[2];
            BotSendingMessages::find($post_id)->increment('button'.$button);

            StartCommandController::contact_us($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'gocontactus___') !== false) {
            StartCommandController::contact_us($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'call_operator___') !== false) {
            StartCommandController::call_operator($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'call_me___') !== false) {
            CmdObjController::execute('callme', $telegram, $callback_query);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'addstat_gomore___') !== false) {

            $arr = explode("___", $callback_data);
            $button = $arr[1];
            $post_id = $arr[2];
            BotSendingMessages::find($post_id)->increment('button'.$button);

            MoreCommandController::execute($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'gomore___') !== false) {
            MoreCommandController::execute($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'addstat_gocashback___') !== false) {

            $arr = explode("___", $callback_data);
            $button = $arr[1];
            $post_id = $arr[2];
            BotSendingMessages::find($post_id)->increment('button'.$button);

            MoreCommandController::send_cashback($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'gocashback___') !== false) {
            MoreCommandController::send_cashback($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'gomenu___') !== false) {

//            CmdObjController::execute('menu', $telegram, $callback_query);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'gocart___') !== false) {
            CmdObjController::execute('order', $telegram, $callback_query);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'addstat_goactions___') !== false) {

            $arr = explode("___", $callback_data);
            $button = $arr[1];
            $post_id = $arr[2];
            BotSendingMessages::find($post_id)->increment('button'.$button);

            MoreCommandController::send_actions($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'goactions___') !== false) {
            MoreCommandController::send_actions($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'addstat_goreviews___') !== false) {

            $arr = explode("___", $callback_data);
            $button = $arr[1];
            $post_id = $arr[2];
            BotSendingMessages::find($post_id)->increment('button'.$button);

            MoreCommandController::get_reviews($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'goreviews___') !== false) {
            MoreCommandController::get_reviews($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'gosendreview___') !== false) {
            MoreCommandController::send_review($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'gosendreviewback___') !== false) {
            MoreCommandController::execute($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'gosendreviewok___') !== false) {
            StartCommandController::send_hello($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'goaboutus___') !== false) {
            $arr = explode("___", $callback_data);
            return ['😃 О нас', true, null];
        }
        elseif (stripos($callback_data, 'gohelp___') !== false) {
            $arr = explode("___", $callback_data);
            return ['❓ Помощь', true, null];
        }
        elseif (stripos($callback_data, 'feedback_start___') !== false) {
            FeedBackCommandController::execute($user_id);
            return ['', false, null];
        }
//        elseif (stripos($callback_data, 'feedback_start_test___') !== false) {
//            FeedBackCommandController::execute($user_id);
//            return ['', false];
//        }
        elseif (stripos($callback_data, 'menu_show_options___') !== false) {
            MenuCommandController::show_menu_options($user_id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'pizza_filter___') !== false) {
            MenuCommandController::show_menu_filter_options($user_id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'pizza_filter_back___') !== false) {
            MenuCommandController::show_menu_filter_back_options($user_id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'menu_option_back___') !== false) {
            MenuCommandController::execute($user_id, 'edit', $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'menu_product_plus___') !== false) {
            $arr = explode("___", $callback_data);
            $user_id = $arr[1];
            $product_id = $arr[2];
            $variant_id = $arr[3];
            $act = $arr[4];
            BotCartController::addToCart($user_id, $product_id, $variant_id, $message_id, $act);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'menu_product_minus___') !== false) {
            $arr = explode("___", $callback_data);
            $user_id = $arr[1];
            $product_id = $arr[2];
            $variant_id = $arr[3];
            $act = $arr[4];
            BotCartController::removeFromCart($user_id, $product_id, $variant_id, $message_id, $act);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'cart_product_plus___') !== false) {
            $arr = explode("___", $callback_data);
            $user_id = $arr[1];
            $product_id = $arr[2];
            $variant_id = $arr[3];
            $act = $arr[4];
            BotCartController::addToCart($user_id, $product_id, $variant_id, $message_id, $act);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'cart_product_minus___') !== false) {
            $arr = explode("___", $callback_data);
            $user_id = $arr[1];
            $product_id = $arr[2];
            $variant_id = $arr[3];
            $act = $arr[4];
            BotCartController::removeFromCart($user_id, $product_id, $variant_id, $message_id, $act);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'cart_edit___') !== false) {
            CartCommandController::edit($user_id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'cart_product_edit___') !== false) {
            $arr = explode("___", $callback_data);
            $id = $arr[1];
            BotCartController::edit_cart_product($user_id, $id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'cart_back___') !== false) {
            CartCommandController::execute($user_id, 'edit', $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'edit_cart_product___') !== false) {
            $arr = explode("___", $callback_data);
            $id = $arr[1];
//            BotCartController::edit_cart_product($user_id, $id, $message_id);
            $cart = BotCartController::getProductInCartFromId($user_id, $id);
            $product_id = $cart['id_tovar'];
            $variant_id = $cart['id_size'];
            MenuCommandController::show_product_from_id($user_id, $product_id, $variant_id, 'cart');
            CartCommandController::execute($user_id, 'edit', null);
            return ['', false, null];
//            $data_t = ['chat_id' => $user_id];
//            $data_t['text'] = 'debug: '.$id.'; '.$message_id.'; ';
//            $send_t = Request::sendMessage($data_t);
        }
        elseif (stripos($callback_data, 'product_add_ingredient___') !== false) {
            $arr = explode("___", $callback_data);
            $id = $arr[1];
            BotCartController::showBortiks($user_id, $id, null, $message_id, 'menu');
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'product_cart_add_ingredient___') !== false) {
            $arr = explode("___", $callback_data);
            $product_id = $arr[1];
            $variant_id = $arr[2];
            $act = $arr[3];
            BotCartController::showBortiks($user_id, $product_id, $variant_id, $message_id, $act);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'add_bortik___') !== false) {
            $arr = explode("___", $callback_data);
            $product_cart_id = $arr[1];
            $bortik_id = $arr[2];
            $bortik_variant_id = $arr[3];
            $act = $arr[4];
            BotCartController::addBortikToCart($user_id, $product_cart_id, $bortik_id, $bortik_variant_id, $message_id, $act);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'remove_bortik___') !== false) {
            $arr = explode("___", $callback_data);
            $product_cart_id = $arr[1];
            $bortik_id = $arr[2];
            $bortik_variant_id = $arr[3];
            $act = $arr[4];
            BotCartController::removeBortikFromCart($user_id, $product_cart_id, $bortik_id, $bortik_variant_id, $message_id, $act);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'menu_product_back___') !== false) {
            $arr = explode("___", $callback_data);
            $id = $arr[1];
            BotCartController::updateMessageProduct($user_id, $id, null, $message_id, 'menu');
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'cart_product_back___') !== false) {
            $arr = explode("___", $callback_data);
            $product_id = $arr[1];
            $variant_id = $arr[2];
            $act = $arr[3];
            BotCartController::updateMessageProduct($user_id, $product_id, $variant_id, $message_id, $act);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'select_bortik___') !== false) {
            $arr = explode("___", $callback_data);
            $product_id = $arr[1];
            $variant_id = $arr[2];
            $bortik_product_id = $arr[3];
            $act = $arr[4];
            BotCartController::selectBortik($user_id, $product_id, $variant_id, $bortik_product_id, $message_id, $act);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'cart_delivery_edit___') !== false) {
            BotCartController::delivery_edit($user_id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'change_delivery_type___') !== false) {
            $arr = explode("___", $callback_data);
            $id = $arr[1];
            BotCartController::change_delivery($user_id, $id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'cancel_order___') !== false) {
            BotCartController::cancel_order($user_id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'cancel_order_delete_yes___') !== false) {
            BotCartController::cancel_order_yes($user_id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'cancel_order_delete_no___') !== false) {
            CartCommandController::execute($user_id, 'edit', $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'cart_cashback_edit___') !== false) {
            CmdObjController::execute('cashback', $telegram, $callback_query);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'cart_order___') !== false) {
//            CmdObjController::execute('cashback', $telegram, $callback_query);
            BotOrderController::pay_cashback($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'order_date_back___') !== false) {
            BotOrderController::delete_message($user_id, $message_id);
            CmdObjController::execute('order', $telegram, $callback_query);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'order_calendar_back___') !== false) {
            BotOrderController::select_date($user_id, 'edit', $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'order_calendar_prev___') !== false) {
            $arr = explode("___", $callback_data);
            $day = $arr[1];
            BotOrderController::get_calendar($user_id, $message_id, $day);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'order_calendar_next___') !== false) {
            $arr = explode("___", $callback_data);
            $day = $arr[1];
            BotOrderController::get_calendar($user_id, $message_id, $day);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'order_other_date___') !== false) {
            BotOrderController::get_calendar($user_id, $message_id, null);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'order_select_day___') !== false) {
            $arr = explode("___", $callback_data);
            $day = $arr[1];
            BotOrderController::select_time($user_id, $day, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'order_time_back___') !== false) {
            BotOrderController::select_date($user_id, 'edit', $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'order_select_time___') !== false) {
            $arr = explode("___", $callback_data);
            $time = $arr[1];
            BotOrderController::select_payment($user_id, $time, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'order_pay_back___') !== false) {
            BotOrderController::select_time($user_id, null, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'order_select_payment___') !== false) {
            $arr = explode("___", $callback_data);
            $id = $arr[1];
            BotOrderController::select_nta($user_id, $id, 'edit', 'edit', $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'order_nta_back___') !== false) {
            BotOrderController::select_payment($user_id, null, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'order_change_edit___') !== false) {
            BotOrderController::select_nta($user_id, null, 'edit', 'nta', $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'order_nta_edit_back___') !== false) {
            BotOrderController::select_nta($user_id, null, 'edit', 'edit', $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'order_change_name___') !== false) {
            BotOrderController::change_name($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'order_change_phone___') !== false) {
            BotOrderController::change_phone($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'order_change_addr___') !== false) {
            BotOrderController::change_addr($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'order_nta_next___') !== false) {
            if (BotUsersNavController::getDeliveryYesOrNo($user_id) == 0) BotOrderController::change_no_call($user_id);
            else BotOrderController::change_addr($user_id);
//            BotOrderController::change_addr($user_id);
//            BotOrderController::change_no_call($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'order_before_success_card___') !== false) {
            BotOrderController::show_order_finish($user_id, 'send', $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'order_success___') !== false) {
            BotOrderController::add_order($user_id, $telegram, $callback_query);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'addstat_change_lang___') !== false) {

            $arr = explode("___", $callback_data);
            $button = $arr[1];
            $post_id = $arr[2];
            BotSendingMessages::find($post_id)->increment('button'.$button);

            StartCommandController::change_lang($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'change_lang___') !== false) {
            StartCommandController::change_lang($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'change_user_lang___') !== false) {
            $arr = explode("___", $callback_data);
            $lang = $arr[1];
            BotUserSettingsController::updateLang($user_id, $lang);
            $data_delete = ['chat_id' => $user_id];
            $data_delete['message_id'] = $message_id;
            Request::deleteMessage($data_delete);
            CmdObjController::execute('start', $telegram, $callback_query);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'addstat_goshare___') !== false) {

            $arr = explode("___", $callback_data);
            $button = $arr[1];
            $post_id = $arr[2];
            BotSendingMessages::find($post_id)->increment('button'.$button);

            StartCommandController::share($user_id);
            return ['', false, null];
        }


        /**
         *  RAFFLE
         */
        elseif (stripos($callback_data, 'addstat_rafflego___') !== false) {

            $arr = explode("___", $callback_data);
            $button = $arr[1];
            $post_id = $arr[2];
            BotSendingMessages::find($post_id)->increment('button'.$button);

            RaffleCommandController::execute($user_id, 'send', null);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'rafflego___') !== false) {
            RaffleCommandController::execute($user_id, 'send', null);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'raffle_rules___') !== false) {
            RaffleCommandController::show_rules($user_id, 'edit', $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'raffle_send_rules___') !== false) {
            RaffleCommandController::show_rules($user_id, 'send', $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'raffle_rules_back___') !== false) {
            RaffleCommandController::execute($user_id,'edit', $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'raffle_start___') !== false) {
            RaffleCommandController::start_game($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'raffle___') !== false) {
            $arr = explode("___", $callback_data);
            $raffle_id = $arr[1];
            $p = $arr[2];
            RaffleCommandController::update_game($user_id, $raffle_id, $p, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'add_action_pizza___') !== false) {
            $arr = explode("___", $callback_data);
            $product_id = $arr[1];
            $variant_id = $arr[2];
            RaffleCommandController::add_action_pizza($user_id, $product_id, $variant_id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'raffle_test___') !== false) {
            RaffleCommandController::start_test_game($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'raffletest___') !== false) {
            $arr = explode("___", $callback_data);
            $raffle_id = $arr[1];
            $p = $arr[2];
            RaffleCommandController::update_test_game($user_id, $raffle_id, $p, $message_id);
            return ['', false, null];
        }


        /**
         *  RAFFLE DRINK CHERRY
         */
//        elseif (stripos($callback_data, 'addstat_raffle_cherry_go___') !== false) {
//            $arr = explode("___", $callback_data);
//            $button = $arr[1];
//            $post_id = $arr[2];
//            BotSendingMessages::find($post_id)->increment('button'.$button);
//
//            RaffleCherryCommandController::execute($user_id, 'send', null);
//            return ['', false, null];
//        }
//        elseif (stripos($callback_data, 'raffle_cherry_go___') !== false) {
//            RaffleCherryCommandController::execute($user_id, 'send', null);
//            return ['', false, null];
//        }
//        elseif (stripos($callback_data, 'raffle_cherry_rules___') !== false) {
//            RaffleCherryCommandController::show_rules($user_id, 'edit', $message_id);
//            return ['', false, null];
//        }
//        elseif (stripos($callback_data, 'raffle_cherry_send_rules___') !== false) {
//            RaffleCherryCommandController::show_rules($user_id, 'send', $message_id);
//            return ['', false, null];
//        }
//        elseif (stripos($callback_data, 'raffle_cherry_rules_back___') !== false) {
//            RaffleCherryCommandController::execute($user_id,'edit', $message_id);
//            return ['', false, null];
//        }
//        elseif (stripos($callback_data, 'raffle_cherry_start___') !== false) {
//            RaffleCherryCommandController::start_game($user_id);
//            return ['', false, null];
//        }
//        elseif (stripos($callback_data, 'rafflecherry___') !== false) {
//            $arr = explode("___", $callback_data);
//            $raffle_id = $arr[1];
//            $p = $arr[2];
//            RaffleCherryCommandController::update_game($user_id, $raffle_id, $p, $message_id);
//            return ['', false, null];
//        }
////        elseif (stripos($callback_data, 'add_action_pizza___') !== false) {
////            $arr = explode("___", $callback_data);
////            $product_id = $arr[1];
////            $variant_id = $arr[2];
////            RaffleCherryCommandController::add_action_pizza($user_id, $product_id, $variant_id, $message_id);
////            return ['', false, null];
////        }
//        elseif (stripos($callback_data, 'raffle_cherry_test___') !== false) {
//            RaffleCherryCommandController::start_test_game($user_id);
//            return ['', false, null];
//        }
//        elseif (stripos($callback_data, 'rafflecherry_test___') !== false) {
//            $arr = explode("___", $callback_data);
//            $raffle_id = $arr[1];
//            $p = $arr[2];
//            RaffleCherryCommandController::update_test_game($user_id, $raffle_id, $p, $message_id);
//            return ['', false, null];
//        }
//        elseif (stripos($callback_data, 'drinkcherry_takeaway___') !== false) {
//            RaffleCherryCommandController::AskWinnerName($user_id, $message_id);
//            return ['', false, null];
//        }


        // REPEAT ORDER
        elseif (stripos($callback_data, 'repeat_order___') !== false) {
            $arr = explode("___", $callback_data);
            $order_id = $arr[1];
            BotOrderController::repeatOrder($user_id, $order_id);
            return ['', false, null];
        }

        elseif (stripos($callback_data, 'feedback_step_0___') !== false) {
            FeedBackCommandController::step_sushi($user_id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'feedback___') !== false) {
            $arr = explode("___", $callback_data);
            $feedback_id = $arr[1];
            $step = $arr[2];
            $o = $arr[3];
            BotFeedBackController::updateFeedBack($user_id, $feedback_id, 'o'.$step, $o);
            $new_step = $step + 1;
            $f = 'step_'.$new_step;
            FeedBackCommandController::$f($user_id, $feedback_id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'test_wayforpay') !== false) {
            WayForPayController::sendWidget($user_id, 'test');
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'addtocartproductfrommailing___') !== false) {
            $arr = explode("___", $callback_data);
            $variant_id = $arr[1];
            $button = $arr[2];
            $post_id = $arr[3];
            $post = BotSendingMessages::find($post_id);
            if ($post) {
                $post->increment('button'.$button);
            }

            if ($variant_id > 0) {
//            $product_id = BotCartController::getProductVariant($variant_id)['product_id'];
                $combination = PrestaShop_Product_Attribute::getProductIdByAttributeId($variant_id);
                Log::debug('COMBINATION', ['combination' => $combination]);

                if (!$combination) {
                    Log::error('Combination not found for variant_id: ' . $variant_id);
                    Request::sendMessage([
                        'chat_id' => $user_id,
                        'text' => 'На жаль, цей товар більше недоступний. Спробуйте обрати інший товар у меню.',
                        'parse_mode' => 'html'
                    ]);
                    return ['', false, null];
                }

                $product = PrestaShop_Product::getProductById($combination->id_product);
                Log::debug('PRODUCT', ['product' => $product]);

                if ($product) {
                    $data = [
                        'user_id' => $user_id,
                        'product_id' => $product->id_product,
                        'combination_id' => $variant_id,
                        'product_name' => $product->name,
                        'price' => $combination->price,
                    ];
                    $add_to_cart = BotCartNew::addProductPresentToCart($data);
                    if ($add_to_cart) {
                        $check_win = BotRaffleController::checkUserRaffleWin($user_id);
                        $inline_keyboard = new InlineKeyboard([]);
                        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Start', 'begin');
                        if ($check_win == 0) {
                            $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'web_app' => ['url' => route('show_new_menu', ['user_id' => $user_id])]]));
                        }
                        else {
                            $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));
                        }
                        $data_send = ['chat_id' => $user_id];
                        $data_send['reply_markup'] = $inline_keyboard;
                        $data_send['text'] = $add_to_cart->product_name.' додано до твого замовлення';
                        $data_send['parse_mode'] = 'html';
                        Request::sendMessage($data_send);
                    }
                }
//            BotCartController::addToCart($user_id, $product_id, $variant_id, $message_id, 'mailing');
            }
            return ['', false, null];
        }

        // BIRTHDAY
        elseif (stripos($callback_data, 'cart_birthday_edit___') !== false) {
            CmdObjController::execute('birthday', $telegram, $callback_query);
            return ['', false, null];
        }

        // CHANGE CITY
        elseif (stripos($callback_data, 'change_city___') !== false) {
//            BotUsersNavController::deleteMessageId($user_id, $message_id);
            StartCommandController::change_city($user_id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'select_region___') !== false) {
            $arr = explode("___", $callback_data);
            $region_id = $arr[1];
            $region_old_id = BotUser::getValue($user_id, 'city_id');
            if ($region_old_id != $region_id) {
                BotUser::setValue($user_id, 'city_id', $region_id);
                BotCartController::clear_cart($user_id);
            }
            BotUsersNavController::deleteMessageId($user_id, $message_id);
            MenuCommandController::show_menu_options($user_id, $message_id);
            return ['', false, null];
        }
        // REACTIONS
        elseif (stripos($callback_data, 'sending_messages_reactions___') !== false) {
            $arr = explode("___", $callback_data);
            $post_id = $arr[1];
            $reaction_id = $arr[2];
            $checkAnswer = null;
            $incrementClicks = BotSendingMessagesReactions::incrementClicks($user_id, $post_id, $reaction_id);
            if ($incrementClicks) {
                $addToHistory = BotSendingMessagesReactionsHistory::addToHistory($user_id, $post_id, $reaction_id);
                $checkAnswer = BotSendingMessagesReactionsAnswers::checkAnswer($user_id, $post_id);
            }
            if ($checkAnswer) return [$checkAnswer, true, null];
            else return ['', false, null];
        }
        // GAMES
        elseif (stripos($callback_data, 'addstat_select_games___') !== false) {
            $arr = explode("___", $callback_data);
            $button = $arr[1];
            $post_id = $arr[2];
            BotSendingMessages::find($post_id)->increment('button'.$button);

            BotGamesController::selectGame($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'select_games___') !== false) {
            BotGamesController::selectGame($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'game_dice___') !== false) {
            $arr = explode("___", $callback_data);
            $emoji = $arr[1];
            $value = $arr[2];
            $send_game = BotGamesController::startGame($user_id, 'dice', ['emoji' => $emoji, 'value' => $value]);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'addstat_game_sea_battle_warning___') !== false) {
            $arr = explode("___", $callback_data);
            $button = $arr[1];
            $post_id = $arr[2];
            BotSendingMessages::find($post_id)->increment('button'.$button);

            $result = BotGameSeaBattleController::showWarning($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'game_sea_battle_warning___') !== false) {
            $result = BotGameSeaBattleController::showWarning($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'game_sea_battle_open_game___') !== false) {
            $result = BotGameSeaBattleController::openGame($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'game_sea_battle_no_cashback_rules___') !== false) {
            $result = BotGameSeaBattleController::showRules($user_id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'game_sea_battle_no_cashback_back___') !== false) {
            $result = BotGameSeaBattleController::noCashbackBack($user_id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'game_sea_battle_rules___') !== false) {
            $result = BotGameSeaBattleController::showRules($user_id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'game_sea_battle_back_open_game___') !== false) {
            $result = BotGameSeaBattleController::openGame($user_id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'game_sea_battle_play___') !== false) {
            $result = BotGameSeaBattleController::playGame($user_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'game_sea_battle_field_set___') !== false) {
            $arr = explode("___", $callback_data);
            $field_id = $arr[1];
            $f_number = $arr[2];
            $result = BotGameSeaBattleController::updateField($user_id, $message_id, $field_id, $f_number);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'game_sea_battle_back_field_clear___') !== false) {
            $arr = explode("___", $callback_data);
            $field_id = $arr[1];
            $result = BotGameSeaBattleController::clearField($user_id, $message_id, $field_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'game_sea_battle_go___') !== false) {
            $arr = explode("___", $callback_data);
            $field_id = $arr[1];
            $result = BotGameSeaBattleController::searchForOpponent($user_id, $message_id, $field_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'game_sea_battle_cancel_search___') !== false) {
            $arr = explode("___", $callback_data);
            $result = BotGameSeaBattleController::cancelSearchAsk($user_id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'game_sea_battle_cancel_search_no___') !== false) {
            $arr = explode("___", $callback_data);
            $result = BotGameSeaBattleController::cancelSearchNo($user_id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'game_sea_battle_cancel_search_yes___') !== false) {
            $arr = explode("___", $callback_data);
            $result = BotGameSeaBattleController::cancelSearchYes($user_id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'game_sea_battle_play_with_bot___') !== false) {
            $result = BotGameSeaBattleController::playGameWithOpponent($user_id, $message_id, 1);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'game_sea_battle_play_start___') !== false) {
            $arr = explode("___", $callback_data);
            $playWithBot = $arr[1];
            $result = BotGameSeaBattleController::playGameStart($user_id, $message_id, $playWithBot);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'game_sea_battle_field_shot___') !== false) {
            $arr = explode("___", $callback_data);
            $playWithBot = $arr[1];
            $game_id = $arr[2];
            $field_id = $arr[3];
            $shot_id = $arr[4];
            $f = $arr[5];
            $result = BotGameSeaBattleController::playGameStart($user_id, $message_id, $playWithBot, $f);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'game_sea_battle_show_results___') !== false) {
            $result = BotGameSeaBattleController::showResults($user_id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'game_sea_battle_review___') !== false) {
            $result = BotGameSeaBattleController::rateGame($user_id, $message_id);
            return ['', false, null];
        }
        elseif (stripos($callback_data, 'game_sea_battle_set_rate___') !== false) {
            $arr = explode("___", $callback_data);
            $game_id = $arr[1];
            $rate = $arr[2];
            $result = BotGameSeaBattleController::rateGameComment($user_id, $message_id, $game_id, $rate);
            return ['', false, null];
        }

        else return ['', false, null];

//        if ($callback_data === 'enter_key___') {
//            CmdObjController::execute('enterkey', $telegram, $callback_query);
//        }

    }

    public static function gameExecute($game, $user_id)
    {
        return ['game', false, route('testGame')];
    }

}
