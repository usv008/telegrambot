<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotCart;
use App\Models\BotMenu;
use App\Models\BotSettings;
use App\Models\BotUsersNav;
use App\Models\Simpla_Categories;
use App\Models\Simpla_Complect_Products;
use App\Models\Simpla_Images;
use App\Models\Simpla_Options;
use App\Models\Simpla_Products;
use App\Models\Simpla_Variants;
use Illuminate\Http\Request as LRequest;
use Longman\TelegramBot\Request;
use App\Http\Controllers\Controller;
use App\Models\BotSettingsButtons;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use PHPUnit\ExampleExtension\Comparable;

class BotOrderButtonsController extends Controller
{

    public static function get_select_date_buttons($user_id)
    {

        $command = 'Order';

        $time_open = BotSettingsController::getSettings($user_id, 'time_open')['settings_value'];
        $time_close = BotSettingsController::getSettings($user_id, 'time_close')['settings_value'];

        $inline_keyboard = new InlineKeyboard([]);

        $today = date("Y-m-d");
        $tomorrow = date("Y-m-d", strtotime($today . "+1 day"));
        $today_close = strtotime(date("Y-m-d") . ' ' . $time_close);
        $time = time();

        $ym = date("Y-m").'-';
        $days_not_work = BotDateTimeController::getDaysNotWork($user_id, $ym);

        if ($time < $today_close && !in_array(date("Y-m-d"), $days_not_work)) {
            $today_ins = BotTextsController::getText($user_id, $command, 'today') . ' - ' . date("d.m.Y") . ' (' . BotDateTimeController::getDayOfWeek($user_id, $today) . ')';
            $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $today_ins, 'callback_data' => 'order_select_day___' . $today]));
        }

        if (!in_array($tomorrow, $days_not_work)) {
            $tomorrow_ins = BotTextsController::getText($user_id, $command, 'tomorrow') . ' - ' . date("d.m.Y", strtotime($today . "+1 day")) . ' (' . BotDateTimeController::getDayOfWeek($user_id, $tomorrow) . ')';
            $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $tomorrow_ins, 'callback_data' => 'order_select_day___' . $tomorrow]));
        }

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'other_date');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'order_date_back');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_select_time_buttons($user_id, $day)
    {

        $inline_keyboard = new InlineKeyboard([]);

        $time_open = BotSettingsController::getSettings($user_id, 'time_open')['settings_value'];
        $time_close = BotSettingsController::getSettings($user_id, 'time_close')['settings_value'];
        $today = date("Y-m-d");
        $today_close = strtotime(date("Y-m-d") . ' ' . $time_close);
        $time = time();

        $h = date("H");
        $min = date("i");

        if ((int)$min > 0 && (int)$min <= 15) { $h = date("H"); $min = '15'; }
        if ((int)$min > 15 && (int)$min <= 30) {$h = date("H");  $min = '30'; }
        if ((int)$min > 30 && (int)$min <= 45) { $h = date("H"); $min = '45'; }
        if ((int)$min > 45 && (int)$min <= 59) { $h = date("H", strtotime(date("H:i:s").'+1 hour')); $min = '00'; }

        $check_discount = BotSettingsDeliveryController::checkDiscount($user_id);
        $time_plus = $check_discount == 1 ? '+30 minutes' : '+1 hour';

        $time_ins = time() <= strtotime($today.' '.$time_open.$time_plus) ? strtotime($today.' '.$time_open.$time_plus) : strtotime(date("Y-m-d").' '.$h.':'.$min.':00'.$time_plus);

        $time_start = strtotime($day) == strtotime($today) ? $time_ins : strtotime($day.' '.$time_open.$time_plus);
        $time_end = strtotime($day.' '.$time_close);
        $time_ins = $time_start;

        $times_not_work = BotDateTimeController::getTimeNotWork($user_id);
        $times_not_accept = BotDateTimeController::getTimeNotAccept($user_id, $day);

//        $data_t = ['chat_id' => $user_id];
//        $data_t['text'] = 'debug: '.PHP_EOL.date("H:i").':00'.PHP_EOL.implode(",", $times_not_accept);
//        $send_t = Request::sendMessage($data_t);

        $time_plus_hour = strtotime(date("Y-m-d H:i").':00'.'+55 minutes');
        if ($day == $today && $time >= strtotime($today.' '.$time_open) && $time <= strtotime($today.' '.$time_close.'-10 minutes') && !in_array(date("H:i:s", $time_plus_hour), $times_not_accept)) {
            $inline_keyboard->addRow(new InlineKeyboardButton(['text' => BotTextsController::getText($user_id, 'Order', 'set_time_button'), 'callback_data' => 'order_select_time___'.date("H:i:s", $time_plus_hour)]));
        }

        $i = 0;
        $ins = [];
        while ($time_ins <= $time_end) {
            if (!in_array(date("H:i:s", $time_ins), $times_not_work) && !in_array(date("H:i:s", $time_ins), $times_not_accept)) {
                $i++;
                $ins[$i] = new InlineKeyboardButton(['text' => date("H:i", $time_ins), 'callback_data' => 'order_select_time___'.date("H:i:s", $time_ins)]);
            }
            $time_ins = strtotime(date("Y-m-d H:i:s", $time_ins).'+15 minutes');
            if ($i == 4) {
                $inline_keyboard->addRow($ins[1], $ins[2], $ins[3], $ins[4]);
                $i = 0;
            }
        }
        if ($i == 3) $inline_keyboard->addRow($ins[1], $ins[2], $ins[3]);
        elseif ($i == 2) $inline_keyboard->addRow($ins[1], $ins[2]);
        elseif ($i == 1) $inline_keyboard->addRow($ins[1]);

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'order_time_back');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_select_payment_buttons($user_id) {

        $inline_keyboard = new InlineKeyboard([]);

        $payments = BotSettingsPaymentsController::getPayments($user_id);
        foreach ($payments as $id => $payment) {
            $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $payment, 'callback_data' => 'order_select_payment___'.$id]));
        }
        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'order_pay_back');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_button_change_edit($user_id, $inline_keyboard) {

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'order_change_edit');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_button_change_nta($user_id, $inline_keyboard) {

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'order_change_edit');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_button_change_name($user_id, $inline_keyboard) {

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'order_change_name');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_button_change_phone($user_id, $inline_keyboard) {

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'order_change_phone');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_button_change_addr($user_id, $inline_keyboard) {

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'order_change_addr');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_button_nta_next($user_id, $inline_keyboard) {

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'order_nta_next');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_button_nta_back($user_id, $inline_keyboard) {

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'order_nta_back');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_button_nta_edit_back($user_id, $inline_keyboard) {

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'order_nta_edit_back');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_button_order_before_success_card($user_id, $inline_keyboard) {

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'order_before_success_card');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_button_order_success($user_id, $inline_keyboard) {

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'order_success');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_button_goto_start($user_id, $inline_keyboard) {

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'goto_start');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

}
