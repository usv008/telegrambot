<?php

namespace App\Http\Controllers;

use App\Models\BotChatMessages;
use App\Models\BotOrder;
use App\Models\BotOrdersNew;
use App\Models\BotReviews;
use App\Models\BotUser;
use App\Models\BotUserHistory;
use App\Models\BotUserHistoryOld;
use Illuminate\Http\Request as LRequest;
use Illuminate\Support\Facades\Input;

use Longman\TelegramBot\Request;

use Illuminate\Support\Facades\DB;
use Longman\TelegramBot\Telegram;

use DataTables;

use App\Models\BotFeedBack;

class BotStatController extends Controller
{

    public static function show_stat(LRequest $request)
    {

        if (view()->exists('admin.bot')) {

            $input = $request->except('_token');

            $date_start = isset($input['date_start']) && $input['date_start'] !== null ? date("Y-m-d", strtotime($input['date_start'])) : date("Y-m-d", strtotime("-1 month", time()));
            $date_end = isset($input['date_end']) && $input['date_end'] !== null ? date("Y-m-d", strtotime($input['date_end'])) : date("Y-m-d", strtotime("+1 day", time()));

            $days_week = ['пн', 'вт', 'ср', 'чт', 'пт', 'сб', 'вс'];
            $days = '';
            $users = '';
            $orders = '';

            $stats = BotUserHistory::selectRaw('date(date_z) as days, count(distinct(bot_user_history_new.user_id)) as users, (select count(bot_orders_new.id) from bot_orders_new where date(bot_orders_new.created_at) = days) as orders')
                ->where('bot_user_history_new.date_z', '>=', $date_start)
                ->where('bot_user_history_new.date_z', '<=', $date_end)
                ->distinct('days')
                ->groupBy('days')
                ->get();

            $i = 0;
            foreach ($stats as $stat) {

                $i++;
                $day_week = date("N", strtotime($stat->days));
                $day_count = '"' . date("d.m", strtotime($stat->days)) . ' (' . $days_week[$day_week - 1] . ')"';
                $days .= $i == 1 ? $day_count : ', ' . $day_count;
                $users .= $i == 1 ? $stat->users : ', ' . $stat->users;
                $orders .= $i == 1 ? $stat->orders : ', ' . $stat->orders;

            }

            $messages_unreaded = BotChatMessages::getMessagesUnreaded();

            $data = [
                'title' => 'Статистика',
                'page' => 'stat',
                'date_start' => $date_start,
                'date_end' => $date_end,
                'days' => $days,
                'users' => $users,
                'orders' => $orders,
                'messages_unreaded' => $messages_unreaded,
            ];

            return view('admin.bot', $data);

        }
    }

    public static function show_new_users_stat(LRequest $request) {

        if (view()->exists('admin.bot')) {

            $input = $request->except('_token');

            $date_start = isset($input['date_start']) && $input['date_start'] !== null ? date("Y-m-d", strtotime($input['date_start'])) : date("Y-m-d", strtotime("-1 month", time()));
            $date_end = isset($input['date_end']) && $input['date_end'] !== null ? date("Y-m-d", strtotime($input['date_end'])) : date("Y-m-d", strtotime("+1 day", time()));

            $users = BotUser::where('created_at', '>=', $date_start.' 00:00:00')->where('created_at', '<=', $date_end.' 23:59:59')->get();
            $orders = BotOrdersNew::where('created_at', '>=', $date_start.' 00:00:00')->where('created_at', '<=', $date_end.' 23:59:59')->get();

            foreach ($users as $user) {
                echo $user->created_at.'; '.$user->user_id.'; '.$orders->where('user_id', $user->user_id)->count().'; '.$orders->where('user_id', $user->user_id)->sum('price_with_discount').'<br />';
            }
            dd($users, $orders);

            dd($date_start, $date_end);

            $days_week = ['пн', 'вт', 'ср', 'чт', 'пт', 'сб', 'вс'];
            $days = '';
            $users = '';
            $orders = '';

            $stats = BotUserHistory::selectRaw('date(date_z) as days, count(distinct(bot_user_history_new.user_id)) as users, (select count(bot_orders_new.id) from bot_orders_new where date(bot_orders_new.created_at) = days) as orders')
                ->where('bot_user_history_new.date_z', '>=', $date_start)
                ->where('bot_user_history_new.date_z', '<=', $date_end)
                ->distinct('days')
                ->groupBy('days')
                ->get();

            $i = 0;
            foreach ($stats as $stat) {

                $i++;
                $day_week = date("N",  strtotime($stat->days));
                $day_count = '"' . date("d.m", strtotime($stat->days)) . ' (' . $days_week[$day_week-1] . ')"';
                $days .= $i == 1 ? $day_count : ', '.$day_count;
                $users .= $i == 1 ? $stat->users : ', '.$stat->users;
                $orders .= $i == 1 ? $stat->orders : ', '.$stat->orders;

            }

            $messages_unreaded = BotChatMessages::getMessagesUnreaded();

            $data = [
                'title' => 'Статистика',
                'page' => 'stat',
                'date_start' => $date_start,
                'date_end' => $date_end,
                'days' => $days,
                'users' => $users,
                'orders' => $orders,
                'messages_unreaded' => $messages_unreaded,
            ];

            return view('admin.bot', $data);

        }

    }

}
