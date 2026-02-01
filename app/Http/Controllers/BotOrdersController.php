<?php

namespace App\Http\Controllers;

use App\Models\BotCashbackHistory;
use App\Models\BotChatMessages;
use App\Models\BotOrder;
use App\Models\BotOrderContent;
use App\Models\BotOrders;
use App\Models\BotOrdersNew;
use DataTables;
use Illuminate\Http\Request as LRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class BotOrdersController extends Controller
{

    public static function show_orders() {

        if (view()->exists('admin.bot')) {

            $messages_unreaded = BotChatMessages::getMessagesUnreaded();
            $data = [
//                'data' => $users,
                'title' => 'Заказы',
                'page' => 'orders',
                'messages_unreaded' => $messages_unreaded,
            ];

            return view('admin.bot', $data);

        }

    }

    public static function orders_list() {

        $data = BotOrder::selectRaw(
            'bot_orders_new.*,
            (select SUM(summa) from bot_cashback_history where bot_cashback_history.order_id = bot_orders_new.id and bot_cashback_history.type = \'OUT\') as cashback_out,
            (select SUM(summa) from bot_cashback_history where bot_cashback_history.order_id = bot_orders_new.id and bot_cashback_history.type = \'IN\') as cashback_in'
        )
            ->orderBy('id', 'desc')
//            ->take(10)
            ->get();

//        dd($data);
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('order_id', function($row){
                $result = '<a href="#order" id="'.$row->id.'" class="order">'.$row->id.'</a>';
                return $result;
            })
            ->addColumn('name', function($row){
                $result = '<a href="'.route('user', array('user'=>$row->user_id)).'">'.$row->name.'</a>';
                return $result;
            })
            ->addColumn('cb_out', function($row){
                $result = $row->cashback_out !== null && $row->cashback_out > 0 ? '-'.$row->cashback_out : '';
                return $result;
            })
            ->addColumn('cb_in', function($row){
                $result = $row->cashback_in !== null && $row->cashback_in > 0 ? '+'.$row->cashback_in : '';
                return $result;
            })
//            ->addColumn('date', function($row){
////                        $result = date("d.m.Y H:i:s", strtotime($row->order_date_reg));
//                $result = $row->order_date_reg;
//                return $result;
//            })
//                    ->addColumn('order_cb_in', function($row){
//                        $result = $row->cashback_summa !== null && $row->cashback_summa > 0 && $row->cashback_type == 'IN' ? '-'.$row->cashback_summa : '';
//                        return $result;
//                    })
            ->addColumn('order_delete', function($row){
                $result = Gate::allows('orders_delete') ? '<button type="button" id="order_delete_'.$row->id.'" class="btn btn-danger mb-2 mt-2 order_delete"><small>Удалить</small></button>' : '';
                return $result;
            })
            ->rawColumns(['order_id', 'name', 'cb_out', 'cb_in', 'order_delete'])
            ->make(true);
    }

    public static function show_order(LRequest $request) {

        $input = $request->except('_token');

        if (view()->exists('admin.order_content')) {

            if ($input['order_id'] !== null && $input['order_id'] > 0) {

//                $data_order = BotOrder::leftJoin('bot_cashback_history', 'bot_cashback_history.order_id', 'bot_order.id')
//                $data_order = BotOrdersNew::selectRaw('bot_orders_new.*, (select (summa) from bot_cashback_history where bot_cashback_history.order_id = bot_orders_new.id and bot_cashback_history.type = \'OUT\') as cashback_out, (select (summa) from bot_cashback_history where bot_cashback_history.order_id = bot_orders_new.id and bot_cashback_history.type = \'IN\') as cashback_in')
//                    ->where('bot_orders_new.id', $input['order_id'])
////                    ->groupBy('bot_cashback_history.id')
//                    ->groupBy('bot_orders_new.id')
//                    ->first();
                $data_order = BotOrdersNew::find($input['order_id']);
                $cashback_in = BotCashbackHistory::where('order_id', $input['order_id'])->where('type', 'IN')->first();
                $cashback_out = BotCashbackHistory::where('order_id', $input['order_id'])->where('type', 'OUT')->first();
                $data_order->cashback_in = $cashback_in && $cashback_in->summa ? $cashback_in->summa : 0;
                $data_order->cashback_out = $cashback_out && $cashback_out->summa ? $cashback_out->summa : 0;
                Log::warning($data_order);
//                dd($data_order[0]->simpla_id);

                $data_order_content = BotOrderContent::where('order_id', $input['order_id'])->get();

                $order_id = $data_order !== null ? $input['order_id'] : 'не найден';

                $messages_unreaded = BotChatMessages::getMessagesUnreaded();
                $data = [
                    'data_order' => $data_order,
                    'data_order_content' => $data_order_content,
                    'title' => 'Заказ: '.$order_id,
                    'messages_unreaded' => $messages_unreaded,
                ];

                return view('admin.order_content', $data);

            }

        }
        return null;
    }

    public static function order_delete(LRequest $request) {

        $input = $request->except('_token');

        $delete_and_id = explode("_", $input['id']) ? explode("_", $input['id']) : null;

        $id = isset($delete_and_id[2]) ? $delete_and_id[2] : null;

        if (is_numeric($id) && $id > 0) {

            $data = [
                'id' => $id,
            ];
            return view('admin.order_delete', $data);

        }
        else return '---';

    }

    public static function order_delete_yes(LRequest $request) {

        $input = $request->except('_token');

        if (is_numeric($input['id']) && $input['id'] > 0) {

            BotOrder::where('id', $input['id'])->delete();

        }

        return redirect()->route('orders');

    }

    public static function user_orders_list(LRequest $request) {

        $user_id = $request->all()['user_id'];

//        $data = BotOrder::all();
//        (select (summa) from bot_cashback_history where bot_cashback_history.order_id = bot_order.id and bot_cashback_history.type = \'OUT\') as cashback_out,
//        (select (summa) from bot_cashback_history where bot_cashback_history.order_id = bot_order.id and bot_cashback_history.type = \'IN\') as cashback_in'

//        $data = BotOrder::leftJoin('bot_cashback_history', 'bot_cashback_history.order_id', 'bot_order.id')
//            ->where('bot_order.user_id', $user_id)
////            ->selectRaw('bot_order.*, count(bot_cashback_history.type) as cashback_out')
//            ->groupBy('bot_order.id')
////            ->where('bot_order.id', '5123')
//            ->take(10)
//            ->get(['bot_order.*', 'bot_cashback_history.summa as cashback_out']);

        $orders = BotOrder::where('user_id', $user_id)->get();
        $cbs = BotCashbackHistory::where('user_id', $user_id)->get();
        $orders = $orders->map(function ($item) use ($cbs) {
            $cb_in = isset($cbs->where('order_id', $item->id)->where('type', 'IN')->first()['summa']) ? $cbs->where('order_id', $item->id)->where('type', 'IN')->first()['summa'] : 0;
            $cb_out = isset($cbs->where('order_id', $item->id)->where('type', 'OUT')->first()['summa']) ? $cbs->where('order_id', $item->id)->where('type', 'OUT')->first()['summa'] : 0;
            $item->cashback_in = $cb_in !== null ? $cb_in : 0;
            $item->cashback_out = $cb_out !== null ? $cb_out : 0;
            return $item;
        });

//        dd($user_id, $orders);

        return Datatables::of($orders)
            ->addIndexColumn()
            ->addColumn('order_id', function($row){
                $result = '<a href="#order" id="'.$row->id.'" class="order">'.$row->id.'</a>';
                return $result;
            })
            ->addColumn('order_name', function($row){
                $result = ''.$row->order_name.'';
                return $result;
            })
            ->addColumn('order_cb_out', function($row){
                $result = $row->cashback_out !== null && $row->cashback_out > 0 ? '-'.$row->cashback_out : '';
                return $result;
            })
            ->addColumn('order_cb_in', function($row){
                $result = $row->cashback_in !== null && $row->cashback_in > 0 ? '+'.$row->cashback_in : '';
                return $result;
            })
            ->addColumn('order_date', function($row){
//                        $result = date("d.m.Y H:i:s", strtotime($row->order_date_reg));
                $result = $row->order_date_reg;
                return $result;
            })
//                    ->addColumn('order_cb_in', function($row){
//                        $result = $row->cashback_summa !== null && $row->cashback_summa > 0 && $row->cashback_type == 'IN' ? '-'.$row->cashback_summa : '';
//                        return $result;
//                    })
            ->rawColumns(['order_id', 'order_name', 'order_cb_out', 'order_cb_in', 'order_date'])
            ->make(true);
    }

}
