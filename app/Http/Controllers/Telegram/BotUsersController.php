<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotUsers;
use Illuminate\Http\Request as LRequest;
use App\Http\Controllers\Controller;

class BotUsersController extends Controller
{

    public static function updateUsers($user_id, $whatupdate, $toupdate) {

        $num = BotUsers::where('user_id', $user_id)->count();
        if (!isset($num) || $num == 0 || $num == null) {

            $date_reg = date("Y-m-d H:i:s");

            $users = new BotUsers;
            $users->user_id = $user_id;
            $users->date_reg = $date_reg;
            $users->date_edit = $date_reg;
            $users->save();

        }

        $date_edit = date("Y-m-d H:i:s");

        BotUsers::where('user_id', $user_id)->update([$whatupdate => $toupdate, 'date_edit' => $date_edit]);

        return true;

    }

    public static function getValueFromUsers($user_id, $value) {

        return BotUsers::where('user_id', $user_id)->first()[$value];

    }
}
