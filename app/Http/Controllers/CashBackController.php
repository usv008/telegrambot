<?php

namespace App\Http\Controllers;


use App\Models\BotCashbackHistory;
use App\Models\BotOrder;
use App\Models\BotOrdersNew;
use App\Models\BotRaffleUsersHistory;
use App\Models\BotSettingsCashback;
use App\Models\BotUser;
use App\Http\Controllers\Telegram\BotButtonsInlineController;
use App\Http\Controllers\Telegram\BotCashbackController;
use App\Http\Controllers\Telegram\BotStickerController;
use App\Http\Controllers\Telegram\BotTextsController;
use App\Models\PrestaShop_Orders;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Request;
use PhpTelegramBot\Laravel\PhpTelegramBotContract;

class CashBackController extends Controller
{

    public static function addCashback() {

        $orders = SimplaOrdersController::getOrdersForCashBack();
        if (count($orders) > 0) {

            $cashback_percent = BotSettingsCashback::get_cashback_percent();
            $cashback_percent = $cashback_percent !== null && $cashback_percent > 0 ? $cashback_percent : 7;

            Log::info('-------------------------------- START CASHBACK -----------------------------------------');
            $i = 0;
            $sum = 0;
            foreach ($orders as $order) {

                $i++;
                $total_price = $order->total_price;
                $no_cashback_products = SimplaOrdersController::getOrderPurchasesForNoCashBack($order->simpla_id);
                foreach ($no_cashback_products as $no_cashback_product) {
                    $no_cashback_product_sum = bcmul($no_cashback_product->price, $no_cashback_product->amount, 2);
                    $total_price = bcsub($total_price, $no_cashback_product_sum, 2);
                }
                $cb = bcmul(bcdiv($total_price, 100, 2), $cashback_percent, 2);
                if ($cb > 0) {
                    $sum += $cb;

                    $balance_old = BotCashbackController::getUserCashback($order->user_id);
                    $balance_new = bcadd($balance_old, $cb, 2);

                    echo $i.') order_id: '.$order->order_id.'; simpla_id: '.$order->simpla_id.'; user_id: '.$order->user_id.'; price: '.$order->total_price.'; cb_old: '.$balance_old.'; cb: '.$cb.'; cb_new: '.$balance_new.'; order_modified: '.$order->modified.'<br />';
                    Log::info($i.') order_id: '.$order->order_id.'; simpla_id: '.$order->simpla_id.'; user_id: '.$order->user_id.'; price: '.$order->total_price.'; total_price: '.$total_price.'; cb_old: '.$balance_old.'; cb: '.$cb.'; cb_new: '.$balance_new.'; order_modified: '.$order->modified);

                    if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== '' && $_SERVER['REMOTE_ADDR'] !== null) $ip = $_SERVER['REMOTE_ADDR'];
                    else $ip = '';

                    $date_z = date("Y-m-d H:i:s");
                    $user_cashback_history = new BotCashbackHistory;
                    $user_cashback_history->admin_login = 'BOT';
                    $user_cashback_history->user_id = $order->user_id;
                    $user_cashback_history->order_id = $order->order_id;
                    $user_cashback_history->type = 'IN';
                    $user_cashback_history->summa = $cb;
                    $user_cashback_history->descr = 'Начисление за заказ № '.$order->simpla_id;
                    $user_cashback_history->balance_old = $balance_old;
                    $user_cashback_history->balance = $balance_new;
                    $user_cashback_history->ip = $ip;
                    $user_cashback_history->date_z = $date_z;
                    $user_cashback_history->save();

                    BotUser::where('user_id', $order->user_id)->update(['cashback' => $balance_new, 'updated_at' => $date_z]);
                    BotOrder::where('id', $order->order_id)->update(['cashback_cron' => 1]);

                    $data_sticker = ['chat_id' => $order->user_id];
                    $data_sticker['sticker'] = BotStickerController::getSticker(null, 'Start');
                    $data_sticker['reply_markup'] = Keyboard::remove(['selective' => true]);
                    $send_sticker = Request::sendSticker($data_sticker);

                    $inline_keyboard = new InlineKeyboard([]);
                    $inline_keyboard->addRow(new InlineKeyboardButton([
                        'text'          => 'OK',
                        'callback_data' => 'gotostart___'
                    ]));

                    $user_id = $order->user_id;

                    $currency = BotTextsController::getText($user_id, 'System', 'currency');
                    $text = BotTextsController::getText($user_id, 'Cashback', 'add');
                    $text = str_replace("___SUMMA___", $cb.' '.$currency, $text);
                    $text = str_replace("___ORDER_ID___", $order->simpla_id, $text);
                    $text = str_replace("___BALANCE___", $balance_new.' '.$currency, $text);

                    $data['chat_id'] = $order->user_id;
                    $data['parse_mode'] = 'html';
                    $data['text'] = $text;
                    $data['reply_markup'] = $inline_keyboard;
                    $result = Request::sendMessage($data);

                    $bot_raffle_users_history_count = BotRaffleUsersHistory::where('guest_user_id', $user_id)->where('cb_yes', 0)->count();
                    if ($bot_raffle_users_history_count > 0) {
                        $bot_raffle_users_history = BotRaffleUsersHistory::where('guest_user_id', $user_id)->where('cb_yes', 0)->first();
                        $invite_user_id = $bot_raffle_users_history['user_id'];

                        self::addCashbackInvite($invite_user_id, $user_id);
                    }
                }

            }
            Log::info('sum: '.$sum.'');
            Log::info('-------------------------------- END CASHBACK -----------------------------------------');

            echo '<b>'.$sum.'</b>';

        }

    }

    public static function addCashbackNew()
    {
        Log::info('---------------------------------START CRON CASHBACK---------------------------------------');
        $bot_orders = BotOrdersNew::getOrdersForCashBack();
        $orders_array = [];
        $current_state = 5;
        foreach ($bot_orders as $bot_order) {
            array_push($orders_array, $bot_order->external_id);
        }
        $presta_orders = PrestaShop_Orders::whereIn('id_order', $orders_array)->where('current_state', $current_state)->get();
        foreach ($presta_orders as $order) {
            $bot_order = $bot_orders->where('external_id', $order->id_order)->first();
            $cashback_percent = BotSettingsCashback::get_cashback_percent();
            $cashback_percent = $cashback_percent !== null && $cashback_percent > 0 ? $cashback_percent : 7;
            $cashback_add = bcmul(bcdiv($bot_order->price_for_cashback, 100, 10), $cashback_percent, 2);
            if ($cashback_add > 0) {
                $balance_old = BotCashbackController::getUserCashback($bot_order->user_id);
                $balance_new = bcadd($balance_old, $cashback_add, 2);
                if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== '' && $_SERVER['REMOTE_ADDR'] !== null) $ip = $_SERVER['REMOTE_ADDR'];
                else $ip = '';

                $user_cashback_history = new BotCashbackHistory;
                $user_cashback_history->admin_login = 'BOT';
                $user_cashback_history->user_id = $bot_order->user_id;
                $user_cashback_history->order_id = $bot_order->id;
                $user_cashback_history->type = 'IN';
                $user_cashback_history->summa = $cashback_add;
                $user_cashback_history->descr = 'Начисление за заказ № '.$bot_order->external_id;
                $user_cashback_history->balance_old = $balance_old;
                $user_cashback_history->balance = $balance_new;
                $user_cashback_history->ip = $ip;
                $user_cashback_history->date_z = date("Y-m-d H:i:s");
                $user_cashback_history->save();

                BotUser::where('user_id', $bot_order->user_id)->update(['cashback' => $balance_new, 'updated_at' => date("Y-m-d H:i:s")]);
                BotOrdersNew::where('id', $bot_order->id)->update(['cashback_cron' => 1]);
                Log::info('order_id: '.$bot_order->id.'; user_id: '.$bot_order->user_id.'; cashback_add: '.$cashback_add.'; price_for_cashback: '.$bot_order->price_for_cashback);

                $data_sticker = ['chat_id' => $bot_order->user_id];
                $data_sticker['sticker'] = BotStickerController::getSticker(null, 'Start');
                $data_sticker['reply_markup'] = Keyboard::remove(['selective' => true]);
                $send_sticker = Request::sendSticker($data_sticker);

                $inline_keyboard = new InlineKeyboard([]);
                $inline_keyboard->addRow(new InlineKeyboardButton([
                    'text'          => 'OK',
                    'callback_data' => 'gotostart___'
                ]));

                $currency = BotTextsController::getText($bot_order->user_id, 'System', 'currency');
                $text = BotTextsController::getText($bot_order->user_id, 'Cashback', 'add');
                $text = str_replace("___SUMMA___", $cashback_add.' '.$currency, $text);
                $text = str_replace("___ORDER_ID___", $bot_order->external_id, $text);
                $text = str_replace("___BALANCE___", $balance_new.' '.$currency, $text);

                $data['chat_id'] = $bot_order->user_id;
                $data['parse_mode'] = 'html';
                $data['text'] = $text;
                $data['reply_markup'] = $inline_keyboard;
                $result = Request::sendMessage($data);

                $bot_raffle_users_history_count = BotRaffleUsersHistory::where('guest_user_id', $bot_order->user_id)->where('cb_yes', 0)->count();
                if ($bot_raffle_users_history_count > 0) {
                    $bot_raffle_users_history = BotRaffleUsersHistory::where('guest_user_id', $bot_order->user_id)->where('cb_yes', 0)->first();
                    $invite_user_id = $bot_raffle_users_history['user_id'];

                    self::addCashbackInvite($invite_user_id, $bot_order->user_id);
                }
            }
        }
        Log::info('----------------------------------STOP CRON CASHBACK----------------------------------------');
    }

    public static function returnCashback() {

        $orders = BotCashbackHistory::join('bot_order', 'bot_order.id', 'bot_cashback_history.order_id')
            ->join('s_orders', 's_orders.id', 'bot_order.simpla_id')
            ->where('bot_cashback_history.type', 'OUT')
            ->where('s_orders.status', 3)
            ->where('bot_order.order_return_cashback', 0)
            ->get([
                'bot_cashback_history.user_id as user_id',
                'bot_order.id as order_id',
                'bot_order.id as order_id',
                's_orders.id as simpla_id',
                's_orders.archi_order_id as archi_id',
                'bot_order.order_phone as phone',
                'bot_cashback_history.summa as summa',
                'bot_cashback_history.descr as desc',
                'bot_cashback_history.balance_old as balance_old',
                'bot_cashback_history.balance as balance',
                's_orders.date as date',
            ]);

        Log::info('-------------------------------- START RETURN CASHBACK -----------------------------------------');
        $i = 0;
        $sum = 0;
        foreach ($orders as $order) {

            $i++;
            $sum += $order->summa;

            $balance_old = BotCashbackController::getUserCashback($order->user_id);
            $balance_new = bcadd($balance_old, $order->summa, 2);

            echo $i.') order_id: '.$order->order_id.'; simpla_id: '.$order->simpla_id.'; user_id: '.$order->user_id.'; cb_old: '.$balance_old.'; cb: '.$order->summa.'; cb_new: '.$balance_new.'<br />';
            Log::info($i.') order_id: '.$order->order_id.'; simpla_id: '.$order->simpla_id.'; user_id: '.$order->user_id.'; cb_old: '.$balance_old.'; cb: '.$order->summa.'; cb_new: '.$balance_new);

            if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== '' && $_SERVER['REMOTE_ADDR'] !== null) $ip = $_SERVER['REMOTE_ADDR'];
            else $ip = '';

            $date_z = date("Y-m-d H:i:s");

            $user_cashback_history = new BotCashbackHistory;
            $user_cashback_history->admin_login = 'BOT';
            $user_cashback_history->user_id = $order->user_id;
            $user_cashback_history->order_id = $order->order_id;
            $user_cashback_history->type = 'IN';
            $user_cashback_history->summa = $order->summa;
            $user_cashback_history->descr = 'Начисление за отмененный заказ № '.$order->simpla_id;
            $user_cashback_history->balance_old = $balance_old;
            $user_cashback_history->balance = $balance_new;
            $user_cashback_history->ip = $ip;
            $user_cashback_history->date_z = $date_z;
            $user_cashback_history->save();

            BotUser::where('user_id', $order->user_id)->update(['cashback' => $balance_new, 'updated_at' => $date_z]);
            BotOrder::where('id', $order->order_id)->update(['return_cashback' => 1]);

            $data_sticker = ['chat_id' => $order->user_id];
            $data_sticker['sticker'] = BotStickerController::getSticker(null, 'Start');
            $data_sticker['reply_markup'] = Keyboard::remove(['selective' => true]);
            $send_sticker = Request::sendSticker($data_sticker);

            $inline_keyboard = new InlineKeyboard([]);
            $inline_keyboard->addRow(new InlineKeyboardButton([
                'text'          => 'OK',
                'callback_data' => 'gotostart___'
            ]));

//            $user_id = 522750680;
            $user_id = $order->user_id;

            $currency = BotTextsController::getText($order->user_id, 'System', 'currency');
            $text = BotTextsController::getText($order->user_id, 'Cashback', 'return');
            $text = str_replace("___SUMMA___", $order->summa.' '.$currency, $text);
            $text = str_replace("___ORDER_ID___", $order->simpla_id, $text);
            $text = str_replace("___BALANCE___", $balance_new.' '.$currency, $text);

            echo '------------------<br />'.$text.'<br />---------------------<br />';

            $data['chat_id'] = $user_id;
            $data['parse_mode'] = 'html';
            $data['text'] = $text;
            $data['reply_markup'] = $inline_keyboard;
            $result = Request::sendMessage($data);


        }
        Log::info('sum: '.$sum.'');
        Log::info('-------------------------------- END RETURN CASHBACK -----------------------------------------');

    }

    public static function returnCashbackNew()
    {
        Log::info('------------------------------START CRON RETURN CASHBACK------------------------------------');
        $bot_orders = BotOrdersNew::getOrdersForCashBack();
        $orders_array = [];
        $current_state = 18;
        foreach ($bot_orders as $bot_order) {
            array_push($orders_array, $bot_order->external_id);
        }
        $presta_orders = PrestaShop_Orders::whereIn('id_order', $orders_array)->where('current_state', $current_state)->get();
        foreach ($presta_orders as $order) {
            $bot_order = $bot_orders->where('external_id', $order->id_order)->first();
            $cashback_return = $bot_order->cashback_pay;
            if ($cashback_return > 0) {
                $balance_old = BotCashbackController::getUserCashback($bot_order->user_id);
                $balance_new = bcadd($balance_old, $cashback_return, 2);
                if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== '' && $_SERVER['REMOTE_ADDR'] !== null) $ip = $_SERVER['REMOTE_ADDR'];
                else $ip = '';

                $user_cashback_history = new BotCashbackHistory;
                $user_cashback_history->admin_login = 'BOT';
                $user_cashback_history->user_id = $bot_order->user_id;
                $user_cashback_history->order_id = $bot_order->id;
                $user_cashback_history->type = 'IN';
                $user_cashback_history->summa = $cashback_return;
                $user_cashback_history->descr = 'Начисление за отмененный заказ № '.$bot_order->external_id;
                $user_cashback_history->balance_old = $balance_old;
                $user_cashback_history->balance = $balance_new;
                $user_cashback_history->ip = $ip;
                $user_cashback_history->date_z = date("Y-m-d H:i:s");
                $user_cashback_history->save();

                BotUser::where('user_id', $bot_order->user_id)->update(['cashback' => $balance_new, 'updated_at' => date("Y-m-d H:i:s")]);
                BotOrdersNew::where('id', $bot_order->id)->update(['cashback_cron' => 1, 'return_cashback' => 1]);
                Log::info('order_id: '.$bot_order->id.'; user_id: '.$bot_order->user_id.'; cashback_return: '.$cashback_return);

                $data_sticker = ['chat_id' => $bot_order->user_id];
                $data_sticker['sticker'] = BotStickerController::getSticker(null, 'Start');
                $data_sticker['reply_markup'] = Keyboard::remove(['selective' => true]);
                $send_sticker = Request::sendSticker($data_sticker);

                $inline_keyboard = new InlineKeyboard([]);
                $inline_keyboard->addRow(new InlineKeyboardButton([
                    'text'          => 'OK',
                    'callback_data' => 'gotostart___'
                ]));

                $currency = BotTextsController::getText($order->user_id, 'System', 'currency');
                $text = BotTextsController::getText($order->user_id, 'Cashback', 'return');
                $text = str_replace("___SUMMA___", $cashback_return.' '.$currency, $text);
                $text = str_replace("___ORDER_ID___", $bot_order->external_id, $text);
                $text = str_replace("___BALANCE___", $balance_new.' '.$currency, $text);

                $data['chat_id'] = $bot_order->user_id;
                $data['parse_mode'] = 'html';
                $data['text'] = $text;
                $data['reply_markup'] = $inline_keyboard;
                $result = Request::sendMessage($data);
            }
        }
        Log::info('-------------------------------STOP CRON RETURN CASHBACK------------------------------------');
    }

    public static function addCashbackReferral($user_id = null, $referral_id = null)
    {

//        $user_id = '522750680';
//        $referral_id = '522750680';

        if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== '' && $_SERVER['REMOTE_ADDR'] !== null) $ip = $_SERVER['REMOTE_ADDR'];
        else $ip = '';

        $cb_user = BotSettingsCashback::get_cashback_add_user();
        $cb_referral = BotSettingsCashback::get_cashback_add_referal();
        $currency = BotTextsController::getText($referral_id, 'System', 'currency');
        $balance_old = BotCashbackController::getUserCashback($referral_id);
        $balance_new = bcadd($balance_old, $cb_referral, 2);

        $date_z = date("Y-m-d H:i:s");
        $user_cashback_history = new BotCashbackHistory;
        $user_cashback_history->admin_login = 'BOT';
        $user_cashback_history->user_id = $referral_id;
        $user_cashback_history->type = 'IN';
        $user_cashback_history->summa = $cb_referral;
        $user_cashback_history->descr = 'Начисление новому пользователю. Привел '.$user_id;
        $user_cashback_history->balance_old = $balance_old;
        $user_cashback_history->balance = $balance_new;
        $user_cashback_history->ip = $ip;
        $user_cashback_history->date_z = $date_z;
        $user_cashback_history->save();

        BotUser::where('user_id', $referral_id)->update(['cashback' => $balance_new, 'updated_at' => $date_z]);

        $text = BotTextsController::getText($referral_id, 'Cashback', 'add_referral_guest');
        $text = str_replace("___SUMMA___", $cb_referral.' '.$currency, $text);
        $text = str_replace("___SUMMA_USER___", $cb_user.' '.$currency, $text);

        $inline_keyboard = new InlineKeyboard([]);

        list($text1, $data1) = BotButtonsInlineController::getButtonInline($user_id, 'More', 'share');
        list($text2, $data2) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'goto_start');
        $inline_keyboard->addRow(
            new InlineKeyboardButton(['text' => $text1, 'switch_inline_query' => $data1])
        );
        $inline_keyboard->addRow(
            new InlineKeyboardButton(['text' => $text2, 'callback_data' => $data2])
        );

        $data['chat_id'] = $referral_id;
        $data['parse_mode'] = 'html';
        $data['text'] = $text;
        $data['reply_markup'] = $inline_keyboard;
        $result = Request::sendMessage($data);

//        dd($result);

    }

    public static function addCashbackInvite($user_id = null, $referral_id = null)
    {

//        $user_id = '522750680';
//        $referral_id = '522750680';

        if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== '' && $_SERVER['REMOTE_ADDR'] !== null) $ip = $_SERVER['REMOTE_ADDR'];
        else $ip = '';

        $cb_user = BotSettingsCashback::get_cashback_add_user();
        $cb_referral = BotSettingsCashback::get_cashback_add_referal();
        $currency = BotTextsController::getText($user_id, 'System', 'currency');
        $balance_old = BotCashbackController::getUserCashback($user_id);
        $balance_new = bcadd($balance_old, $cb_referral, 2);

        $date_z = date("Y-m-d H:i:s");
        $user_cashback_history = new BotCashbackHistory;
        $user_cashback_history->admin_login = 'BOT';
        $user_cashback_history->user_id = $user_id;
        $user_cashback_history->type = 'IN';
        $user_cashback_history->summa = $cb_user;
        $user_cashback_history->descr = 'Начисление за заказ нового пользователя. refferal_user_id: '.$referral_id;
        $user_cashback_history->balance_old = $balance_old;
        $user_cashback_history->balance = $balance_new;
        $user_cashback_history->ip = $ip;
        $user_cashback_history->date_z = $date_z;
        $user_cashback_history->save();

        BotUser::where('user_id', $user_id)->update(['cashback' => $balance_new, 'updated_at' => $date_z]);
        BotRaffleUsersHistory::where('guest_user_id', $referral_id)->where('cb_yes', 0)->update(['cb_yes' => 1]);

        $text = BotTextsController::getText($referral_id, 'Cashback', 'add_invite_user');
        $text = str_replace("___SUMMA_USER___", $cb_user.' '.$currency, $text);

        $inline_keyboard = new InlineKeyboard([]);
        $inline_keyboard->addRow(new InlineKeyboardButton([
            'text'          => 'OK',
            'callback_data' => 'gotostart___'
        ]));

        $data['chat_id'] = $user_id;
        $data['parse_mode'] = 'html';
        $data['text'] = $text;
        $data['reply_markup'] = $inline_keyboard;
        $result = Request::sendMessage($data);

//        dd($result);

    }


}
