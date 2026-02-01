<?php

namespace App\Http\Controllers;

use App\Models\BotChatMessages;
use App\Models\BotEcoUser;
use App\Models\BotOrder;
use App\Models\BotOrdersNew;
use App\Models\BotRaffleUsersHistory;
use Illuminate\Http\Request as LRequest;
use Illuminate\Support\Facades\Input;

use Longman\TelegramBot\Request;

use DataTables;
use App\Models\BotUser;

use Illuminate\Support\Facades\DB;
use Longman\TelegramBot\Telegram;

class BotUsersController extends Controller
{

    public static function show_users(LRequest $request) {

        if (view()->exists('admin.bot')) {

            $noactive_users = isset($request) && isset($request->noactive_users) ? $request->noactive_users : 0;
            $all_users = isset($request) && isset($request->all_users) ? $request->all_users : 0;
            $messages_unreaded = BotChatMessages::getMessagesUnreaded();

            //            dd($noactive_users, $all_users);
            $data = [
//                'data' => $users,
                'title' => 'Пользователи',
                'page' => 'users',
                'noactive_users' => $noactive_users,
                'all_users' => $all_users,
                'messages_unreaded' => $messages_unreaded,
            ];

            return view('admin.bot', $data);

        }

    }

    public static function users_list(LRequest $request) {

        $noactive_users = isset($request) && isset($request->noactive_users) ? $request->noactive_users : 0;
        $all_users = isset($request) && isset($request->all_users) ? $request->all_users : 0;

        $data = BotUser::leftJoin('bot_orders_new', 'bot_orders_new.user_id', 'bot_user.user_id')
            ->selectRaw('bot_user.*, COUNT(bot_orders_new.user_id) as num_orders, SUM(bot_orders_new.price_with_discount) as sum_price, ROUND(AVG(bot_orders_new.price_with_discount),2) as avg_price, bot_orders_new.name, bot_orders_new.phone');

        if ($noactive_users == 1 && $all_users == 0)
            $data = $data->where('bot_user.active', 0);
        elseif ($all_users == 0 && $noactive_users == 0)
            $data = $data->where('bot_user.active', 1);

        $data = $data->groupBy('bot_user.user_id')
            ->get();

        return Datatables::of($data)
            ->addIndexColumn()
                    ->addColumn('fio', function($row){
                        $username = $row->username !== null && $row->username !== '' ? '<br />('.$row->username.')' : '';
                        $result = '<a href="'.route('user', array('user'=>$row->user_id)).'">'.$row->first_name . ' ' . $row->last_name . $username.'</a>';
                        return $result;
                    })
                    ->addColumn('city', function($row){
                        $result = 'Не выбран';
                        if ($row->city_id == 6) $result = 'Днепр';
                        elseif ($row->city_id == 7) $result = 'Киев';
                        return $result;
                    })
                    ->addColumn('active', function($row){
                        $result = '⛔️';
                        if ($row->active == 1) $result = '✅';
                        return $result;
                    })
                    ->rawColumns(['fio', 'city', 'active'])
            ->make(true);
    }

    public static function show_user($user) {

        if (view()->exists('admin.user')) {

            if ($user !== null && $user > 0) {

                $bot_user = BotUser::where('user_id', $user)->first();

                $user_id = $bot_user !== null ? $user : 'не найден';

                $telegram = new Telegram(env('PHP_TELEGRAM_BOT_API_KEY'), env('PHP_TELEGRAM_BOT_NAME'));
                $data_photo = [
                    'user_id' => $user_id,
                    'limit'   => 100,
                    'offset'  => 0,
                ];

                $result = Request::getUserProfilePhotos($data_photo);
                $photos = $result->getOk() ? $result->getResult()->photos : [];

                $photo_arr = [];
                $photo_small_arr = [];
                foreach ($photos as $photo) {

                    $data_path = [
                        'file_id' => $photo[0]['file_id'],
                    ];
                    $file_path = Request::getFile($data_path);
                    if ($file_path->getOk()) $photo_small_arr[] = 'https://api.telegram.org/file/bot'.env('PHP_TELEGRAM_BOT_API_KEY').'/'.$file_path->getResult()->file_path;

                    $data_path = [
                        'file_id' => $photo[2]['file_id'],
                    ];
                    $file_path = Request::getFile($data_path);
                    if ($file_path->getOk()) $photo_arr[] = 'https://api.telegram.org/file/bot'.env('PHP_TELEGRAM_BOT_API_KEY').'/'.$file_path->getResult()->file_path;

                }

                $orders = BotOrdersNew::where('user_id', $user_id)->get();

                $share_user = BotRaffleUsersHistory::where('guest_user_id', $user_id)->first();
                $referrals = BotRaffleUsersHistory::where('user_id', $user_id)->get();

                $messages_unreaded = BotChatMessages::getMessagesUnreaded();

                $data = [
                    'data' => $bot_user,
                    'photos' => $photo_arr,
                    'photos_small' => $photo_small_arr,
                    'title' => 'Пользователь: '.$user_id,
                    'user_id' => $user_id,
                    'orders' => $orders,
                    'orders_count' => $orders->count(),
                    'share_user' => $share_user,
                    'referrals' => $referrals,
                    'messages_unreaded' => $messages_unreaded,
                ];

                return view('admin.user', $data);

            }

        }

    }

}
