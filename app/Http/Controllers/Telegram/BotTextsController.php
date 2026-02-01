<?php

namespace App\Http\Controllers\Telegram;

use Illuminate\Http\Request as LRequest;
use App\Http\Controllers\Controller;
use App\Models\BotSettingsTexts;

class BotTextsController extends Controller
{

    public static function getUserTextLang($user_id) {

        $lang = BotUserSettingsController::getLang($user_id);
        $text_lang = 'text_value_'.$lang;
        return $text_lang;

    }

    public static function getText($user_id, $command, $name) {

        $text_lang = self::getUserTextLang($user_id);
        return BotSettingsTexts::where('text_command', $command)->where('text_name', $name)->first()->$text_lang;

    }
}
