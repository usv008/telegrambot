<?php

namespace App\Http\Controllers;

use App\Models\BotChatMessages;
use App\Models\BotOrder;
use App\Models\BotReviews;
use Illuminate\Http\Request as LRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Input;

use Longman\TelegramBot\Request;

use Illuminate\Support\Facades\DB;
use Longman\TelegramBot\Telegram;

use DataTables;

use App\Models\BotFeedBack;

class BotReviewsController extends Controller
{

    public static function show_reviews() {

        if (view()->exists('admin.bot')) {

            $num = BotReviews::count();

            $messages_unreaded = BotChatMessages::getMessagesUnreaded();
            $data = [
//                'data' => $users,
                'title' => 'Отзывы',
                'num' => $num,
                'page' => 'reviews',
                'messages_unreaded' => $messages_unreaded,
            ];

            return view('admin.bot', $data);

        }

    }

    public static function reviews_list() {

        $data = BotReviews::all();
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
                    ->addColumn('status_change', function($row){
                        $status = $row->status == 'ok' ? '<img src="'.asset('assets/img/yes.png').'" width="30" />' : '<img src="'.asset('assets/img/no.png').'" width="30" />';
                        $result = '<a href="#change" class="change_status" id="status_'.$row->id.'">'.$status.'</a>';
                        return $result;
                    })
                    ->addColumn('delete', function($row){
                        $result = Gate::allows('reviews_delete') ? '<button type="button" id="review_delete_'.$row->id.'" class="btn btn-danger mb-2 mt-2 review_delete"><small>Удалить</small></button>' : '';
                        return $result;
                    })
                    ->rawColumns(['user', 'status_change', 'delete'])
            ->make(true);
    }

    public static function change_status(LRequest $request) {

        $input = $request->except('_token');

        $status_and_id = explode("_", $input['id']) ? explode("_", $input['id']) : null;

        $id = isset($status_and_id[1]) ? $status_and_id[1] : null;

        if (is_numeric($id) && $id > 0) {

            $status = BotReviews::find($id)['status'];
            $status_new = $status == 'ok' ? '' : 'ok';

            BotReviews::where('id', $id)->update(['status' => $status_new]);

            return $status_new == 'ok' ? '<img src="'.asset('assets/img/yes.png').'" width="30" />' : '<img src="'.asset('assets/img/no.png').'" width="30" />';

        }
        else return '---';

    }

    public static function review_delete(LRequest $request) {

        $input = $request->except('_token');

        $delete_and_id = explode("_", $input['id']) ? explode("_", $input['id']) : null;

        $id = isset($delete_and_id[2]) ? $delete_and_id[2] : null;

        if (is_numeric($id) && $id > 0) {

            $data = [
                'id' => $id,
            ];
            return view('admin.review_delete', $data);

        }
        else return '---';

    }

    public static function review_delete_yes(LRequest $request) {

        $input = $request->except('_token');

        if (is_numeric($input['id']) && $input['id'] > 0) {

            BotReviews::where('id', $input['id'])->delete();

        }

        return redirect()->route('reviews');

    }

}
