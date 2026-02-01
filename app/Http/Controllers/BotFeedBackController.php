<?php

namespace App\Http\Controllers;

use App\Models\BotChatMessages;
use App\Models\BotOrder;
use App\Models\BotFeedBack;
use Illuminate\Http\Request as LRequest;
use Illuminate\Support\Facades\Input;

use Longman\TelegramBot\Request;

use Illuminate\Support\Facades\DB;
use Longman\TelegramBot\Telegram;

use DataTables;


class BotFeedBackController extends Controller
{

    public static function show_feedback() {

        if (view()->exists('admin.bot')) {

            $num_users = BotFeedBack::count();
            $nps0 = 0; $nps1 = 0; $nps2 = 0; $nps3 = 0;
            $n01 = 0; $n02 = 0; $n11 = 0; $n12 = 0; $n21 = 0; $n22 = 0; $n31 = 0; $n32 = 0;
            $n0 = BotFeedBack::where('o0', '!=', null)->count() > 0 ? 0 : 1; $n1 = 0; $n2 = 0; $n3 = 0;
            $rows = BotFeedBack::all();
            foreach ($rows as $key => $value) {

                $row = $rows[$key];
                if ($row['o0'] !== '' && $row['o0'] > 0 && $row['o0'] <= 6) { $n01++; $n0++;}
                if ($row['o0'] !== '' && $row['o0'] > 8 && $row['o0'] <= 10) { $n02++; $n0++; }

                if ($row['o1'] !== '' && $row['o1'] > 0 && $row['o1'] <= 6) { $n11++; $n1++;}
                if ($row['o1'] !== '' && $row['o1'] > 8 && $row['o1'] <= 10) { $n12++; $n1++; }

                if ($row['o2'] !== '' && $row['o2'] > 0 && $row['o2'] <= 6) { $n21++; $n2++; }
                if ($row['o2'] !== '' && $row['o2'] > 8 && $row['o2'] <= 10) { $n22++; $n2++; }

                if ($row['o3'] !== '' && $row['o3'] > 0 && $row['o3'] <= 6) { $n31++; $n3++; }
                if ($row['o3'] !== '' && $row['o3'] > 8 && $row['o3'] <= 10) { $n32++; $n3++; }

            }
            $nps0 = ($n02/$n0)-($n01/$n0); $nps0 = round($nps0 * 100, 2);
            $nps1 = ($n12/$n1)-($n11/$n1); $nps1 = round($nps1 * 100, 2);
            $nps2 = ($n22/$n2)-($n21/$n2); $nps2 = round($nps2 * 100, 2);
            $nps3 = ($n32/$n3)-($n31/$n3); $nps3 = round($nps3 * 100, 2);

            $messages_unreaded = BotChatMessages::getMessagesUnreaded();

            $data = [
//                'data' => $users,
                'title' => 'Оценки',
                'nps0' => $nps0,
                'nps1' => $nps1,
                'nps2' => $nps2,
                'nps3' => $nps3,
                'num_users' => $num_users,
                'page' => 'feedback',
                'messages_unreaded' => $messages_unreaded,
            ];

            return view('admin.bot', $data);

        }

    }

    public static function feedback_list() {

//        $data = BotFeedBack::all();
//        $data = BotOrder::selectRaw('bot_order.*, (select (summa) from bot_cashback_history where bot_cashback_history.order_id = bot_order.id and bot_cashback_history.type = \'OUT\') as cashback_out, (select (summa) from bot_cashback_history where bot_cashback_history.order_id = bot_order.id and bot_cashback_history.type = \'IN\') as cashback_in')
//            ->selectRaw('bot_order.*, count(bot_cashback_history.type) as cashback_out')
//            ->groupBy('bot_cashback_history.id')
//            ->where('bot_order.id', '5123')
//            ->take(10)
//            ->get();
        $data = BotFeedBack::leftJoin('bot_orders_new', 'bot_feedback.order_id', 'bot_orders_new.external_id')
            ->select([
                'bot_feedback.user_id',
                'bot_feedback.order_id',
                'bot_feedback.o0',
                'bot_feedback.o1',
                'bot_feedback.o2',
                'bot_feedback.o3',
                'bot_feedback.comment',
                'bot_feedback.date_reg',
                'bot_feedback.date_edit',
                'bot_orders_new.external_id',
                'bot_orders_new.id as order_id',
            ])
            ->get();
//        dd($data);
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('order', function($row){
                $order_id = $row->order_id;
                if ($order_id) {
                    $result = '<a href="#order" id="'.$order_id.'" class="order">'.$order_id.'</a>';
                    return $result;
                }
                else return '';
            })
            ->addColumn('user', function($row){
                $result = '<a href="'.route('user', array('user'=>$row->user_id)).'">'.$row->user_id.'</a>';
                return $result;
            })
//                    ->addColumn('order_name', function($row){
//                        $result = '<a href="'.route('user', array('user'=>$row->user_id)).'">'.$row->order_name.'</a>';
//                        return $result;
//                    })
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
//                    ->addColumn('order_cb_in', function($row){
//                        $result = $row->cashback_summa !== null && $row->cashback_summa > 0 && $row->cashback_type == 'IN' ? '-'.$row->cashback_summa : '';
//                        return $result;
//                    })
//                    ->addColumn('order_delete', function($row){
//                        $result = '<a href="#delete">Удалить</a>';
//                        return $result;
//                    })
            ->rawColumns(['order', 'user'])
            ->make(true);
    }

}
