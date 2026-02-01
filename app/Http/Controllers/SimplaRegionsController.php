<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Telegram\BotUserSettingsController;
use App\Models\Simpla_Regions;

class SimplaRegionsController extends Controller
{

    public static function getRegions($user_id)
    {

        $lang = BotUserSettingsController::getLang($user_id);

        $city_lang = 'name_'.$lang;
        $cities = Simpla_Regions::orderBy('position', 'asc')->get();

        $cities_arr = [];
        foreach ($cities as $city) {
            $cities_arr[$city->id] = $city->$city_lang;
        }

        return $cities_arr;

    }

    public static function getRegionNameFromId($user_id, $region_id)
    {
        $lang = BotUserSettingsController::getLang($user_id);
        $name_lang = 'name_'.$lang;
        return Simpla_Regions::find($region_id)[$name_lang];
    }

}
