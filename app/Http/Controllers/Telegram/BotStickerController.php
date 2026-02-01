<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request as LRequest;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\Keyboard;

use App\Models\BotSettingsSticker;

class BotStickerController extends Controller
{
    public static function getSticker($user_id, $commmand) {

        return BotSettingsSticker::where('sticker_command', $commmand)->first()->sticker_value;

    }

    public static function sendSticker($user_id, $command)
    {
        $data_sticker = ['chat_id' => $user_id];
        $data_sticker['sticker'] = BotStickerController::getSticker($user_id, $command);
        $data_sticker['reply_markup'] = Keyboard::remove(['selective' => true]);
        return Request::sendSticker($data_sticker);
    }

    public static function editSticker($user_id, $message_id, $command)
    {
        $data_sticker = ['chat_id' => $user_id];
        $data_sticker['sticker'] = BotStickerController::getSticker($user_id, $command);
        $data_sticker['reply_markup'] = Keyboard::remove(['selective' => true]);
        return Request::sendSticker($data_sticker);
    }

}
