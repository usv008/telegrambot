<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotSettingsCities;
use App\Http\Controllers\Controller;

class BotCitiesController extends Controller
{

    public static function getCities($user_id)
    {

        $lang = BotUserSettingsController::getLang($user_id);

        $city_lang = 'name_'.$lang;
        $cities = BotSettingsCities::orderBy('name', 'asc')->get();

        $cities_arr = [];
        foreach ($cities as $city) {
            $cities_arr[] = $city->$city_lang;
        }

        return $cities_arr;

    }

}
