<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotUser;
use App\Http\Controllers\Controller;
use App\Models\LongmanBotUser;
use Illuminate\Http\Request;
use App\Models\BotUserHistory;

class BotUserHistoryController extends Controller {

    public static function insertToHistory($user_id, $type, $user_event) {

        $date_z = date("Y-m-d H:i:s");

        if ($type !== 'open') {
            $user_history = new BotUserHistory;
            $user_history->user_id = $user_id;
            $user_history->type = $type;
            $user_history->user_event = $user_event;
            $user_history->date_z = $date_z;
            $user_history->save();
        }

        $check_user = BotUser::where('user_id', $user_id)->count();
        if ($check_user == 0) {

            $longman_user = LongmanBotUser::where('id', $user_id)->first();

            $user = new BotUser;
            $user->user_id = $user_id;
            $user->is_bot = $longman_user->is_bot;
            $user->username = $longman_user->username;
            $user->first_name = $longman_user->first_name;
            $user->last_name = $longman_user->last_name;
//            $user->language_code = $longman_user->language_code;
            $user->language_code = 'uk';
            $user->city_id = 6;
            $user->created_at = $date_z;
            $user->updated_at = $date_z;
            $user->save();

        }
        else {

            BotUser::where('user_id', $user_id)->update(['updated_at' => date("Y-m-d H:i:s")]);

        }


    }



}
