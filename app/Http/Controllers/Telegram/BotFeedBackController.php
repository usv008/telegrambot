<?php

namespace App\Http\Controllers\Telegram;


use App\Models\BotFeedBack;

use App\Models\BotOrder;
use App\Http\Controllers\Controller;
use App\Models\BotOrdersNew;
use Illuminate\Support\Facades\Log;


class BotFeedBackController extends Controller
{

    public static function addFeedBack($user_id)
    {

        $date_z = date("Y-m-d H:i:s");
        $order_external_id = BotOrderController::getLastOrderFromUserId($user_id)['external_id'];

        if ($order_external_id !== null) {
            $feedback = new BotFeedBack;
            $feedback->user_id = $user_id;
            $feedback->order_id = $order_external_id;
            $feedback->date_reg = $date_z;
            $feedback->date_edit = $date_z;
            $feedback->save();

            if ($feedback->id !== null) {
                BotUsersNavController::updateValue($user_id, 'feedback_id', $feedback->id);
                return $feedback->id;
            }
            else return null;

        }
        else return null;

    }

    public static function updateFeedBack($user_id, $feedback_id, $key, $value) {

        BotFeedBack::where('id', $feedback_id)->where('user_id', $user_id)->update([$key => $value, 'date_edit' => date("Y-m-d H:i:s")]);

    }

    public static function sendFeedBack() {

        $day = date("Y-m-d", mktime(0,0,0, date("m"),date("d")-1, date("Y")));

        Log::info('--------------- START FEEDBACK --------------------------');
        Log::info('День: '.$day);
        $users = BotOrdersNew::where('created_at', 'like', $day.'%')->groupBy('user_id')->get(['user_id']);

        $date_z = date("Y-m-d H:i:s");
        $count = count($users);

        $send = 0;
        $send_no = 0;

        Log::info('Старт: '.$date_z);
        Log::info('Пользователей в очереди:  '.$count);

        $i = 0;
        foreach ($users as $user) {

            $i++;
            $user_id = $user->user_id;
//            $user_id = 522750680;
            $send = FeedBackCommandController::execute($user_id);
            if ($send == true) Log::info($i.') '.$user_id.' - ok');
            else Log::info($i.') '.$user_id.' - error');

        }
        Log::info('Стоп: '.date("Y-m-d H:i:s"));
        Log::info('--------------- END FEEDBACK --------------------------');

    }

//    public static function sendFeedBack() {
//
//        $day = date("Y-m-d", mktime(0,0,0, date("m"),date("d")-1, date("Y")));
//
//        Log::info('--------------- START FEEDBACK --------------------------');
//        Log::info('День: '.$day);
////        $users = [522750680];
//        $users = BotOrder::where('order_date_reg', 'like', $day.'%')->groupBy('user_id')->get(['user_id']);
////        dd($users);
//
//        $date_z = date("Y-m-d H:i:s");
//        $count = count($users);
//
//        $send = 0;
//        $send_no = 0;
//
//        Log::info('Старт: '.$date_z);
//        Log::info('Пользователей в очереди:  '.$count);
//
//        $i = 0;
//        foreach ($users as $user) {
//
//            $i++;
//            $user_id = $user->user_id;
//            $send = FeedBackCommandController::execute($user_id);
//            if ($send == true) Log::info($i.') '.$user_id.' - ok');
//            else Log::info($i.') '.$user_id.' - error');
//
//        }
//        Log::info('Стоп: '.date("Y-m-d H:i:s"));
//        Log::info('--------------- END FEEDBACK --------------------------');
//
//    }

}
