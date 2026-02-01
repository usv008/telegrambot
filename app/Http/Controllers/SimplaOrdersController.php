<?php

namespace App\Http\Controllers;

use App\Models\BotOrder;
use App\Models\BotUser;
use App\Http\Controllers\Telegram\BotCartController;
use App\Http\Controllers\Telegram\BotSettingsDeliveryController;
use App\Http\Controllers\Telegram\BotSettingsPaymentsController;
use App\Http\Controllers\Telegram\BotTextsController;
use App\Http\Controllers\Telegram\BotUsersNavController;
use App\Models\Simpla_Purchases;
use App\Models\SimplaOrders;
use App\Models\SimplaOrdersDuble;
use App\Models\SimplaOrdersLabels;
use Longman\TelegramBot\Request;

class SimplaOrdersController extends Controller
{

    public static function getValuesForSimplaOrder($user_id) {

        $delivery_id = BotUsersNavController::getValue($user_id, 'delivery_id');
        $simpla_delivery_id = BotSettingsDeliveryController::getSDeliveryId($user_id, $delivery_id);

        $payment_id = BotUsersNavController::getValue($user_id, 'payment_id');
        $payment_method_id = BotSettingsPaymentsController::getPaymentMethodId($payment_id);

        $name = BotUsersNavController::getValue($user_id, 'name');

        $phone = BotUsersNavController::getValue($user_id, 'phone');
        $ins_phone_simpla = strval($phone);
        $ins_phone_simpla = '+'.substr($ins_phone_simpla, 0, 2).'('.substr($ins_phone_simpla, 2, 3).')'.substr($ins_phone_simpla, 5, 7);

        $city_id = BotUser::getValue($user_id, 'city_id');
        $city_id = $city_id !== null && is_numeric($city_id) ? $city_id : 6;
        $city_name = SimplaRegionsController::getRegionNameFromId($user_id, $city_id);

        $addr = $city_name . ', ' . BotUsersNavController::getValue($user_id, 'addr');

        $simpla_user_id = SimplaUsersController::add_user(['name' => $name, 'phone' => '+'.$phone, 'enabled' => 1]);

        $order_date = BotUsersNavController::getValue($user_id, 'date');
        $odate = date("d.m.Y", strtotime($order_date));

        $time = BotUsersNavController::getValue($user_id, 'time');
        $time_arr = explode(":", $time);
        $ins_hour = $time_arr[0];
        $ins_min =  $time_arr[1];

        $comment = BotUsersNavController::getValue($user_id, 'comment');

        $region_id = BotUser::getValue($user_id, 'city_id');
        $region = SimplaRegionsController::getRegionNameFromId($user_id, $region_id);
        $comment .= PHP_EOL.PHP_EOL.'Город: '.$region.PHP_EOL;

        $comment .= PHP_EOL.PHP_EOL.'Адрес: '.$addr.PHP_EOL;

        $no_call = BotUsersNavController::getValue($user_id, 'no_call');
        $comment .= $no_call == 1 ? PHP_EOL.PHP_EOL.BotTextsController::getText($user_id, 'Order', 'order_send_no_call').PHP_EOL : '';

        $comment .= PHP_EOL.PHP_EOL.BotUsersNavController::getValue($user_id, 'change_from').PHP_EOL;

        $comment .= BotCartController::checkProductsSushiInCart($user_id) > 0 ? PHP_EOL.PHP_EOL.BotUsersNavController::getValue($user_id, 'sushi_sticks').PHP_EOL : '';

        $contactless = BotUsersNavController::getValue($user_id, 'contactless');
        $comment .= $contactless == 1 ? PHP_EOL.BotTextsController::getText($user_id, 'Order', 'order_contactless_send').BotUsersNavController::getValue($user_id, 'contactless_comment').PHP_EOL : '';

        $comment .= BotUsersNavController::getValue($user_id, 'birthday') == 1 ? PHP_EOL.'---=== СКИДКА ДР 20% ===---'.PHP_EOL : '';

        $sum = BotCartController::count_sum($user_id);
        $delivery_price = BotCartController::count_delivery($user_id, $sum);

        $total = BotCartController::count_sum_total($user_id);

        $cashback = BotCartController::count_cashback($user_id);

        $url = 'tg' . md5(uniqid());

        $discount = BotUsersNavController::getValue($user_id, 'birthday') == 1 ? 20 : 0;

        $city_id = BotUser::getValue($user_id, 'city_id');
        $city_id = $city_id !== null && is_numeric($city_id) ? $city_id : 6;

        $arr = [
            'delivery_id' => $simpla_delivery_id,
            'delivery_price' => $delivery_price,
            'payment_method_id' => $payment_method_id,
            'date' => date("Y-m-d H:i:s"),
            'user_id' => $simpla_user_id,
            'name' => $name,
            'address' => $addr,
            'phone' => $ins_phone_simpla,
            'odate' => $odate,
            'hour' => $ins_hour,
            'minute' => $ins_min,
            'comment' => $comment,
            'url' => $url,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'total_price' => $total,
            'discount' => $discount,
//                'coupon_discount' => 0,
//                'coupon_code' => '',
            'bonus_cashback_bot' => $cashback,
            'region_id' => $city_id,
            'telegram_bot' => 1
        ];

        if ($payment_method_id == 12) $arr['note'] = 'ЖДЕМ ОПЛАТУ';

        return $arr;

    }


    public static function check_order($user_id)
    {

        $order_arr = self::getValuesForSimplaOrder($user_id);
        return SimplaOrders::where('delivery_id', $order_arr['delivery_id'])
//            ->where('delivery_price', $order_arr['delivery_price'])
            ->where('payment_method_id', $order_arr['payment_method_id'])
            ->where('user_id', $order_arr['user_id'])
            ->where('name', $order_arr['name'])
            ->where('address', $order_arr['address'])
            ->where('phone', $order_arr['phone'])
            ->where('odate', $order_arr['odate'])
            ->where('hour', $order_arr['hour'])
            ->where('minute', $order_arr['minute'])
//            ->where('comment', $order_arr['comment'])
//            ->where('ip', $order_arr['ip'])
            ->where('total_price', $order_arr['total_price'])
            ->where('bonus_cashback_bot', $order_arr['bonus_cashback_bot'])
            ->where('region_id', $order_arr['region_id'])
            ->count();

    }

    public static function add_order($user_id)
    {

        if (BotUsersNavController::getValue($user_id, 'order_sent') == 0 && BotCartController::checkProductsInCart($user_id) > 0) {

            BotUsersNavController::updateValue($user_id, 'order_sent', 1);
            $order_arr = self::getValuesForSimplaOrder($user_id);
            $id = SimplaOrders::insertGetId($order_arr);
            if ($id !== null && $id > 0) {
                $order_arr['id'] = $id;
                SimplaOrdersDuble::insert($order_arr);
                SimplaOrdersLabels::insert(['order_id' => $id, 'label_id' => 10]);
                if ($order_arr['payment_method_id'] == 12) {
                    SimplaOrdersLabels::insert(['order_id' => $id, 'label_id' => 8]);
                }
                return $id;
            }

        }
        else {
            return null;
        }
        return null;

    }

    public static function getSumFromOrdersSuccess($user_id) {

        $phone = BotUsersNavController::getValue($user_id, 'phone');
        $phone_simpla = strval($phone);
        $phone_simpla = '+'.substr($phone_simpla, 0, 2).'('.substr($phone_simpla, 2, 3).')'.substr($phone_simpla, 5, 7);

        return SimplaOrders::where('phone', $phone_simpla)->where('status', 2)->sum('total_price');

    }

    public static function getOrdersForCashBack() {

        $day = '2019-06-27';
        return BotOrder::join('s_orders', 's_orders.id', 'bot_order.external_id')
            ->where('bot_order.cashback_cron', 0)
            ->where('bot_order.created_at', '>', $day.' 00:00:00')
            ->where('s_orders.status', 2)
            ->orderBy('bot_order.id', 'asc')
            ->get(['bot_order.user_id', 'bot_order.id as order_id', 's_orders.id as simpla_id', 's_orders.total_price', 's_orders.modified']);

    }

    public static function getOrderPurchasesForNoCashBack($order_id)
    {
        return Simpla_Purchases::join('s_products', 's_products.id', 's_purchases.product_id')
            ->where('s_purchases.order_id', $order_id)
            ->where('s_products.no_cashback', 1)
            ->get(['s_purchases.price as price', 's_purchases.amount as amount']);
    }

}
