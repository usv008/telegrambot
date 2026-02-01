<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotSettings;
use Illuminate\Http\Request as LRequest;
use App\Http\Controllers\Controller;

class BotSettingsController extends Controller
{

    public static function getSettings($user_id, $settings_name) {

        return BotSettings::where('settings_name', $settings_name)->first();

    }

}
