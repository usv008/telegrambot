<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotOrder;
use App\Models\BotSettingsCashback;
use App\Models\BotSettingsSticker;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request as LRequest;

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;

use App\Models\BotSettingsTexts;
use App\Models\BotSettingsButtonsInline;

class CashbackCommandController extends Controller
{

    public static function show_message($user_id) {

        $command = 'Cashback';

        $message_cart_id = BotUsersNavController::getCartMessageId($user_id);
        $inline_keyboard = new InlineKeyboard([]);
        $data_edit = ['chat_id' => $user_id];
        $data_edit['reply_markup'] = $inline_keyboard;
        $data_edit['message_id'] = $message_cart_id;
        Request::editMessageReplyMarkup($data_edit);

        $buttons = BotButtonsController::getButtons($user_id,'System', ['back', 'skip']);

        $total = BotCartController::count_sum_total_without_cashback($user_id);
        $user_cashback = BotCashbackController::getUserCashback($user_id);
        $user_cashback_action = BotCashbackController::getUserCashbackAction($user_id);
        $min_order_sum = BotSettingsCashback::get_min_order_sum();

        if ($total >= 350) $pay_cashback = $user_cashback + $user_cashback_action >= $total / 2 ? bcdiv($total, '2', 2) : $user_cashback + $user_cashback_action;
        else $pay_cashback = $user_cashback >= $total / 2 ? bcdiv($total, '2', 2) : $user_cashback;

        if (BotUsersNavController::getDeliveryYesOrNo($user_id) == 1) {
            if ($total - $pay_cashback < $min_order_sum) $pay_cashback = $total - $min_order_sum;
            if ($pay_cashback < 0) $pay_cashback = 0;
        }

        $currency = BotTextsController::getText($user_id, 'System', 'currency');

        $products_paid = BotCartController::count_sum_products_paid($user_id);
        if ($products_paid > 0) {

            $products_no_paid = BotCartController::count_sum_products_no_paid_for_cashback($user_id);
            $sum = BotCartController::count_sum($user_id);
            $delivery = BotCartController::count_delivery($user_id, $sum);
            $text_ins = BotTextsController::getText($user_id, $command, 'message_products');
            $text_ins = str_replace("___TOTAL___", $products_no_paid.' '.$currency, $text_ins);
            $text_ins = str_replace("___DELIVERY___", $delivery.' '.$currency, $text_ins);
            $text_ins = str_replace("___PRODUCTS_SUM___", $products_paid.' '.$currency, $text_ins);
            $text_ins = str_replace("___CASHBACK_USER___", $user_cashback.' '.$currency, $text_ins);
            $text_ins = str_replace("___CASHBACK_MAX___", $pay_cashback.' '.$currency, $text_ins);

        }
        else {

            if (BotUsersNavController::getDeliveryYesOrNo($user_id) == 1) {
                $text_ins = BotTextsController::getText($user_id, $command, 'message');
            }
            else {
                $text_ins = BotTextsController::getText($user_id, $command, 'message_takeaway');
            }
            $text_ins = str_replace("___TOTAL___", $total.' '.$currency, $text_ins);
            $text_ins = str_replace("___CASHBACK_USER___", $user_cashback.' '.$currency, $text_ins);
            $text_ins = str_replace("___CASHBACK_ACTION_USER___", $user_cashback_action.' '.$currency, $text_ins);
            $text_ins = str_replace("___CASHBACK_MAX___", $pay_cashback.' '.$currency, $text_ins);
            $text_ins = str_replace("___MIN_ORDER_SUM___", $min_order_sum.' '.$currency, $text_ins);

        }

        BotUserHistoryController::insertToHistory($user_id, 'send', 'CashBack - user_cb: '.$user_cashback.'; action_cb: '.$user_cashback_action.'; max_pay: '.$pay_cashback);

        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = $text_ins;
        $data_text['parse_mode'] = 'html';

        $keyboard_bottom = new Keyboard([]);

        $pay_cashback_ins = (string)floor($pay_cashback);
        $keyboard_bottom->addRow($pay_cashback_ins);

        foreach ($buttons as $button) {
            $keyboard_bottom->addRow($button);
        }

        $keyboard_b = $keyboard_bottom
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->setSelective(false);

        $data_text['reply_markup'] = $keyboard_b;

        $send_text = Request::sendMessage($data_text);


    }

    public static function show_message_ok($user_id, $text) {

        $command = 'Cashback';

        $buttons_back = BotButtonsController::getButtons($user_id,'System', ['back']);

        $cashback_pay = $text > 0 ? 1 : 0;
        BotUsersController::updateUsers($user_id, 'cashback_pay', $cashback_pay);
        BotUsersController::updateUsers($user_id, 'cashback_summa', $text);

        $currency = BotTextsController::getText($user_id, 'System', 'currency');

        $name = $text > 0 ? 'message_ok' : 'message_ok_0';
        $text_ins = BotTextsController::getText($user_id, $command, $name);
        $text_ins = str_replace("___SUMMA___", $text.' '.$currency, $text_ins);

        BotUserHistoryController::insertToHistory($user_id, 'send', 'CashBack - summa pay: '.$text);

        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = $text_ins;
        $data_text['parse_mode'] = 'html';
        $data_text['reply_markup'] = Keyboard::remove(['selective' => true]);;
        $send_text = Request::sendMessage($data_text);

    }

    public static function show_message_no_cashback($user_id) {

        $command = 'Cashback';

        $text_ins = BotTextsController::getText($user_id, $command, 'message_no_cashback');

        BotUserHistoryController::insertToHistory($user_id, 'send', 'CashBack - no');

        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = $text_ins;
        $data_text['parse_mode'] = 'html';
        $data_text['reply_markup'] = Keyboard::remove(['selective' => true]);;
        $send_text = Request::sendMessage($data_text);

    }

    public static function show_message_action_no($user_id) {

        $command = 'Cashback';

        $text_ins = BotTextsController::getText($user_id, $command, 'message_action_no');

        BotUserHistoryController::insertToHistory($user_id, 'send', 'CashBack - action no');

        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = $text_ins;
        $data_text['parse_mode'] = 'html';

        $send_text = Request::sendMessage($data_text);


    }

}
