<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotMenu;
use App\Models\BotOrder;
use App\Models\BotSettingsSticker;
use App\Models\BotUsersNav;
use App\Http\Controllers\Controller;

use App\Models\Simpla_Categories;
use App\Models\Simpla_Images;
use App\Models\Simpla_Products;
use App\Models\Simpla_Variants;
use Illuminate\Http\Request as LRequest;

use Longman\TelegramBot\Commands\SystemCommands\StartCommand;
use Longman\TelegramBot\Commands\UserCommands\OrderCommand;
use Longman\TelegramBot\Entities\InputMessageContent\InputTextMessageContent;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;

use App\Models\BotSettingsTexts;
use App\Models\BotSettingsButtonsInline;

class CartCommandController extends Controller
{

    public static function execute($user_id, $act, $message_id)
    {

        $command = 'Cart';
        $name = 'show_cart';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command);

        BotUsersNavController::updateValue($user_id, 'order_sent', 0);
        BotCashbackController::clearCashback($user_id);

        // Вытягиваем из БД текст для сообщения
        $text_cart = BotTextsController::getText($user_id, $command, $name);

//        $data_text = ['chat_id' => $user_id];
//        $data_text['text'] = $text_cart;
//        $data_text['parse_mode'] = 'html';
//        $send_text = Request::sendMessage($data_text);

        $products_num = BotCartController::checkProductsInCart($user_id);
        if ($products_num == 0) {

            BotUserHistoryController::insertToHistory($user_id, 'open', $command.': корзина пуста');
            BotUsersNavController::updateValue($user_id, 'birthday', null);

            $text = BotTextsController::getText($user_id, $command, 'empty');

            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = $text_cart.PHP_EOL.PHP_EOL.$text;
            $data_text['parse_mode'] = 'html';
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

            $result = Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);
            sleep(1);
            return StartCommandController::send_hello($user_id);

        }

        $inline_keyboard = BotCartButtonsController::get_buttons_cart_total($user_id);

        $delivery_discount = BotUsersNavController::get_delivery_from_user_id($user_id)['discount'];
        list($check_delivery_type_selected, $delivery_type) = BotCartController::get_selected_delivery_type($user_id);
        $min_sum_order = BotSettingsController::getSettings($user_id,'min_sum_order')['settings_value'];
        $total_without_delivery = BotCartController::count_sum_total_without_delivery($user_id);

        if ($check_delivery_type_selected == null) {

            $delivery_id = BotSettingsDeliveryController::getDeliveryCourier($user_id)['id'];
            BotUsersNavController::update_delivery($user_id, $delivery_id);
            $text = BotCartController::getTextCart($user_id, 'show');
//            $text .= PHP_EOL . PHP_EOL . '<b>' . BotTextsController::getText($user_id, 'Cart', 'delivery_edit') . '</b>';
//            $inline_keyboard = BotCartButtonsController::get_buttons_delivery_edit($user_id);

        }
        elseif ($total_without_delivery < $min_sum_order && $delivery_discount == null) {

            $text = BotCartController::getTextCart($user_id, 'show');
            $text .= PHP_EOL . PHP_EOL . '<b>' . BotTextsController::getText($user_id, 'Cart', 'min_order') . '</b>';
            $inline_keyboard = BotCartButtonsController::add_button_menu($user_id, $inline_keyboard);

        }
        else {
            $text = BotCartController::getTextCart($user_id, 'show');
        }

        BotUserHistoryController::insertToHistory($user_id, 'open', $command.PHP_EOL.$text);

        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = $text;
        $data_text['parse_mode'] = 'html';
        $data_text['reply_markup'] = $inline_keyboard;

        if ($act == 'send') {

            $remove_keyboard = StartCommandController::removeKeyboardBottom($user_id);

            $send_text = Request::sendMessage($data_text);
            if ($send_text->getResult() !== null) {
                $message_id = $send_text->getResult()->getMessageId();

                $message_old_id = BotUsersNav::where('user_id', $user_id)->first()['cart_message_id'];
                if ($message_old_id !== null) {
                    $data_delete = ['chat_id' => $user_id];
                    $data_delete['message_id'] = $message_old_id;
                    Request::deleteMessage($data_delete);
                }
                BotUsersNavController::updateCartMessageId($user_id, $message_id);

                BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'menu_message_id', null);
            }

        }
        elseif ($act == 'edit') {
            $message_id = $message_id !== null ? $message_id : BotUsersNavController::getCartMessageId($user_id);
            $data_text['message_id'] = $message_id;
            Request::editMessageText($data_text);
        }

        BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'order_message_id', null);

//        BotCartController::show_cart_total($user_id, $currency, $message_id, $act);

    }

    public static function edit($user_id, $message_id) {

        BotCartController::edit_cart($user_id, $message_id, 1);
//        BotCartController::edit_cart_products($user_id);

//        $data_edit = ['chat_id' => $user_id];
//        $data_edit['reply_markup'] = $inline_keyboard;
//        $data_edit['message_id'] = $message_id;
//        Request::editMessageReplyMarkup($data_edit);

    }

//    public static function execute($user_id, $act, $message_id)
//    {
//
//        $command = 'Cart';
//        $name = 'show_cart';
//
//        // Записываемся в историю
//        BotUserHistoryController::insertToHistory($user_id, 'open', $command);
//
//        // Вытягиваем из БД текст для сообщения
//        $text_cart = BotTextsController::getText($user_id, $command, $name);
//
//        $products_num = BotCartController::checkProductsInCart($user_id);
//        if ($products_num == 0) {
//
//            $text = BotTextsController::getText($user_id, $command, 'empty');
//
//            $data_text = ['chat_id' => $user_id];
//            $data_text['text'] = $text_cart.PHP_EOL.PHP_EOL.$text;
//            $data_text['parse_mode'] = 'html';
//
//            $send_text = Request::sendMessage($data_text);
//            if ($send_text->getResult() !== null) {
//                $message_id = $send_text->getResult()->getMessageId();
//
//                $message_old_id = BotUsersNav::where('user_id', $user_id)->first()['cart_message_id'];
//                if ($message_old_id !== null) {
//                    $data_delete = ['chat_id' => $user_id];
//                    $data_delete['message_id'] = $message_old_id;
//                    Request::deleteMessage($data_delete);
//                }
//                BotCartController::updateCartMessageId($user_id, $message_id);
//            }
//
//            exit();
//
//        }
//
//        $products = BotCartController::getProductsInCart($user_id);
//
//        $pcs = BotTextsController::getText($user_id, 'System', 'pcs');
//        $currency = BotTextsController::getText($user_id, 'System', 'currency');
//
//        $text = '';
//        $price_all = 0;
//        foreach ($products as $product) {
//
//            $price_all += $product['price_all'];
//            $variant = BotCartController::getProductVariant($product['id_size']);
//            $text .= '<pre>'.$product['product_name'].' ('.$variant['name'].') '.PHP_EOL.$product['price'].' x '.$product['quantity'].' = '.$product['price_all'].' '.$currency.'</pre>'.PHP_EOL.PHP_EOL;
//
//        }
//
//        $text_total = BotTextsController::getText($user_id, $command, 'total');
//        $sum = BotCartController::count_sum($user_id);
//        $total = BotCartController::count_sum_total($user_id);
//
//        $text_delivery = BotTextsController::getText($user_id, $command, 'delivery');
//        $delivery = BotCartController::count_delivery($user_id, $sum);
//        $text .= ''.$text_delivery.': '.$delivery.' '.$currency.''.PHP_EOL;
//
//        $text .= '<b>'.$text_total.': '.$total.' '.$currency.'</b>';
//
//        $inline_keyboard = BotCartController::get_buttons($user_id);
//
//        $data_text = ['chat_id' => $user_id];
//        $data_text['text'] = $text_cart.PHP_EOL.PHP_EOL.$text;
//        $data_text['parse_mode'] = 'html';
//        $data_text['reply_markup'] = $inline_keyboard;
//
//        if ($act == 'send') {
//            $send_text = Request::sendMessage($data_text);
//            if ($send_text->getResult() !== null) {
//                $message_id = $send_text->getResult()->getMessageId();
//
//                $message_old_id = BotUsersNav::where('user_id', $user_id)->first()['cart_message_id'];
//                if ($message_old_id !== null) {
//                    $data_delete = ['chat_id' => $user_id];
//                    $data_delete['message_id'] = $message_old_id;
//                    Request::deleteMessage($data_delete);
//                }
//                BotCartController::updateCartMessageId($user_id, $message_id);
//            }
//        }
//        elseif ($act == 'edit') {
//            $data_text['message_id'] = $message_id;
//            Request::editMessageText($data_text);
//        }
//
//    }

}
