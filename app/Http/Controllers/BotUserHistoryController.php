<?php

namespace App\Http\Controllers;

use App\Models\BotChatMessages;
use App\Models\BotOrder;
use App\Models\BotReviews;
use App\Models\BotUserHistory;
use Illuminate\Http\Request as LRequest;
use Illuminate\Support\Facades\Input;

use Longman\TelegramBot\Request;

use Illuminate\Support\Facades\DB;
use Longman\TelegramBot\Telegram;

use DataTables;

use App\Models\BotUserHistoryOld;

class BotUserHistoryController extends Controller
{

    public static function show_history() {

        if (view()->exists('admin.bot')) {

            $messages_unreaded = BotChatMessages::getMessagesUnreaded();

            $data = [
                'title' => 'История пользователей за неделю',
                'page' => 'users_history',
                'messages_unreaded' => $messages_unreaded,
            ];

            return view('admin.bot', $data);

        }

    }

    public static function users_history_list() {

        $day = date("Y-m-d");
        $filter = date("Y-m-d 00:00:00", strtotime("-1 week", strtotime($day)));

        $data = BotUserHistory::leftJoin('bot_user', 'bot_user.user_id', 'bot_user_history_new.user_id')
            ->where('date_z', '>=', $filter)
            ->get([
                'bot_user_history_new.id as id',
                'bot_user_history_new.user_id as user_id',
                'bot_user_history_new.type as type',
                'bot_user_history_new.user_event as user_event',
                'bot_user_history_new.date_z as date_z',
                'bot_user.username as username',
                'bot_user.first_name as first_name',
                'bot_user.last_name as last_name'
            ]);
//        $data = BotOrder::selectRaw('bot_order.*, (select (summa) from bot_cashback_history where bot_cashback_history.order_id = bot_order.id and bot_cashback_history.type = \'OUT\') as cashback_out, (select (summa) from bot_cashback_history where bot_cashback_history.order_id = bot_order.id and bot_cashback_history.type = \'IN\') as cashback_in')
//            ->selectRaw('bot_order.*, count(bot_cashback_history.type) as cashback_out')
//            ->groupBy('bot_cashback_history.id')
//            ->where('bot_order.id', '5123')
//            ->take(10)
//            ->get();

//        dd($data);
        return Datatables::of($data)
            ->addIndexColumn()
//                    ->addColumn('order', function($row){
//                        $order_id = BotOrder::where('simpla_id', $row->order_id)->first('id')['id'];
//                        $result = '<a href="#order" id="'.$order_id.'" class="order">'.$order_id.'</a>';
//                        return $result;
//                    })
                    ->addColumn('user', function($row){
                        $result = '<a href="'.route('user', array('user'=>$row->user_id)).'">'.$row->user_id.'</a>';
                        return $result;
                    })
                    ->addColumn('fio', function($row){
                        $username = $row->username !== '' && $row->username !== null ? '<br />('.$row->username.')' : '';
                        $result = $row->first_name.' '.$row->last_name.$username;
                        return $result;
                    })
                    ->addColumn('type', function($row){
                        $result = $row->type;
                        return $result;
                    })
                    ->addColumn('user_event', function($row){
                        $result = str_replace(array("\r\n", "\r", "\n"), '<br>', $row->user_event);
                        return $result;
                    })
//                    ->addColumn('order_cb_out', function($row){
//                        $result = $row->cashback_out !== null && $row->cashback_out > 0 ? '-'.$row->cashback_out : '';
//                        return $result;
//                    })
//                    ->addColumn('order_cb_in', function($row){
//                        $result = $row->cashback_in !== null && $row->cashback_in > 0 ? '+'.$row->cashback_in : '';
//                        return $result;
//                    })
//                    ->addColumn('order_date', function($row){
//                        $result = date("d.m.Y H:i:s", strtotime($row->order_date_reg));
//                        return $result;
//                    })
                    ->rawColumns(['user', 'fio', 'type', 'user_event'])
            ->make(true);
    }

}
