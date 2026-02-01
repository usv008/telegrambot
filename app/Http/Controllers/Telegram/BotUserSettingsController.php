<?php

namespace App\Http\Controllers\Telegram;

use Illuminate\Http\Request as LRequest;
use App\Http\Controllers\Controller;
use App\Models\BotUserSettings;

class BotUserSettingsController extends Controller
{
    public static function getLang($user_id) {

        $lang = 'uk';
        if ($user_id !== null) {
            $date_z = date("Y-m-d H:i:s");
            $settings = BotUserSettings::firstOrCreate(
                ['user_id' => $user_id],
                ['lang' => $lang, 'date_z' => $date_z]
            );
            if ($settings->lang == 'ru') $settings->lang = 'uk';
            $lang = $settings && isset($settings->lang) && $settings->lang !== null && $settings->lang !== '' ? $settings->lang : 'uk';
        }

        return $lang;

    }

    public static function updateLang($user_id, $lang) {

        $date_z = date("Y-m-d H:i:s");
        return BotUserSettings::where('user_id', $user_id)->update(['lang' => $lang, 'date_z' => $date_z]);

    }

    public static function getUsers()
    {
        return BotUserSettings::all();
    }

}
