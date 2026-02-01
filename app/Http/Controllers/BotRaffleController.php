<?php

namespace App\Http\Controllers;

use App\Models\BotChatMessages;
use App\Models\BotOrder;
use App\Models\BotRaffle;
use App\Models\BotRaffleUsers;
use App\Models\BotReviews;
use App\Models\Simpla_Products;
use Illuminate\Http\Request as LRequest;
use Illuminate\Support\Facades\Input;

use Longman\TelegramBot\Request;

use Illuminate\Support\Facades\DB;
use Longman\TelegramBot\Telegram;

use DataTables;

use App\Models\BotFeedBack;

class BotRaffleController extends Controller
{

    public static function show_raffle(LRequest $request) {

        if (view()->exists('admin.bot')) {

            $input = $request->except('_token');

            $wins = BotRaffle::where('win', 1)->count();
            $users_win = BotRaffle::where('win', 1)->distinct('user_id')->count('user_id');
            $attempts = BotRaffle::count();

            $procent = round(($wins/$attempts) * 100, 2);
            $messages_unreaded = BotChatMessages::getMessagesUnreaded();

//            dd($wins);

            $data = [
                'title' => 'Выиграй пиццу',
                'page' => 'raffle',
                'wins' => $wins,
                'users_win' => $users_win,
                'attempts' => $attempts,
                'procent' => $procent,
                'messages_unreaded' => $messages_unreaded,
            ];

            return view('admin.bot', $data);

        }

    }

    public static function raffle_list() {

        $data = BotRaffleUsers::join('bot_user', 'bot_user.user_id', 'bot_raffle_users.user_id')
            ->leftJoin('bot_raffle', 'bot_raffle.user_id', 'bot_raffle_users.user_id')
            ->leftJoin('bot_cart', function($join){
                $join->on('bot_cart.id_user', 'bot_raffle_users.user_id')
                    ->where('bot_cart.action_pizza', 1);
            })
            ->selectRaw('bot_raffle_users.*, bot_user.first_name as first_name, bot_user.last_name as last_name, bot_user.username as username, count(bot_raffle.id) as attempts, sum(bot_raffle.win) as wins, bot_cart.action_pizza as cart, (select count(bot_raffle_users_history.guest_user_id) from bot_raffle_users_history where bot_raffle_users_history.user_id = bot_raffle_users.user_id) as guests')
            ->groupBy('bot_raffle_users.user_id')
            ->get();
//        dd($data);
//        $data = BotOrder::selectRaw('bot_order.*, (select (summa) from bot_cashback_history where bot_cashback_history.order_id = bot_order.id and bot_cashback_history.type = \'OUT\') as cashback_out, (select (summa) from bot_cashback_history where bot_cashback_history.order_id = bot_order.id and bot_cashback_history.type = \'IN\') as cashback_in')
//            ->selectRaw('bot_order.*, count(bot_cashback_history.type) as cashback_out')
//            ->groupBy('bot_cashback_history.id')
//            ->where('bot_order.id', '5123')
//            ->take(10)
//            ->get();

//        dd($data);
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('user', function($row){
                $result = '<a href="'.route('user', array('user'=>$row->user_id)).'">'.$row->user_id.'</a>';
                return $result;
            })
            ->addColumn('fio', function($row){
                $username = $row->username !== null && $row->username !== '' ? '<br />('.$row->username.')' : '';
                $result = '<a href="'.route('user', array('user'=>$row->user_id)).'">'.$row->first_name . ' ' . $row->last_name . $username.'</a>';
                return $result;
            })
            ->addColumn('attempts', function($row){
                $result = '<a href="#attempts" class="attempts" id="user_id_'.$row->user_id.'" data-num="'.$row->attempts.'">'.$row->attempts.'</a>';
                return $result;
            })
            ->addColumn('raffle_try', function($row){
                $result = $row->raffle_try + $row->raffle_try_guest;
                return $result;
            })
            ->rawColumns(['user', 'attempts', 'fio'])
            ->make(true);
    }

    public static function raffle_attempts(LRequest $request) {

        $input = $request->except('_token');

        $arr_and_id = explode("_", $input['user_id']) ? explode("_", $input['user_id']) : null;

        $id = isset($arr_and_id[2]) ? $arr_and_id[2] : null;

        if (is_numeric($id) && $id > 0) {

            $raffles = BotRaffle::where('user_id', $id)->orderBy('id', 'desc')->get();

            $arr = [];
            foreach ($raffles as $raffle) {

                $k = 0;
                for ($i=1; $i<=12; $i++) {
                    $p = 'p'.$i;
                    if (strripos($raffle->$p, '___') !== false) $k++;
                }

                if ($k == 8) $arr[] = $raffle;

            }

            $data = [
                'id' => $id,
                'raffles' => $arr,
            ];
            return view('admin.raffle_user_content', $data);

        }
        else return '---';
    }

    public static function rafflePizzas()
    {
        $products = Simpla_Products::join('s_products_categories', 's_products_categories.product_id', 's_products.id')
            ->join('s_variants', 's_variants.product_id', 's_products.id')
            ->where('s_products_categories.category_id', 52)
            ->where('s_products.visible', 1)
            ->orderBy('s_products.position', 'asc')
            ->get([
                's_products.name',
                's_variants.name as variant_name',
                's_variants.sku',
                's_variants.price'
            ]);
        $data = [
            'products' => $products
        ];
        dd($products);
        return view('admin.raffle_pizzas_content', $data);
    }

}
