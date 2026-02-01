<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use Longman\TelegramBot\Request;

class BotGameController extends Controller
{

    public static function startGame($user_id, $emoji, $value)
    {

        $data = ['chat_id' => $user_id];

    }

}
