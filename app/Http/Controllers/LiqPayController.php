<?php

namespace App\Http\Controllers;

use App\Models\BotCart;
use App\Models\BotOrder;
use App\Models\BotOrdersNew;
use App\Models\BotPaymentsCallback;
use App\Http\Controllers\Telegram\BotOrderController;
use App\Http\Controllers\Telegram\BotSettingsController;
use App\Http\Controllers\Telegram\BotTextsController;
use App\Http\Controllers\Telegram\StartCommandController;
use App\Models\PrestaShop_Orders;
use App\Models\SimplaOrders;
use App\Models\SimplaOrdersDuble;
use Illuminate\Http\Request as LRequest;
use App\Http\Controllers\LiqPaySDKController;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Commands\UserCommands\PreCheckoutQueryCommand;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Telegram;
use PhpTelegramBot\Laravel\PhpTelegramBotContract;

class LiqPayController extends Controller
{

    public static function sendInvoice($user_id, $external_id) {

//        $total_price = BotOrderController::getOrderFromSimplaId($user_id, $simpla_id)['order_price'];
        $order = BotOrdersNew::where('user_id', $user_id)->where('external_id', $external_id)->first();
        $total_price = $order->price_with_discount;
        $currency = BotTextsController::getText($user_id, 'System', 'currency');
        $text_button = BotTextsController::getText($user_id, 'LiqPay', 'pay');

        $inline_keyboard = new InlineKeyboard(
            [
                ['text' => $text_button .$total_price. $currency, 'pay' => true]
            ]
        );

        $label = BotTextsController::getText($user_id, 'LiqPay', 'label');
        $LabeledPrice = json_encode(array(array('label' => $label.$external_id, 'amount' => bcmul($total_price, 100, 0))));

        $data['text'] = BotTextsController::getText($user_id, 'LiqPay', 'order_wait_pay');
        $data['chat_id'] = $user_id;
        Request::sendMessage($data);

        $description = BotTextsController::getText($user_id, 'LiqPay', 'description');
        $description = str_replace("___ID___", $external_id, $description);

        $data_pay = [
            'chat_id'       => $user_id,
            'title'         => BotTextsController::getText($user_id, 'LiqPay', 'title').$external_id,
            'description'   => $description,
            'payload'   => $external_id,
            'provider_token'   => BotSettingsController::getSettings($user_id, 'liqpay_token')['settings_value'],
            'start_parameter'   => 'pay',
            'is_flexible' => false,
            'need_shipping_address' => false,
            'currency'   => 'UAH',
            'prices'   => $LabeledPrice,
            'reply_markup' => $inline_keyboard
        ];
        $r = Request::sendInvoice($data_pay);
        Log::warning($r);
//        dd($data_pay, $r);

//        $data['text']      = 'debug: '.$r;
//        $data['chat_id']      = $user_id;
//        Request::sendMessage($data);

        self::sendWidget($user_id, $external_id);

    }

    public static function sendWidget($user_id, $external_id) {

        $order = BotOrdersNew::where('user_id', $user_id)->where('external_id', $external_id)->first();
        $presta_order = PrestaShop_Orders::where('id_order', $order->external_id)->first();
        $archi_order_id = $presta_order->archi_order_id;
        $total_price = $order->price_with_discount;
        $currency = BotTextsController::getText($user_id, 'System', 'currency');
        $text_button = BotTextsController::getText($user_id, 'LiqPay', 'pay');

//        $public_key = 'sandbox_i63655253006';
//        $private_key = 'sandbox_AimijfoL1Z6UkFPjxoUH19coldCX6lL6tS2Kvuks';
        $public_key = BotSettingsController::getSettings($user_id, 'liqpay_public_key')['settings_value'];
        $private_key = BotSettingsController::getSettings($user_id,'liqpay_private_key')['settings_value'];

        $liqpay = new LiqPaySDKController($public_key, $private_key);
        $res = $liqpay->api("request", array(
            'action'            => 'invoice_send',
            'version'           => '3',
            'amount'            => $total_price,
            'currency'          => 'UAH',
            'description'       => 'Telegram - замовлення Eco&Pizza №'.$external_id.' через бота ('.$order->phone.')',
            'order_id'          => 'id_'.$order->id.'___chat_'.$user_id.'___user_'.$user_id.'___order_'.$external_id.'___archiorderid_'.$archi_order_id.'___time_'.time(),
            'action_payment'    => 'pay',
            'phone'             => $order->phone,
            'email'             => $order->phone.'@ecopizza.com.ua',
            'sender_first_name' => $order->name,
            'server_url'        => route('liqpay_callback')
        ));

//        dd($res);
        $res = json_encode($res);
        $res = json_decode($res);
        if ($res->result == 'ok') {

            $inline_keyboard = new InlineKeyboard(
                [
                    ['text' => '💳 Сплатити ' .$total_price. ' грн', 'url' => $res->href]
                ]
            );
            $data = [
                'chat_id'      => $user_id,
                'text'         => 'Сплатити через платіжний віджет',
                'reply_markup' => $inline_keyboard,
            ];
            $result = Request::sendMessage($data);
            Log::warning($result);
        }

    }

    public static function liqpay_callback(LRequest $request) {

        $input = $request->except('_token');
//        dd($input);
        $ins_text = '';

        foreach($input as $key=>$val) {
            // echo "POST '$key' = '$val'; <br>";
            $ins_text .= "POST '$key' = '$val'; ";
        }

//        echo $ins_text;

        $payment = new BotPaymentsCallback();
        $payment->post_text = $ins_text;
        $payment->date_reg = date("Y-m-d H:i:s");
        $payment->save();

        $id_payment = $payment->id;

//        $private_key = 'sandbox_AimijfoL1Z6UkFPjxoUH19coldCX6lL6tS2Kvuks';
        $private_key = BotSettingsController::getSettings(null,'liqpay_private_key')['settings_value'];

        $sign_post = $input['signature'];
        $data_post = $input['data'];

        $sign = base64_encode( sha1( $private_key .  $data_post . $private_key , 1 ));

        if ($sign !== $sign_post) {
            die ('Singnature error<br />');
        }

        echo base64_decode($data_post).'<br />';

        $s = base64_decode($data_post);
        Log::warning('LIQPAY');
        Log::warning($s);
        $s = json_decode($s);

        $chat_and_user_and_order = explode("___", $s->order_id);
        $id_arr = explode("_", $chat_and_user_and_order[0]);
        $id = $id_arr[1];
        $chat = explode("_", $chat_and_user_and_order[1]);
        $chat_id = $chat[1];
        $user = explode("_", $chat_and_user_and_order[2]);
        $user_id = $user[1];
        $order = explode("_", $chat_and_user_and_order[3]);
        $order_id = $order[1];

        Log::warning('------------------------- LiqPay CALLBACK -----------------------------------');
        if ($s->action == 'pay' && $s->status == 'success') {
//            Log::warning($s);
            $bot_api_key = env('PHP_TELEGRAM_BOT_API_KEY');

            BotOrdersNew::where('external_id', $order_id)->update(['pay_yes' => 1]);
            PrestaShop_Orders::where('id_order', $order_id)->update(['current_state' => 14]);
//            SimplaOrders::where('id', $order_id)->update(['paid' => 1, 'payment_date' => date("Y-m-d H:i:s")]);
//            SimplaOrdersDuble::where('id', $order_id)->update(['paid' => 1, 'payment_date' => date("Y-m-d H:i:s")]);

            $text_ins = BotTextsController::getText($user_id, 'LiqPay', 'success');
            $text_ins = str_replace("___ID___", $order_id, $text_ins);

            $data['chat_id'] = $user_id;
            $data['parse_mode'] = 'html';
            $data['text'] = $text_ins;
            $result = Request::sendMessage($data);

            BotPaymentsCallback::where('id', $id_payment)->update(['result1' => $result]);

            $data['chat_id'] = '483966473';
            $data['parse_mode'] = 'html';
            $data['text'] = 'Появился новый заказ безнал.' . PHP_EOL . 'order_text: ' . $s->order_id;
            $result = Request::sendMessage($data);

            BotPaymentsCallback::where('id', $id_payment)->update(['result1' => $result]);

            Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);
            sleep(3);
            return StartCommandController::send_hello($user_id);

        }
        else {
            Log::error('ERROR PAY');
        }


    }
}

