<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotCart;
use App\Models\BotMenu;
use App\Models\BotOrder;
use App\Models\BotOrderContent;
use App\Models\BotOrders;
use App\Models\BotOrdersNew;
use App\Models\BotPaymentsCallback;
use App\Models\BotSettings;
use App\Models\BotSettingsCashback;
use App\Models\BotUser;
use App\Models\BotUsersNav;
use App\Http\Controllers\BotRaffleUsersController;
use App\Http\Controllers\LiqPayController;
use App\Http\Controllers\SimplaOrdersController;
use App\Http\Controllers\SimplaPurchasesController;
use App\Http\Controllers\SimplaRegionsController;
use App\Http\Controllers\SimplaUsersController;
use App\Http\Controllers\WayForPayController;
use App\Models\Simpla_Categories;
use App\Models\Simpla_Complect_Products;
use App\Models\Simpla_Images;
use App\Models\Simpla_Options;
use App\Models\Simpla_Products;
use App\Models\Simpla_Variants;
use App\Models\SimplaPurchases;
use App\Models\SimplaPurchasesComplects;
use Illuminate\Http\Request as LRequest;
use Longman\TelegramBot\Commands\UserCommands\OrderCommand;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;
use App\Http\Controllers\Controller;
use App\Models\BotSettingsButtons;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;

class BotOrderController extends Controller
{

    public static function delete_message($user_id, $message_id)
    {

        $data_edit = ['chat_id' => $user_id];
        $data_edit['message_id'] = $message_id;
        Request::deleteMessage($data_edit);

    }

    public static function pay_cashback($user_id)
    {
        $message_cart_id = BotUsersNavController::getCartMessageId($user_id);
        $inline_keyboard = new InlineKeyboard([]);
        $data_edit = ['chat_id' => $user_id];
        $data_edit['reply_markup'] = $inline_keyboard;
        $data_edit['message_id'] = $message_cart_id;
        Request::editMessageReplyMarkup($data_edit);

        $total = BotCartController::count_sum_total_without_cashback($user_id);
        $min_sum_order = (float)BotSettingsController::getSettings($user_id,'min_sum_order')['settings_value'];

        $user_cashback = BotCashbackController::getUserCashback($user_id);
        $user_cashback_action = BotCashbackController::getUserCashbackAction($user_id);

        if ($total >= 350) $pay_cashback = $user_cashback + $user_cashback_action >= $total / 2 ? bcdiv($total, '2', 2) : $user_cashback + $user_cashback_action;
        else $pay_cashback = $user_cashback >= $total / 2 ? bcdiv($total, '2', 2) : $user_cashback;

        $min_order_sum = BotSettingsCashback::get_min_order_sum();

        if (BotUsersNavController::getDeliveryYesOrNo($user_id) == 1) {
            if ($total - $pay_cashback < $min_order_sum) $pay_cashback = $total - $min_order_sum;
            if ($pay_cashback < 0) $pay_cashback = 0;
        }

        if ($user_cashback > 0 || $user_cashback_action > 0) {

            if ($total < 350 && $user_cashback_action > 0) {

                CashbackCommandController::show_message_action_no($user_id);

            }
            CashbackCommandController::show_message($user_id);
            BotUsersNavController::updateValue($user_id, 'change_key', 'cashback');

        }
        else {

            BotOrderController::select_date($user_id, 'send', null);

        }

    }

    public static function select_date($user_id, $act, $message_id)
    {

        $command = 'Order';

        $text = BotTextsController::getText($user_id, $command, 'select_date');

        $date_for_calendar = date('Y-m-d');
        $ym = date("Y-m", strtotime($date_for_calendar)) . '-';

        $days = BotDateTimeController::getArrDaysNotWork($user_id, $ym);
        foreach ($days as $day) {
            $text .= PHP_EOL . date("d.m.Y", strtotime($day['date'])) . ' - ' . $day['text'];
        }

        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = $text;
        $data_text['parse_mode'] = 'html';
        $data_text['reply_markup'] = BotOrderButtonsController::get_select_date_buttons($user_id);

        if ($act == 'send') {

            $remove_keyboard = StartCommandController::removeKeyboardBottom($user_id);

            $message_cart_id = BotUsersNavController::getCartMessageId($user_id);
            $inline_keyboard = new InlineKeyboard([]);
            $data_edit = ['chat_id' => $user_id];
            $data_edit['reply_markup'] = $inline_keyboard;
            $data_edit['message_id'] = $message_cart_id;
            Request::editMessageReplyMarkup($data_edit);
            $send_text = Request::sendMessage($data_text);

            if ($send_text->getResult() !== null) {
                $order_message_id = $send_text->getResult()->getMessageId();
                BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'order_message_id', $order_message_id);
            }

        } elseif ($act == 'edit') {

            $data_text['message_id'] = $message_id;
            Request::editMessageText($data_text);

        }

    }

    public static function get_calendar($user_id, $message_id, $date)
    {

        $command = 'Order';
        $text = BotTextsController::getText($user_id, $command, 'select_date');

        $date_for_calendar = $date !== null ? $date : date('Y-m-d');
        $ym = date("Y-m", strtotime($date_for_calendar));
        $ym .= '-';

        $days = BotDateTimeController::getArrDaysNotWork($user_id, $ym);
        foreach ($days as $day) {
            $text .= PHP_EOL . date("d.m.Y", strtotime($day['date'])) . ' - ' . $day['text'];
        }

        $data_edit = ['chat_id' => $user_id];
        $data_edit['text'] = $text;
        $data_edit['parse_mode'] = 'html';
        $data_edit['reply_markup'] = BotCalendarController::getCalendar($user_id, $date_for_calendar, $message_id);
        $data_edit['message_id'] = $message_id;
        Request::editMessageText($data_edit);

    }

    public static function select_time($user_id, $date, $message_id)
    {

        $date = $date == null ? BotUsersNavController::getValue($user_id, 'date') : $date;

        $time_close = BotSettingsController::getSettings($user_id, 'time_close')['settings_value'];
        $today_close = strtotime(date("Y-m-d") . ' ' . $time_close);

        if ($date == date("Y-m-d") && time() < $today_close) {
            self::edit_select_time($user_id, $date, $message_id);
        } elseif ($date !== date("Y-m-d")) {
            self::edit_select_time($user_id, $date, $message_id);
        } else {
            self::select_date($user_id, 'edit', $message_id);
        }

    }

    public static function edit_select_time($user_id, $date, $message_id)
    {

        BotUsersNavController::updateUsersNavDate($user_id, $date);
        $day = date("d.m.Y", strtotime($date));

        $command = 'Order';
        $text = BotTextsController::getText($user_id, $command, 'select_time');
        $text = str_replace("___DAY___", $day, $text);

//        $times_not_work = BotDateTimeController::getTimeNotWork($user_id);
//        $days = BotDateTimeController::getArrTimesNotWorkAllDays($user_id);
//        foreach ($days as $day) {
//            $text .= PHP_EOL.$day['time_start'].'-'.$day['time_end'].' - '.$day['text'];
//        }

        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = $text;
        $data_text['parse_mode'] = 'html';
        $data_text['reply_markup'] = BotOrderButtonsController::get_select_time_buttons($user_id, $date);
        $data_text['message_id'] = $message_id;
        Request::editMessageText($data_text);

    }

    public static function select_payment($user_id, $time, $message_id)
    {

        $time = $time == null ? BotUsersNavController::getValue($user_id, 'time') : $time;

        BotUsersNavController::updateUsersNavTime($user_id, $time);
        $time_ins = date("H:i", strtotime($time));

        $day = date("d.m.Y", strtotime(BotUsersNavController::getValue($user_id, 'date')));

        $command = 'Order';
        $text = BotTextsController::getText($user_id, $command, 'select_payment');
        $text = str_replace("___DAY___", $day, $text);
        $text = str_replace("___TIME___", $time_ins, $text);

        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = $text;
        $data_text['parse_mode'] = 'html';
        $data_text['reply_markup'] = BotOrderButtonsController::get_select_payment_buttons($user_id);
        $data_text['message_id'] = $message_id;
        Request::editMessageText($data_text);

    }

    public static function select_nta($user_id, $payment_id, $act, $change, $message_id)
    {

        $day = date("d.m.Y", strtotime(BotUsersNavController::getValue($user_id, 'date')));
        $time = date("H:i", strtotime(BotUsersNavController::getValue($user_id, 'time')));

        $delivery_id = BotUsersNavController::getValue($user_id, 'delivery_id');

        $payment_id = $payment_id == null ? BotUsersNavController::getValue($user_id, 'payment_id') : $payment_id;
        BotUsersNavController::updateUsersNavPayment($user_id, $payment_id);

        $payment = BotSettingsPaymentsController::getPaymentText($user_id, $payment_id);
        $payment_emoji = BotSettingsPaymentsController::getPaymentEmoji($user_id, $payment_id);

        $command = 'Order';
        $text_date = BotTextsController::getText($user_id, $command, 'select_nta_date');
        $text_date = str_replace("___DAY___", $day, $text_date);
        $text_time = BotTextsController::getText($user_id, $command, 'select_nta_time');
        $text_time = str_replace("___TIME___", $time, $text_time);
        $text_payment = BotTextsController::getText($user_id, $command, 'select_nta_payment');
        $text_payment = $payment_emoji . str_replace("___PAYMENT___", $payment, $text_payment);

        $text = $text_date . PHP_EOL . $text_time . PHP_EOL . $text_payment;

        $inline_keyboard = new InlineKeyboard([]);

        $count = BotOrder::where('user_id', $user_id)->orderBy('id', 'desc')->count();
        $check_nta = BotUsersNavController::checkNamePhoneAddress($user_id);
        $first_time = BotUsersNavController::getValue($user_id, 'first_time');
        if ($first_time !== 1) {

            $inline_keyboard = $change == 'edit' ? BotOrderButtonsController::get_button_change_edit($user_id, $inline_keyboard) : $inline_keyboard;
            $text .= PHP_EOL;
            $order = BotOrder::where('user_id', $user_id)->where('delivery_id', $delivery_id)->first();
            $name = BotUsersNavController::getValue($user_id, 'name');
            if ($name !== null && $name !== '') {
                $name = BotUsersNavController::getAndUpdateValue($user_id, 'name', $order['order_name']);
                $text .= PHP_EOL . BotTextsController::getText($user_id, $command, 'order_name') . $name;
                $inline_keyboard = $change == 'nta' ? BotOrderButtonsController::get_button_change_name($user_id, $inline_keyboard) : $inline_keyboard;
            }
            $phone = BotUsersNavController::getValue($user_id, 'phone');
            if ($phone !== null && $phone !== '') {
                $phone = BotUsersNavController::getAndUpdateValue($user_id, 'phone', $order['order_phone']);
                $text .= PHP_EOL . BotTextsController::getText($user_id, $command, 'order_phone') . $phone;
                $inline_keyboard = $change == 'nta' ? BotOrderButtonsController::get_button_change_phone($user_id, $inline_keyboard) : $inline_keyboard;
            }
//            if (BotSettingsDeliveryController::checkDiscount($user_id) == null) {
//                $addr_nav = BotUsersNavController::getValue($user_id, 'addr');
//                $addr = $addr_nav !== null && $addr_nav !== '' ? $addr_nav : BotUsersNavController::getAndUpdateValue($user_id, 'addr', $order['order_addr']);
//                $text .= PHP_EOL . BotTextsController::getText($user_id, $command, 'order_addr') . $addr;
//                $inline_keyboard = $change == 'nta' ? BotOrderButtonsController::get_button_change_addr($user_id, $inline_keyboard) : $inline_keyboard;
//            } else {
//                $addr = BotSettingsDeliveryController::getAddr($user_id, $delivery_id);
//                $addr = BotUsersNavController::getAndUpdateValue($user_id, 'addr', $addr);
//                $text .= $delivery_id !== null ? PHP_EOL . BotTextsController::getText($user_id, $command, 'order_addr') . $addr : '';
//            }

            $inline_keyboard = BotOrderButtonsController::get_button_nta_next($user_id, $inline_keyboard);
            $inline_keyboard = $change == 'edit' ? BotOrderButtonsController::get_button_nta_back($user_id, $inline_keyboard) : BotOrderButtonsController::get_button_nta_edit_back($user_id, $inline_keyboard);

            if (BotUsersNavController::getValue($user_id, 'addr') == null) {
                if (BotUsersNavController::getDeliveryYesOrNo($user_id) == 1) {
                    $inline_keyboard = new InlineKeyboard([]);
                    self::change_addr($user_id);
                }
            }

        }


        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = $text;
        $data_text['parse_mode'] = 'html';
        $data_text['reply_markup'] = $inline_keyboard;

        if ($act == 'send') {

            $remove_keyboard = StartCommandController::removeKeyboardBottom($user_id);

            $send_text = Request::sendMessage($data_text);

            if ($send_text->getResult() !== null) {

                $order_message_id = $send_text->getResult()->getMessageId();
                BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'order_message_id', $order_message_id);

            }

        } elseif ($act == 'edit') {

            $data_text['message_id'] = $message_id;
            Request::editMessageText($data_text);

        }

        if ($first_time == 1) {
            self::change_name($user_id);
        }

    }

    public static function change_name($user_id)
    {

        $command = 'Order';

        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = BotTextsController::getText($user_id, $command, 'order_change_name');
        $data_text['parse_mode'] = 'html';

        $keyboard_bottom = new Keyboard([]);

        $values = self::getDistinctValuesFromOrders($user_id, 'name');

        $value = BotUsersNavController::getValue($user_id, 'name');
        if ($value !== null && $value !== '' && !in_array($value, $values)) $keyboard_bottom->addRow($value);

        foreach ($values as $value) {
            $keyboard_bottom->addRow($value);
        }
        $keyboard_b = $keyboard_bottom
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->setSelective(false);
        $data_text['reply_markup'] = $keyboard_b;

        $send_text = Request::sendMessage($data_text);
        BotUsersNavController::updateValue($user_id, 'change_key', 'name');

    }

    public static function change_phone($user_id)
    {

        $command = 'Order';

        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = BotTextsController::getText($user_id, $command, 'order_change_phone');
        $data_text['parse_mode'] = 'html';

        $keyboard_bottom = new Keyboard([]);

        $buttons = BotButtonsController::getButtons($user_id, 'Order', ['send_contact']);
        foreach ($buttons as $button) {
            $keyboard_bottom->addRow((new KeyboardButton($button))->setRequestContact(true));
        }

        $values = self::getDistinctValuesFromOrders($user_id, 'phone');
        $value = BotUsersNavController::getValue($user_id, 'phone');
        if ($value !== null && $value !== '' && !in_array($value, $values)) $keyboard_bottom->addRow($value);

        foreach ($values as $value) {
            $keyboard_bottom->addRow($value);
        }
        $keyboard_b = $keyboard_bottom
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->setSelective(false);
        $data_text['reply_markup'] = $keyboard_b;

        $send_text = Request::sendMessage($data_text);
        BotUsersNavController::updateValue($user_id, 'change_key', 'phone');

    }

    public static function change_addr($user_id)
    {

        $command = 'Order';

        $text = BotTextsController::getText($user_id, $command, 'order_change_addr');
        $text = str_replace("___NAME___", BotUsersNavController::getValue($user_id, 'name'), $text);

        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = $text;
        $data_text['parse_mode'] = 'html';

        $keyboard_bottom = new Keyboard([]);

//        $buttons = BotButtonsController::getButtons($user_id, 'Order', ['send_location']);
//        foreach ($buttons as $button) {
//            $keyboard_bottom->addRow((new KeyboardButton($button))->setRequestLocation(true));
//        }

        $values = self::getDistinctValuesFromOrders($user_id, 'address');
        $value = BotUsersNavController::getValue($user_id, 'addr');
        if ($value !== null && $value !== '' && !in_array($value, $values)) $keyboard_bottom->addRow($value);

        foreach ($values as $value) {
            $keyboard_bottom->addRow($value);
        }
        $keyboard_b = $keyboard_bottom
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->setSelective(false);
        $data_text['reply_markup'] = $keyboard_b;

        $send_text = Request::sendMessage($data_text);
        BotUsersNavController::updateValue($user_id, 'change_key', 'addr');
        BotButtonsInlineController::deleteInlineKeyboardFromMessageId($user_id, 'order_message_id');

    }

    public static function getDistinctValuesFromOrders($user_id, $key)
    {

        $arr = [];
        if ($key == 'address') $values = BotOrder::where('user_id', $user_id)->where('delivery_id', 1)->distinct($key)->groupBy($key)->get();
        else $values = BotOrder::where('user_id', $user_id)->distinct($key)->groupBy($key)->get();

        foreach ($values as $value) {
            if ($value[$key] !== null && $value[$key] !== '') $arr[] = $value[$key];
        }
        return $arr;
    }

    public static function change_no_call($user_id)
    {

        $payment_id = BotUsersNavController::getValue($user_id, 'payment_id');
        if (SimplaOrdersController::getSumFromOrdersSuccess($user_id) > 200 || BotSettingsPaymentsController::getPaymentType($payment_id) == 'card') {

            $command = 'Order';

            $text = BotTextsController::getText($user_id, $command, 'order_change_no_call');

            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = $text;
            $data_text['parse_mode'] = 'html';

            $keyboard_bottom = new Keyboard([]);

            $buttons = BotButtonsController::getButtons($user_id, 'Order', ['call', 'no_call']);
            foreach ($buttons as $button) {
                $keyboard_bottom->addRow($button);
            }

            $keyboard_b = $keyboard_bottom
                ->setResizeKeyboard(true)
                ->setOneTimeKeyboard(true)
                ->setSelective(false);
            $data_text['reply_markup'] = $keyboard_b;

            $send_text = Request::sendMessage($data_text);
            BotUsersNavController::updateValue($user_id, 'change_key', 'no_call');

            BotButtonsInlineController::deleteInlineKeyboardFromMessageId($user_id, 'order_message_id');

        }
        else self::change_sushi_sticks($user_id);

    }

    public static function change_from($user_id)
    {

        $command = 'Order';

        $payment_id = BotUsersNavController::getValue($user_id, 'payment_id');
        if (BotSettingsPaymentsController::getPaymentType($payment_id) == 'cash') {

            $text = BotTextsController::getText($user_id, $command, 'order_change_from');

            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = $text;
            $data_text['parse_mode'] = 'html';

            $keyboard_bottom = new Keyboard([]);

            $buttons = BotButtonsController::getButtons($user_id, 'Order', ['no_change_from']);
            foreach ($buttons as $button) {
                $keyboard_bottom->addRow($button);
            }

            $keyboard_b = $keyboard_bottom
                ->setResizeKeyboard(true)
                ->setOneTimeKeyboard(true)
                ->setSelective(false);
            $data_text['reply_markup'] = $keyboard_b;

            $send_text = Request::sendMessage($data_text);
            BotUsersNavController::updateValue($user_id, 'change_key', 'change_from');

            BotButtonsInlineController::deleteInlineKeyboardFromMessageId($user_id, 'order_message_id');

        }
        else self::change_sushi_sticks($user_id);

    }

    public static function change_sushi_sticks($user_id)
    {

        $command = 'Order';

        if (BotCartController::checkProductsSushiInCart($user_id) > 0) {

            $text = BotTextsController::getText($user_id, $command, 'order_sushi_sticks');
            $sushi_stikcks_no = BotTextsController::getText($user_id, $command, 'order_sushi_sticks_no');

            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = $text;
            $data_text['parse_mode'] = 'html';

            $keyboard_bottom = new Keyboard([]);

            $keyboard_bottom->addRow('1', '2', '3', '4');
            $keyboard_bottom->addRow('5', '6', '7', '8');
            $keyboard_bottom->addRow('9', '10', '11', '12');
            $keyboard_bottom->addRow('13', '13', '15', '16');
            $keyboard_bottom->addRow($sushi_stikcks_no);

            $keyboard_b = $keyboard_bottom
                ->setResizeKeyboard(true)
                ->setOneTimeKeyboard(true)
                ->setSelective(false);
            $data_text['reply_markup'] = $keyboard_b;

            $send_text = Request::sendMessage($data_text);
            BotUsersNavController::updateValue($user_id, 'change_key', 'sushi_sticks');

            BotButtonsInlineController::deleteInlineKeyboardFromMessageId($user_id, 'order_message_id');

        }
        else self::change_comment($user_id);

    }

    public static function change_comment($user_id)
    {

        $command = 'Order';

        $no_call = BotUsersNavController::getValue($user_id, 'no_call');
        if ($no_call == 1) {
            $check_discount = BotUsersNavController::get_delivery_from_user_id($user_id)['discount'];
            if ($check_discount == null) {
                $text = BotTextsController::getText($user_id, $command, 'order_change_comment_no_call');
            }
            else $text = BotTextsController::getText($user_id, $command, 'order_change_comment');
        }
        else $text = BotTextsController::getText($user_id, $command, 'order_change_comment');

        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = $text;
        $data_text['parse_mode'] = 'html';

        $keyboard_bottom = new Keyboard([]);

        $buttons = BotButtonsController::getButtons($user_id, 'Order', ['no_comments']);
        foreach ($buttons as $button) {
            $keyboard_bottom->addRow($button);
        }

        $keyboard_b = $keyboard_bottom
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->setSelective(false);
        $data_text['reply_markup'] = $keyboard_b;

        $send_text = Request::sendMessage($data_text);
        BotUsersNavController::updateValue($user_id, 'change_key', 'comment');

        BotButtonsInlineController::deleteInlineKeyboardFromMessageId($user_id, 'order_message_id');

    }

    public static function change_contactless($user_id)
    {

        if (BotSettingsDeliveryController::checkDiscount($user_id) == null) {

            $command = 'Order';

            $text = BotTextsController::getText($user_id, $command, 'order_change_contactless');

            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = $text;
            $data_text['parse_mode'] = 'html';

            $keyboard_bottom = new Keyboard([]);

            $buttons = BotButtonsController::getButtons($user_id, 'Order', ['contactless', 'skip']);
            foreach ($buttons as $button) {
                $keyboard_bottom->addRow($button);
            }

            $keyboard_b = $keyboard_bottom
                ->setResizeKeyboard(true)
                ->setOneTimeKeyboard(true)
                ->setSelective(false);
            $data_text['reply_markup'] = $keyboard_b;

            $send_text = Request::sendMessage($data_text);
            BotUsersNavController::updateValue($user_id, 'change_key', 'contactless');

        }
        else {

            self::show_order_before_success_card($user_id);
//            self::show_order_finish($user_id, 'send', null);

        }

    }

    public static function change_contactless_comment($user_id)
    {

        $command = 'Order';

        $text = BotTextsController::getText($user_id, $command, 'order_change_contactless_comment');

        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = $text;
        $data_text['parse_mode'] = 'html';

        $keyboard_bottom = new Keyboard([]);

        $buttons = BotButtonsController::getButtons($user_id, 'Order', ['no_contactless_comment']);
        foreach ($buttons as $button) {
            $keyboard_bottom->addRow($button);
        }

        $keyboard_b = $keyboard_bottom
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->setSelective(false);
        $data_text['reply_markup'] = $keyboard_b;

        $send_text = Request::sendMessage($data_text);
        BotUsersNavController::updateValue($user_id, 'change_key', 'contactless_comment');

    }

    public static function show_order_before_success_card($user_id)
    {

        $command = 'Order';

        $text = BotTextsController::getText($user_id, $command, 'order_before_success_card');

        $inline_keyboard = new InlineKeyboard([]);
        $inline_keyboard = BotOrderButtonsController::get_button_order_before_success_card($user_id, $inline_keyboard);

        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = $text;
        $data_text['parse_mode'] = 'html';
        $data_text['reply_markup'] = $inline_keyboard;
        $send_text = Request::sendMessage($data_text);

    }

    public static function show_order_finish($user_id, $act, $message_id)
    {

        if ($message_id !== null) {

            $data_text = ['chat_id' => $user_id];
            $inline_keyboard = new InlineKeyboard([]);
            $data_text['reply_markup'] = $inline_keyboard;
            $data_text['message_id'] = $message_id;
            $send_text = Request::editMessageReplyMarkup($data_text);

        }

        $day = date("d.m.Y", strtotime(BotUsersNavController::getValue($user_id, 'date')));
        $time = date("H:i", strtotime(BotUsersNavController::getValue($user_id, 'time')));

        $payment_id = BotUsersNavController::getValue($user_id, 'payment_id');
        $payment = BotSettingsPaymentsController::getPaymentText($user_id, $payment_id);
        $payment_emoji = BotSettingsPaymentsController::getPaymentEmoji($user_id, $payment_id);

        $command = 'Order';
        $text_date = BotTextsController::getText($user_id, $command, 'select_nta_date');
        $text_date = str_replace("___DAY___", $day, $text_date);
        $text_time = BotTextsController::getText($user_id, $command, 'select_nta_time');
        $text_time = str_replace("___TIME___", $time, $text_time);
        $text_payment = BotTextsController::getText($user_id, $command, 'select_nta_payment');
        $text_payment = $payment_emoji . str_replace("___PAYMENT___", $payment, $text_payment);

        $text = BotTextsController::getText($user_id, $command, 'order_finish');

        $currency = BotTextsController::getText($user_id, 'System', 'currency');
        $text .= PHP_EOL . PHP_EOL . BotCartController::count_cart_total($user_id, $currency);

        $text .= PHP_EOL . PHP_EOL . $text_date . PHP_EOL . $text_time . PHP_EOL . $text_payment;

//        $text .= PHP_EOL;
        $order = BotOrder::where('user_id', $user_id)->orderBy('id', 'desc')->first();
        $name = BotUsersNavController::getValue($user_id, 'name');
        $text .= PHP_EOL . BotTextsController::getText($user_id, $command, 'order_name') . $name;
        $phone = BotUsersNavController::getValue($user_id, 'phone');
        $text .= PHP_EOL . BotTextsController::getText($user_id, $command, 'order_phone') . $phone;

        $city_id = BotUser::getValue($user_id, 'city_id');
        $city_id = $city_id !== null && is_numeric($city_id) ? $city_id : 6;
        $city_name = SimplaRegionsController::getRegionNameFromId($user_id, $city_id);


        $city_id = BotUser::getValue($user_id, 'city_id');
        $city_id = $city_id !== null && is_numeric($city_id) ? $city_id : 6;
        $city_name = SimplaRegionsController::getRegionNameFromId($user_id, $city_id);

        if (BotSettingsDeliveryController::checkDiscount($user_id) == null) {
            $addr = BotUsersNavController::getAndUpdateValue($user_id, 'addr', $order['order_addr']);
            $text .= PHP_EOL . BotTextsController::getText($user_id, $command, 'order_addr') . $city_name . ', ' . $addr;
        } else {
            $delivery_id = BotUsersNavController::getValue($user_id, 'delivery_id');
            $addr = BotSettingsDeliveryController::getAddr($user_id, $delivery_id);

//            $data_t = ['chat_id' => $user_id];
//            $data_t['text'] = 'debug: '.$delivery_id.'; '.$addr.'; ';
//            $send_t = Request::sendMessage($data_t);

            $addr = BotUsersNavController::getAndUpdateValue($user_id, 'addr', $addr);
            $text .= $delivery_id !== null ? PHP_EOL . PHP_EOL . BotTextsController::getText($user_id, $command, 'order_addr') . $city_name . ', ' . $addr : '';

        }

        $no_call = BotUsersNavController::getValue($user_id, 'no_call');
        $text .= $no_call == 1 ? PHP_EOL . PHP_EOL . BotTextsController::getText($user_id, $command, 'order_no_call') : '';

        $comment = BotUsersNavController::getValue($user_id, 'comment');
        $text .= PHP_EOL . PHP_EOL . BotTextsController::getText($user_id, $command, 'order_comment') . $comment;

        $payment_type = BotSettingsPaymentsController::getPaymentType($payment_id);
//        if ($payment_type == 'card') {
//            if (BotSettingsDeliveryController::checkDiscount($user_id) == null) {
//                $contactless = BotUsersNavController::getValue($user_id, 'contactless');
//                if ($contactless == 1) {
//                    $text .= PHP_EOL . PHP_EOL . BotTextsController::getText($user_id, $command, 'order_contactless') . BotTextsController::getText($user_id, $command, 'order_contactless_yes');
//                    $text .= PHP_EOL . PHP_EOL . BotTextsController::getText($user_id, $command, 'order_contactless_comment') . BotUsersNavController::getValue($user_id, 'contactless_comment');
//                } else {
//                    $text .= PHP_EOL . PHP_EOL . BotTextsController::getText($user_id, $command, 'order_contactless') . BotTextsController::getText($user_id, $command, 'order_contactless_no');
//                }
//            }
//        }

        $inline_keyboard = new InlineKeyboard([]);
        $inline_keyboard = BotOrderButtonsController::get_button_order_success($user_id, $inline_keyboard);
//        $inline_keyboard = BotOrderButtonsController::get_button_change_edit($user_id, $inline_keyboard);
        $inline_keyboard = BotOrderButtonsController::get_button_nta_back($user_id, $inline_keyboard);

        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = $text;
        $data_text['parse_mode'] = 'html';
        $data_text['reply_markup'] = $inline_keyboard;

        if ($act == 'send') {

            $remove_keyboard = StartCommandController::removeKeyboardBottom($user_id);

//            $data_open = ['chat_id' => $user_id];
//            $data_open['text'] = BotTextsController::getText($user_id, 'Cart', 'open_cart');
//            $data_open['parse_mode'] = 'html';
//            $data_open['reply_markup'] = ['keyboard' => $keyboard_bottom, 'remove_keyboard' => true, 'selective' => true];
//            $send_sec = Request::sendMessage($data_open);

            $send_text = Request::sendMessage($data_text);

            if ($send_text->getResult() !== null) {

                $order_message_id = $send_text->getResult()->getMessageId();
                BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'order_message_id', $order_message_id);

            }

        } elseif ($act == 'edit') {

            $data_text['message_id'] = $message_id;
            Request::editMessageText($data_text);

        }

    }

    public static function getValuesForOrder($user_id, $simpla_id)
    {

        $delivery_id = BotUsersNavController::getValue($user_id, 'delivery_id');
        $delivery = BotSettingsDeliveryController::getTextValue($user_id, $delivery_id);

        $payment_id = BotUsersNavController::getValue($user_id, 'payment_id');
        $payment = BotSettingsPaymentsController::getPaymentTextDefault($payment_id);

        $name = BotUsersNavController::getValue($user_id, 'name');

        $phone = BotUsersNavController::getValue($user_id, 'phone');

        $addr = BotUsersNavController::getValue($user_id, 'addr');

        $order_date = BotUsersNavController::getValue($user_id, 'date');
        $odate = date("d.m.Y", strtotime($order_date));

        $time = BotUsersNavController::getValue($user_id, 'time');
        $time_arr = explode(":", $time);
        $ins_hour = $time_arr[0];
        $ins_min = $time_arr[1];

        $comment = BotUsersNavController::getValue($user_id, 'comment');

        $sum = BotCartController::count_sum($user_id);
        $delivery_price = BotCartController::count_delivery($user_id, $sum);

        $total = BotCartController::count_sum_total($user_id);

        $cashback = BotCartController::count_cashback($user_id);

        $latitude = BotUsersNavController::getValue($user_id, 'latitude');
        $longitude = BotUsersNavController::getValue($user_id, 'longitude');

        return [
            'simpla_id' => $simpla_id,
            'user_id' => $user_id,
            'order_name' => $name,
            'order_city' => '',
            'order_delivery' => $delivery,
            'order_delivery_id' => $delivery_id,
            'order_addr' => $addr,
            'order_phone' => $phone,
            'order_longitude' => $longitude,
            'order_latitude' => $latitude,
            'order_price' => $total,
            'order_delivery_date' => $odate,
            'order_delivery_time' => $time,
            'order_oplata' => $payment,
            'order_payment_id' => $payment_id,
            'order_comment' => $comment,
            'order_oplata_yes' => 0,
            'order_yes' => 0,
            'order_date_reg' => date("Y-m-d H:i:s"),
            'order_date_edit' => date("Y-m-d H:i:s")
        ];

    }

    public static function add_order($user_id, $telegram, $callback_query)
    {

        $simpla_id = SimplaOrdersController::add_order($user_id);

        if ($simpla_id !== null && $simpla_id > 0) {

            $order_arr = self::getValuesForOrder($user_id, $simpla_id);
            $order_id = BotOrder::insertGetId($order_arr);

            $products = BotCartController::getProductsInCart($user_id);
            foreach ($products as $product) {

                $orders = [
                    'id_order' => $order_id,
                    'id_user' => $user_id,
                    'category' => $product['category'],
                    'id_tovar' => $product['id_tovar'],
                    'id_size' => $product['id_size'],
                    'product_name' => $product['product_name'],
                    'variant_name' => BotCartController::getProductVariant($product['id_size'])['name'],
                    'quantity' => $product['quantity'],
                    'price' => $product['price'],
                    'price_all' => $product['price_all'],
                    'vendor_code' => $product['vendor_code'],
                    'action_pizza' => $product['action_pizza'],
                    'product_present' => $product['product_present'],
                    'date_reg' => date("Y-m-d H:i:s")
                ];
                $orders_id = BotOrderContent::insertGetId($orders);
//                if (BotOrders::insertGetId($orders) == null) return null;

                $purchases = [
                    'order_id' => $simpla_id,
                    'product_id' => $product['id_tovar'],
                    'variant_id' => $product['id_size'],
                    'product_name' => $product['product_name'],
                    'variant_name' => BotCartController::getProductVariant($product['id_size'])['name'],
                    'price' => $product['price'],
                    'amount' => $product['quantity'],
//                    'complect_code' => $bortik_ins
                ];
                $purchases_id = SimplaPurchasesController::add_purchase($purchases);

                $bortik_ins = '';
                $bortiks = BotCartController::getBortikProductInCart($user_id, $product['id']);
                $i = 0;
                foreach ($bortiks as $bortik) {

                    $i++;
                    $bortik_ins .= $i > 1 ? ';' : '';
                    $bortik_ins .= $bortik['id_size'].':'.$bortik['quantity'];

                    $complects = [
                        'order_id' => $simpla_id,
                        'product_id' => $purchases_id,
                        'variant_id' => $bortik['id_size'],
                        'product_name' => $bortik['product_name'],
                        'variant_name' => BotCartController::getProductVariant($bortik['id_size'])['name'],
                        'price' => $bortik['price'],
                        'amount' => $bortik['quantity']
                    ];
                    SimplaPurchasesComplects::insertGetId($complects);

                    $orders_bortik = [
                        'id_order' => $order_id,
                        'id_user' => $user_id,
                        'category' => $bortik['category'],
                        'parent_product_id' => $orders_id,
                        'id_tovar' => $bortik['id_tovar'],
                        'id_size' => $bortik['id_size'],
                        'product_name' => $bortik['product_name'],
                        'variant_name' => BotCartController::getProductVariant($bortik['id_size'])['name'],
                        'quantity' => $bortik['quantity'],
                        'price' => $bortik['price'],
                        'price_all' => $bortik['price_all'],
                        'vendor_code' => $bortik['vendor_code'],
                        'action_pizza' => $bortik['action_pizza'],
                        'product_present' => $bortik['product_present'],
                        'date_reg' => date("Y-m-d H:i:s")
                    ];
                    $orders_id = BotOrderContent::insertGetId($orders_bortik);

                }
                SimplaPurchases::where('id', $purchases_id)->update(['complect_code' => $bortik_ins]);

                //                if (SimplaPurchasesController::add_purchase($purchases) == null) return null;

            }

            self::sendToArchi($user_id, $simpla_id);

            $command = 'Order';
            $currency = BotTextsController::getText($user_id, 'System', 'currency');
            $total_price = self::getOrderFromSimplaId($user_id, $simpla_id)['order_price'];
            $text = BotTextsController::getText($user_id, $command, 'order_success');
            $text .= BotUsersNavController::getValue($user_id, 'no_call') == 1 ? '' : PHP_EOL.BotTextsController::getText($user_id, $command, 'order_success_call');
            $text = str_replace("___ORDER_NUM___", $simpla_id, $text);
            $text = str_replace("___SUM___", $total_price.'' .$currency, $text);

            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = $text;
            $data_text['parse_mode'] = 'html';
            $send_t = Request::sendMessage($data_text);

            self::sendBotLocation($user_id, $simpla_id);

            BotButtonsInlineController::deleteInlineKeyboardFromMessageId($user_id, 'order_message_id');

            BotCashbackController::payCashback($user_id, $simpla_id);

            BotUsersNavController::updateValue($user_id, 'cart_message_id', null);
            BotUsersNavController::updateValue($user_id, 'order_message_id', null);
            BotUsersNavController::updateValue($user_id, 'delivery_id', null);
            BotUsersNavController::updateValue($user_id, 'date', null);
            BotUsersNavController::updateValue($user_id, 'time', null);
            BotUsersNavController::updateValue($user_id, 'payment_id', null);

            BotUsersNavController::updateValue($user_id, 'latitude', null);
            BotUsersNavController::updateValue($user_id, 'longitude', null);
            BotUsersNavController::updateValue($user_id, 'addr', null);
            BotUsersNavController::updateValue($user_id, 'change_from', null);
            BotUsersNavController::updateValue($user_id, 'sushi_sticks', null);
            BotUsersNavController::updateValue($user_id, 'comment', null);
            BotUsersNavController::updateValue($user_id, 'contactless', null);
            BotUsersNavController::updateValue($user_id, 'contactless_comment', null);
            BotUsersNavController::updateValue($user_id, 'no_call', null);
            BotUsersNavController::updateValue($user_id, 'birthday', null);

            BotUsersController::updateUsers($user_id, 'save_tree', 0);
            BotUsersController::updateUsers($user_id, 'go_from_cart', 0);
            BotUsersController::updateUsers($user_id, 'user_delivery', '');
            BotRaffleUsersController::updateRaffleTry($user_id);
            BotCartController::clear_cart_all($user_id);

            BotUsersNavController::updateValue($user_id, 'order_sent', 0);

            $payment_id = BotOrder::where('external_id', $simpla_id)->first()['payment_id'];
            $payment_type = BotSettingsPaymentsController::getPaymentType($payment_id);

            if ($payment_type == 'card') {
                WayForPayController::sendWidget($user_id, $simpla_id);
//                LiqPayController::sendInvoice($user_id, $simpla_id);
//                CmdObjController::execute('precheckoutquery', $telegram, $callback_query);
            }
            else {
                Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);
                sleep(3);
                StartCommandController::send_hello($user_id);
            }

        } else return null;

    }

    public static function sendToArchi($user_id, $simpla_id)
    {

        $postfields = http_build_query(array(
            'simpla_id' => $simpla_id,
            'key' => env('SEND_TO_ARCHI_KEY')
        ));

        $url = env('SEND_TO_ARCHI_URL');

        $curlInit = curl_init($url);
        curl_setopt($curlInit, CURLOPT_POST, true);
        curl_setopt($curlInit, CURLOPT_POSTFIELDS, "simpla_id=" . $simpla_id . "&key=" . env('SEND_TO_ARCHI_KEY'));
        curl_setopt($curlInit, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curlInit);

        curl_close($curlInit);

//        $data_t = ['chat_id' => $user_id];
//        $data_t['text'] = 'debug: Archi response: ' . $response;
//        $send_t = Request::sendMessage($data_t);

        if ($response == 'ok') {

//            $data_t = ['chat_id' => $user_id];
//            $data_t['text'] = 'debug: Archi Ok';
//            $send_t = Request::sendMessage($data_t);
            BotUsersNavController::updateValue($user_id, 'sent_archi', 1);

        }

    }

    public static function getOrderFromSimplaId($user_id, $simpla_id)
    {

        return BotOrder::where('user_id', $user_id)->where('external_id', $simpla_id)->first();

    }

    public static function getOrderFromSimplaIdWithoutUserId($simpla_id)
    {

        return BotOrder::where('external_id', $simpla_id)->first();

    }

    public static function sendBotLocation($user_id, $simpla_id) {

        $order = self::getOrderFromSimplaId($user_id, $simpla_id);
        $delivery_id = $order['order_delivery_id'];

        $delivery = BotSettingsDeliveryController::getDeliveryFromDeliveryId($delivery_id);

        if ($delivery['discount'] == 1) {

            $addr = BotSettingsDeliveryController::getAddr($user_id, $delivery_id);
            $latitude = $delivery['latitude'];
            $longitude = $delivery['longitude'];

            $text = BotTextsController::getText($user_id, 'Order', 'send_location');
            $text = str_replace("___ADDR___", $addr, $text);

            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = $text;
            $data_text['parse_mode'] = 'html';
            $send_t = Request::sendMessage($data_text);

            $data_location = ['chat_id' => $user_id];
            $data_location['latitude'] = $latitude;
            $data_location['longitude'] = $longitude;
            Request::sendLocation($data_location);

            return true;
        }

        return null;

    }

    public static function countOrderFromUserId($user_id) {

        return BotOrder::where('user_id', $user_id)->count();

    }

    public static function getOrderFromUserId($user_id) {

        return BotOrder::where('user_id', $user_id)->orderBy('id', 'desc')->get();

    }

    public static function sendOrderForRepeat($user_id, $simpla_id) {

        $order_id = BotOrderController::getOrderFromSimplaId($user_id, $simpla_id)['id'];
        $ttt = BotCartController::getTextOrder($user_id, $simpla_id, $order_id);
        $data_t = ['chat_id' => $user_id];
        $data_t['text'] = $ttt;
        $data_t['reply_markup'] = BotCartButtonsController::order_repeat_buttons($user_id, $order_id);
        $data_t['parse_mode'] = 'html';
        $send_t = Request::sendMessage($data_t);

    }

    public static function repeatOrder($user_id, $order_id) {

        BotCartController::clear_cart($user_id);

        $products = BotCartController::getProductsInOrder($user_id, $order_id);

        $order = BotOrder::where('id', $order_id)->where('user_id', $user_id)->first();

        foreach ($products as $product) {

            if ($product['action_pizza'] == 0 && $product['product_present'] == 0) {

                $simpla_product =BotMenuController::get_product_sql($user_id, $product['id_tovar']);
                if ($simpla_product['no_actions'] == 0 && $simpla_product['visible'] == 1) {

                    $variant = BotCartController::getProductVariant($product['id_size']);
                    $price = number_format(round($variant['price'], 2), 2);
                    $product_price_all = number_format(round($product['quantity'] * $variant['price'], 2), 2);

                    $orders = [
                        'id_user' => $user_id,
                        'category' => $simpla_product['url'],
                        'id_tovar' => $product['id_tovar'],
                        'id_size' => $product['id_size'],
                        'product_name' => $simpla_product['name'],
                        'variant_name' => $variant['name'],
                        'quantity' => $product['quantity'],
                        'price' => $price,
                        'price_all' => $product_price_all,
                        'date_reg' => date("Y-m-d H:i:s"),
                        'date_edit' => date("Y-m-d H:i:s")
                    ];
                    $cart_id = BotCart::insertGetId($orders);

                    $bortiks_in_cart = BotCartController::findBortiksInOrderFromProductCardId($user_id, $product['id']);
                    foreach ($bortiks_in_cart as $bortik_in_cart) {

                        $simpla_product =BotMenuController::get_product_sql($user_id, $bortik_in_cart['id_tovar']);
                        $variant_bortik = BotCartController::getProductVariant($bortik_in_cart['id_size']);
                        $bortik_price_all = number_format(round($product['quantity'] * $variant_bortik['price'], 2), 2);

                        $orders_bortik = [
                            'id_user' => $user_id,
                            'category' => $simpla_product['url'],
                            'parent_product_id' => $cart_id,
                            'id_tovar' => $bortik_in_cart['id_tovar'],
                            'id_size' => $bortik_in_cart['id_size'],
                            'product_name' => $simpla_product['name'],
                            'variant_name' => $variant_bortik['name'],
                            'quantity' => $bortik_in_cart['quantity'],
                            'price' => $variant_bortik['price'],
                            'price_all' => $bortik_price_all,
                            'date_reg' => date("Y-m-d H:i:s"),
                            'date_edit' => date("Y-m-d H:i:s")
                        ];

                        $bortiks_id = BotCart::insertGetId($orders_bortik);

                    }

                }

            }

        }

        $addr = $order['order_addr'];
        if ($addr !== '') {
            BotUsersNavController::updateValue($user_id, 'addr', $addr);
        }

        CartCommandController::execute($user_id, 'send', null);

    }

    public static function getLastOrderFromUserId($user_id) {

        return BotOrdersNew::where('user_id', $user_id)->orderBy('id', 'desc')->first();

    }

}
