<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotSettingsDate;
use App\Models\BotUser;
use App\Models\EcoPizzaTimeNotAccept;
use App\Models\WorkingHoursClosure;
use App\Models\WorkingHoursSchedule;
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

        // Full-day closures from the closures table
        $closures = WorkingHoursClosure::where('active', 1)
            ->where('start_datetime', 'like', $ym.'%')
            ->orderBy('start_datetime')
            ->get();

        foreach ($closures as $closure) {
            $arr[$closure->id] = date('Y-m-d', strtotime($closure->start_datetime));
        }

        // Non-working days from weekly schedule
        $startDate = $ym . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));
        $currentDate = $startDate;

        while ($currentDate <= $endDate) {
            $dayOfWeek = (int) date('N', strtotime($currentDate)) - 1;
            $schedule = WorkingHoursSchedule::where('day_of_week', $dayOfWeek)->first();
            if ($schedule && !$schedule->is_working_day) {
                $arr['sched_' . $currentDate] = $currentDate;
            }
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }

        return $arr;
    }

    public static function getArrDaysNotWork($user_id, $ym)
    {
        $arr = [];
        $closures = WorkingHoursClosure::where('active', 1)
            ->where('start_datetime', 'like', $ym.'%')
            ->orderBy('start_datetime')
            ->get();

        foreach ($closures as $closure) {
            $arr[$closure->id] = [
                'date' => date('Y-m-d', strtotime($closure->start_datetime)),
                'text' => $closure->reason ?? '',
            ];
        }

        return $arr;
    }

    public static function getTimeNotWork($user_id)
    {
        $check_discount = BotSettingsDeliveryController::checkDiscount($user_id);

        $arr = [];

        // Get today's schedule from new working hours tables
        $dayOfWeek = (int) date('N') - 1;
        $schedule = WorkingHoursSchedule::where('day_of_week', $dayOfWeek)->first();

        if (!$schedule || !$schedule->is_working_day) {
            // Full day is non-working
            for ($h = 0; $h < 24; $h++) {
                for ($m = 0; $m < 60; $m++) {
                    $arr[] = sprintf('%02d:%02d:00', $h, $m);
                }
            }
            return $arr;
        }

        // Times before opening
        $openMinutes = (int) date('H', strtotime($schedule->open_time)) * 60 + (int) date('i', strtotime($schedule->open_time));
        for ($min = 0; $min < $openMinutes; $min++) {
            $time_val = sprintf('%02d:%02d:00', intdiv($min, 60), $min % 60);
            $time_ins = $check_discount == null ? date('H:i:s', strtotime($time_val . '+1 hour')) : $time_val;
            $arr[] = $time_ins;
        }

        // Times after closing
        $closeMinutes = (int) date('H', strtotime($schedule->close_time)) * 60 + (int) date('i', strtotime($schedule->close_time));
        for ($min = $closeMinutes; $min < 1440; $min++) {
            $time_val = sprintf('%02d:%02d:00', intdiv($min, 60), $min % 60);
            $time_ins = $check_discount == null ? date('H:i:s', strtotime($time_val . '+1 hour')) : $time_val;
            $arr[] = $time_ins;
        }

        // Times during active closures today
        $today = date('Y-m-d');
        $closures = WorkingHoursClosure::where('active', 1)
            ->where('start_datetime', '<=', $today . ' 23:59:59')
            ->where('end_datetime', '>=', $today . ' 00:00:00')
            ->get();

        foreach ($closures as $closure) {
            $closureStart = max(strtotime($closure->start_datetime), strtotime($today . ' 00:00:00'));
            $closureEnd = min(strtotime($closure->end_datetime), strtotime($today . ' 23:59:59'));
            $time_count = $closureStart;
            while ($time_count <= $closureEnd) {
                $time_count_ins = $check_discount == null ? strtotime(date("H:i:s", $time_count) . '+1 hour') : $time_count;
                $arr[] = date("H:i:s", $time_count_ins);
                $time_count = strtotime(date("H:i:s", $time_count) . '+1 minutes');
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
        $arr = [];

        // Get active/upcoming closures
        $closures = WorkingHoursClosure::where('active', 1)
            ->where('end_datetime', '>', now()->format('Y-m-d H:i:s'))
            ->orderBy('start_datetime')
            ->get();

        foreach ($closures as $closure) {
            $arr[$closure->id] = [
                'time_start' => date('H:i', strtotime($closure->start_datetime)),
                'time_end' => date('H:i', strtotime($closure->end_datetime)),
                'text' => $closure->reason ?? '',
            ];
        }

        return $arr;
    }

}

