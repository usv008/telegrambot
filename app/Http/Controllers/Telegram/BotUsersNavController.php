<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotUser;
use App\Models\BotUsersNav;
use Illuminate\Http\Request as LRequest;
use App\Http\Controllers\Controller;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request;

class BotUsersNavController extends Controller
{

    public static function getValue($user_id, $value)
    {

        return BotUsersNav::where('user_id', $user_id)->first()[$value];

    }

    public static function getCartMessageId($user_id)
    {

        return BotUsersNav::where('user_id', $user_id)->first()['cart_message_id'];

    }

    public static function updateCartMessageId($user_id, $message_id)
    {

        $date_z = date("Y-m-d H:i:s");
        $cart = BotUsersNav::updateOrCreate(
            ['user_id' => $user_id],
            ['cart_message_id' => $message_id, 'date_z' => $date_z]
        );

    }

    public static function get_delivery_from_user_id($user_id) {

        $city_id = BotUser::getValue($user_id, 'city_id');
        $city_id = $city_id !== null && is_numeric($city_id) ? $city_id : 6;

        $delivery = BotUsersNav::leftJoin('bot_settings_delivery', 'bot_settings_delivery.id', 'bot_users_nav.delivery_id')
            ->where('bot_users_nav.user_id', $user_id)
            ->where('bot_settings_delivery.enabled', 1)
            ->where('bot_settings_delivery.region_id', $city_id)
            ->first(['bot_users_nav.delivery_id', 'bot_settings_delivery.discount']);

        return $delivery;

    }

    public static function update_delivery($user_id, $value)
    {

        $date_z = date("Y-m-d H:i:s");
        $delivery = BotUsersNav::updateOrCreate(
            ['user_id' => $user_id],
            ['delivery_id' => $value, 'date_z' => $date_z]
        );

    }

    public static function clear_delivery($user_id)
    {

        $date_z = date("Y-m-d H:i:s");
        $delivery = BotUsersNav::where('user_id', $user_id)->update(['delivery_id' => null, 'date_z' => $date_z]);

    }

    public static function updateUsersNavDate($user_id, $value)
    {

        $date_z = date("Y-m-d H:i:s");
        $delivery = BotUsersNav::updateOrCreate(
            ['user_id' => $user_id],
            ['date' => $value, 'date_z' => $date_z]
        );

    }

    public static function updateUsersNavTime($user_id, $value)
    {

        $date_z = date("Y-m-d H:i:s");
        $delivery = BotUsersNav::updateOrCreate(
            ['user_id' => $user_id],
            ['time' => $value, 'date_z' => $date_z]
        );

    }

    public static function updateUsersNavPayment($user_id, $value)
    {

        $date_z = date("Y-m-d H:i:s");
        $delivery = BotUsersNav::updateOrCreate(
            ['user_id' => $user_id],
            ['payment_id' => $value, 'date_z' => $date_z]
        );

    }

    public static function updateUsersNavName($user_id, $value)
    {

        $date_z = date("Y-m-d H:i:s");
        $delivery = BotUsersNav::updateOrCreate(
            ['user_id' => $user_id],
            ['name' => $value, 'date_z' => $date_z]
        );

    }

    public static function updateValue($user_id, $key, $value)
    {

        $date_z = date("Y-m-d H:i:s");
        $update = BotUsersNav::updateOrCreate(
            ['user_id' => $user_id],
            [$key => $value, 'date_z' => $date_z]
        );
        return $update;

    }

    public static function getAndUpdateValue($user_id, $key, $value)
    {

        $value_old = self::getValue($user_id, $key);
        $value_new = $value_old == null || $value_old == '' ? $value : $value_old;
        self::updateValue($user_id, $key, $value_new);

        return $value_new;

    }

    public static function deleteMessageAndUpdateMessageId($user_id, $key, $value)
    {

        $message_old_id = BotUsersNavController::getValue($user_id, $key);
        if ($message_old_id !== null) {
            $data_delete = ['chat_id' => $user_id];
            $data_delete['message_id'] = $message_old_id;
            Request::deleteMessage($data_delete);
        }
        self::updateValue($user_id, $key, $value);

    }

    public static function deleteMessageId($user_id, $message_id)
    {
        $data_delete = ['chat_id' => $user_id];
        $data_delete['message_id'] = $message_id;
        $result = Request::deleteMessage($data_delete);
        return $result;
    }

    public static function checkNamePhoneAddress($user_id)
    {

        $nav = BotUsersNav::where('user_id', $user_id)->first();
        return $nav['name'] !== null && $nav['name'] !== '' && $nav['phone'] !== null && $nav['phone'] !== '' && $nav['addr'] !== null && $nav['addr'] !== '' ? 1 : null;

    }

    public static function getDeliveryYesOrNo($user_id)
    {
        $delivery_id = BotUsersNavController::getValue($user_id, 'delivery_id');
        $delivery = BotSettingsDeliveryController::getDeliveryFromDeliveryId($delivery_id);
        return $delivery->delivery;
    }

}
