<?php

namespace App\Http\Controllers;

use App\Models\BotRaffleUsers;

class BotRaffleUsersController extends Controller
{

    public static function updateRaffleTry($user_id)
    {

        $raffle_try = 2;
        $date_z = date("Y-m-d H:i:s");
        return BotRaffleUsers::where('user_id', $user_id)->update(['raffle_try' => $raffle_try, 'date_edit' => $date_z]);

    }

}
