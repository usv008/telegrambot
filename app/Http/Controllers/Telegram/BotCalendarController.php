<?php

namespace App\Http\Controllers\Telegram;

use Illuminate\Http\Request as LRequest;
use App\Http\Controllers\Controller;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

use Longman\TelegramBot\Request;

class BotCalendarController extends Controller
{
    public static function getCalendar($user_id, $date_for_calendar, $message_id) {

        $time_close = BotSettingsController::getSettings($user_id, 'time_close')['settings_value'];
        $today_close = strtotime(date("Y-m-d").' '.$time_close);
        $time = time();

        $month = date("m", strtotime($date_for_calendar));
        $year = date("Y", strtotime($date_for_calendar));
        $day = (int)date("Y") == (int)$year && (int)date("m") == (int)$month ? (int)date("j", strtotime(date("Y-m-d"))) : 1;
        $days_of_month = date('t', strtotime($date_for_calendar));
        $date_calendar = $year.'-'.$month.'-'.$day;

        $inline_keyboard = new InlineKeyboard([]);

        if (strtotime(date("Y-m").'-01') < strtotime($year.'-'.$month.'-01')) {
            list($text_prev, $data_prev) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'order_calendar_prev');
            $data_prev .= date("Y-m-d", strtotime($year.'-'.$month."-01-1 month"));
        }
        else {
            $text_prev = ' ';
            $data_prev = 'no'.time();
        }

        list($text_next, $data_next) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'order_calendar_next');
        $data_next .= date("Y-m-d", strtotime($year.'-'.$month."-01+1 month"));

        $inline_keyboard->addRow(
            new InlineKeyboardButton(['text' => $text_prev, 'callback_data' => $data_prev]) ,
            new InlineKeyboardButton(['text' => BotDateTimeController::getMonth($user_id, (int)$month), 'callback_data' => 'no']),
            new InlineKeyboardButton(['text' => $text_next, 'callback_data' => $data_next])
        );

        $i = 0;
        $ins = [];
        $week = BotDateTimeController::getDaysOfWeek($user_id);
        foreach ($week as $value) {
            $i++;
            $ins[$i] = new InlineKeyboardButton(['text' => $value, 'callback_data' => 'no'.$i]);
        }
        $inline_keyboard->addRow($ins[1], $ins[2], $ins[3], $ins[4], $ins[5], $ins[6], $ins[7]);

        $day_ins = $day;
        $day_check = 0;
        $stop = 0;

        $ym = $year.'-'.$month.'-';
        $days_not_work = BotDateTimeController::getDaysNotWork($user_id, $ym);

//        $data_t = ['chat_id' => $user_id];
//        $data_t['text'] = 'debug: '.PHP_EOL.implode(";", $days_not_work).PHP_EOL.$time_close.'; '.date("H:i:s", strtotime(date("H:i:s", strtotime($time_close)).'+1 minutes'));
//        $send_t = Request::sendMessage($data_t);

        while ($stop == 0) {

            $ins = [];
            for ($i = 1; $i <= 7; $i++) {

                $day_check++;
                if ($day_check >= date('N', strtotime($date_calendar)) && $day_ins <= $days_of_month) {
                    if (!in_array(date("Y-m-d", strtotime($year.'-'.$month.'-'.$day_ins)), $days_not_work) && (int)date("Y") == (int)$year && (int)date("m") == (int)$month && (int)date("d") == (int)$day_ins) {
                        if ($time < $today_close) {
                            $value = $day_ins;
                            $data = 'order_select_day___'.$year.'-'.$month.'-'.$day_ins;
                            $day_ins++;
                        }
                        else {
                            $value = ' ';
                            $data = 'no'.time();
                            $day_ins++;
                        }
                    }
                    else {
                        if (!in_array(date("Y-m-d", strtotime($year.'-'.$month.'-'.$day_ins)), $days_not_work)) {
                            $value = $day_ins;
                            $data = 'order_select_day___'.$year.'-'.$month.'-'.$day_ins;
                            $day_ins++;
                        }
                        else {
                            $value = ' ';
                            $data = 'no'.time();
                            $day_ins++;
                        }
                    }
                }
                else {
                    $value = ' ';
                    $data = 'no'.time();
                }
                $ins[$i] = new InlineKeyboardButton(['text' => $value, 'callback_data' => $data]);

                $stop = $i == 7 && $day_ins >= $days_of_month ? 1 : 0;

            }
            $inline_keyboard->addRow($ins[1], $ins[2], $ins[3], $ins[4], $ins[5], $ins[6], $ins[7]);

        }

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'order_calendar_back');
        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]) );

        return $inline_keyboard;

    }

}
