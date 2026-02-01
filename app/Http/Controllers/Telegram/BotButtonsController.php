<?php

namespace App\Http\Controllers\Telegram;

use Illuminate\Http\Request as LRequest;
use App\Http\Controllers\Controller;
use App\Models\BotSettingsButtons;

class BotButtonsController extends Controller
{
    public static function getButtons($user_id, $button_command, $button_name) {

        $lang = BotUserSettingsController::getLang($user_id);

        $buttons_arr = [];
        $w = isset($button_command) && $button_command !== '' && $button_command !== null ? 1 : 0;
        $w = isset($button_name) && $button_name !== '' && $button_name !== null ? 2 : $w;

        if ($w > 0) {

            $botton_lang = 'button_value_'.$lang;

            if ($w == 1) $buttons = BotSettingsButtons::where('button_command', $button_command)->orderBy('button_sort', 'asc')->get();
            elseif ($w == 2) $buttons = BotSettingsButtons::where('button_command', $button_command)->whereIn('button_name', $button_name)->orderBy('button_sort', 'asc')->get();
            // $where_button_ins = $w == 2 && $w !== 0 ? [['button_command', $button_command], ['button_name', $button_name]] : [['button_command', $button_command]];
            foreach ($buttons as $button) {
                $buttons_arr[] = $button->$botton_lang;
            }

        }

        return $buttons_arr;

    }
}
