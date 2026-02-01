<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotSettings;
use App\Models\BotSettingsDelivery;
use App\Models\BotSettingsPayments;
use App\Models\BotUser;
use App\Models\BotUsersNav;
use Illuminate\Http\Request as LRequest;
use App\Http\Controllers\Controller;

class BotSettingsDeliveryController extends Controller
{

    public static function checkDiscount($user_id) {

        $city_id = BotUser::getValue($user_id, 'city_id');
        $city_id = $city_id !== null && is_numeric($city_id) ? $city_id : 6;

        return BotUsersNav::leftJoin('bot_settings_delivery', 'bot_settings_delivery.id', 'bot_users_nav.delivery_id')
            ->where('bot_users_nav.user_id', $user_id)
            ->where('bot_settings_delivery.enabled', 1)
            ->where('bot_settings_delivery.region_id', $city_id)
            ->first(['bot_settings_delivery.discount'])['discount'];

    }

    public static function checkDiscountFromDeliveryId($user_id, $delivery_id) {

        return BotSettingsDelivery::where('id', $delivery_id)->first()['discount'];

    }

    public static function getDeliveryFromDeliveryId($delivery_id) {

        return BotSettingsDelivery::where('id', $delivery_id)->first();

    }

    public static function getAddr($user_id, $delivery_id) {

        $lang = BotUserSettingsController::getLang($user_id);
        return BotSettingsDelivery::where('id', $delivery_id)->first()['addr_'.$lang];

    }

    public static function getSDeliveryId($user_id, $delivery_id) {

        return BotSettingsDelivery::where('id', $delivery_id)->first()['s_delivery_id'];

    }

    public static function getTextValue($user_id, $delivery_id) {

        return BotSettingsDelivery::where('id', $delivery_id)->first()['text_value'];

    }

    public static function getDeliveryCourier($user_id) {

        $city_id = BotUser::getValue($user_id, 'city_id');
        $city_id = $city_id !== null && is_numeric($city_id) ? $city_id : 6;

        return BotSettingsDelivery::where('is_default', 1)->where('region_id', $city_id)->first();

    }

}
