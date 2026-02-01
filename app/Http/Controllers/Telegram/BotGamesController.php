<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;

class BotGamesController extends Controller
{

    private static $games = [
        'dice',
        'sea_battle',
    ];

    public static function selectGame($user_id)
    {
        $command = 'Games';
        $name = 'select';

        $send_sticker = BotStickerController::sendSticker($user_id, 'Start');

        $data_message = ['chat_id' => $user_id];
        $data_message['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_message['parse_mode'] = 'html';
        $data_message['reply_markup'] = BotButtonsInlineController::getButtonsInline($user_id, $command, $name);;
        $send_text = Request::sendMessage($data_message);
        return $send_text;
    }

    public static function startGame($user_id, $game, $data)
    {
        if (in_array($game, self::$games)) {
            switch ($game) {
                case 'dice':
                    BotGameDiceController::startGame($user_id, $data);
                    break;
                case 'sea_battle':
                    BotGameSeaBattleController::startGame($user_id, $data);
                    break;
                default:
                    BotGameDiceController::startGame($user_id, $data);
            }
        }
    }

}
