<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotAdvertisingChannelHistory;
use Illuminate\Http\Request as LRequest;
use App\Http\Controllers\Controller;
use App\Models\BotSettingsButtons;

class BotAdvertisingChannelController extends Controller
{

    public static function add_to_history($channel_id, $user_id) {

        $history = new BotAdvertisingChannelHistory();
        $history->channel_id = $channel_id;
        $history->user_id = $user_id;
        $history->date_z = date("Y-m-d H:i:s");
        $history->save();

    }

}
