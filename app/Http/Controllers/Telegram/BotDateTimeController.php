<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotSettingsDate;
use App\Models\BotSettingsDateNotWork;
use App\Models\BotUser;
use App\Models\BotUsersNav;
use App\Models\EcoPizzaTimeNotAccept;
use Illuminate\Http\Request as LRequest;
use App\Http\Controllers\Controller;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

use App\Models\BotSettingsButtonsInline;
use Longman\TelegramBot\Request;

class BotDateTimeController extends Controller
{

    public static function getDayOfWeek($user_id, $day) {

        $command = 'DateTime';

        $lang = BotUserSettingsController::getLang($user_id);

        $day = date('N', strtotime($day));
//        $day = $day == 0 ? 7 : $day;

        $week = [];
        for($i = 1; $i <= 7; $i++) {
            $week[$i] = BotSettingsDate::where('text_command', $command)->where('text_name', 'day_s_'.$i)->first()['text_value_'.$lang];
        }

        return $week[$day];

    }

    public static function getDaysOfWeek($user_id) {

        $command = 'DateTime';

        $lang = BotUserSettingsController::getLang($user_id);

        $week = [];
        $days = BotSettingsDate::where('text_command', $command)->where('text_name', 'like', 'day_s_%')->get();
        foreach ($days as $day) {
            $week[] = $day['text_value_'.$lang];
        }

        return $week;

    }

    public static function getMonth($user_id, $month)
    {

        $command = 'DateTime';
        $lang = BotUserSettingsController::getLang($user_id);
        return $month !== null && is_numeric($month) ? BotSettingsDate::where('text_command', $command)->where('text_name', 'month_'.(int)$month)->first()['text_value_'.$lang] : null;

    }

    public static function getDaysNotWork($user_id, $ym)
    {

        $arr = [];
        $days = BotSettingsDateNotWork::where('date', 'like', $ym.'%')->where('time_start', null)->where('time_end', null)->orderBy('date')->get();
        foreach ($days as $day) {
            $arr[$day['id']] = $day['date'];
        }

        return $arr;

    }

    public static function getArrDaysNotWork($user_id, $ym)
    {

        $lang = BotUserSettingsController::getLang($user_id);

        $arr = [];
        $days = BotSettingsDateNotWork::where('date', 'like', $ym.'%')->where('time_start', null)->where('time_end', null)->orderBy('date')->get();
        foreach ($days as $day) {
            $arr[$day['id']] = ['date' => $day['date'], 'text' => $day['text_value_'.$lang]];
        }

        return $arr;

    }

    public static function getTimeNotWork($user_id)
    {

        $check_discount = BotSettingsDeliveryController::checkDiscount($user_id);

        $arr = [];
        $days = BotSettingsDateNotWork::where('date', null)->where('enabled', 1)->orderBy('time_start')->get();
        foreach ($days as $day) {
            $time_start = strtotime($day['time_start']);
            $time_end = strtotime($day['time_end']);
            $time_count = $time_start;
            while ($time_count <= $time_end) {
                $time_count_ins = $check_discount == null ? strtotime(date("H:i:s", $time_count).'+1 hour') : $time_count_ins = $time_count;
                $arr[] = date("H:i:s", $time_count_ins);
                $time_count = strtotime(date("H:i:s", $time_count).'+1 minutes');
            }
        }

        return $arr;

    }

    public static function getTimeNotAccept($user_id, $day)
    {

        // смотрим какой регион пользователь выбрал
        $region_id = BotUser::getValue($user_id, 'city_id');

        switch ($region_id) {
            // Днепр = 6 региону в симпле, значит присваеваем точку 5 (Днепр) в Арчи
            case 6:
                $point_id = 5;
                break;
            // Киев = 6 региону в симпле, значит присваеваем точку 15 (Киев) в Арчи
            case 7:
                $point_id = 15;
                break;
            // По умолчанию ставим Днепр
            default:
                $point_id = 5;
        }

//        $check_discount = BotSettingsDeliveryController::checkDiscount($user_id);
        $arr = [];
        $times = EcoPizzaTimeNotAccept::where('date', $day)->where('point_id', $point_id)->get();
        foreach ($times as $time) {
            $time_start = strtotime($time['time']);
            $time_end = strtotime($time['time'].'+14 minutes');
            $time_count = $time_start;
            while ($time_count <= $time_end) {
//                $time_count_ins = $check_discount == null ? strtotime(date("H:i:s", $time_count).'+1 hour') : $time_count_ins = $time_count;
//                $time_count_ins = $check_discount == null ? strtotime(date("H:i:s", $time_count)) : $time_count_ins = $time_count;
                $time_count_ins = strtotime(date("H:i:s", $time_count));
                $arr[] = date("H:i:s", $time_count_ins);
                $time_count = strtotime(date("H:i:s", $time_count).'+1 minutes');
            }
        }

        return $arr;

    }

    public static function getArrTimesNotWorkAllDays($user_id)
    {

        $lang = BotUserSettingsController::getLang($user_id);

        $arr = [];
        $days = BotSettingsDateNotWork::where('date', null)->orderBy('time_start')->get();
        foreach ($days as $day) {
            $arr[$day['id']] = ['time_start' => date("H:i", strtotime($day['time_start'])), 'time_end' => date("H:i", strtotime($day['time_end'])), 'text' => $day['text_value_'.$lang]];
        }

        return $arr;

    }

}

