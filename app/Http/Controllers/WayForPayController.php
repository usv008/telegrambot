<?php

namespace App\Http\Controllers;

use App\Models\BotCart;
use App\Models\BotOrder;
use App\Models\BotPaymentsCallback;
use App\Models\EcoPizzaPaymentsHistrory;
use App\Http\Controllers\Telegram\BotOrderController;
use App\Http\Controllers\Telegram\BotSettingsController;
use App\Http\Controllers\Telegram\BotTextsController;
use App\Http\Controllers\Telegram\StartCommandController;
use App\Models\BotOrdersNew;
use App\Models\PrestaShop_Orders;
use App\Models\SimplaOrders;
use App\Models\SimplaOrdersDuble;
use Illuminate\Http\Request as LRequest;
use Illuminate\Support\Facades\Input;
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

class WayForPayController extends Controller
{

    public static function sendWidget($user_id, $order_id) {

        Log::info('Start Widget: user_id: '.$user_id.', order_id: '.$order_id.' --------------------------------------------------------------------------------------');

        if ($order_id == 'test') {
            $order_id .= (string)time();
            $total_price = '1';
            $phone = '';
        }
        else {
            $order = BotOrderController::getOrderFromSimplaId($user_id, $order_id);
            $total_price = $order['price_with_discount'];
            $phone = $order['phone'];
        }

        $orderReference = $order_id + 500000;
        $orderReference = (string)$orderReference;
//        $orderReference = (string)time();

        $merchantAccount = env('WAYFORPAY_MERCHANT_ACCOUNT');
        $key = env('WAYFORPAY_MERCHANT_KEY');

        $amount = $total_price;
        $currency = 'UAH';
        $productName = ["Оплата заказу №".$order_id];
        $productCount = ['1'];
        $productPrice = [$total_price];

        $time = time();
        $merchantDomainName = env('WAYFORPAY_MERCHANT_DOMAIN', 'ecopizza.com.ua');

        $string = "$merchantAccount;$merchantDomainName;$orderReference;$time;$amount;$currency;$productName[0];$productCount[0];$productPrice[0]";
        $hash = hash_hmac("md5", $string, $key);

        $data = [
            'transactionType' => 'CREATE_INVOICE',
            'merchantAccount' => $merchantAccount,
            'merchantAuthType' => 'SimpleSignature',
            'merchantDomainName' => $merchantDomainName,
            'merchantSignature' => $hash,
            'apiVersion' => 1,
            'orderReference' => $orderReference,
            'orderDate' => $time,
            'amount' => $amount,
            'currency' => $currency,
            'productName' => $productName,
            'productCount' => $productCount,
            'productPrice' => $productPrice,
            'defaultPaymentSystem' => 'card',
            'clientPhone' => $phone,
            'serviceUrl' => route('wayforpay_callback'),
        ];

        $data_string = json_encode($data, true);

        $website = 'https://api.wayforpay.com/api';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $website);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        $response = curl_exec($ch);
        curl_close($ch);

        Log::info('Send Widget: --------------------------------------------------------------------------------------');
        Log::info('Input: '.(string)$response);

        $result = json_decode($response, true);

        if ($result && isset($result['reason']) && $result['reason'] == 'Ok' && isset($result['invoiceUrl']) && $result['invoiceUrl'] !== null) {

            $currency = BotTextsController::getText($user_id, 'System', 'currency');
            $text_button = BotTextsController::getText($user_id, 'WayForPay', 'pay');

            $inline_keyboard = new InlineKeyboard(
                [
                    ['text' => $text_button .$total_price. $currency, 'url' => $result['invoiceUrl']]
                ]
            );

            $data = [
                'chat_id'      => $user_id,
                'text'         => $text_button = BotTextsController::getText($user_id, 'WayForPay', 'widget_pay'),
                'reply_markup' => $inline_keyboard,
            ];
            $result = Request::sendMessage($data);

        }

        return $result;

    }

    public static function isJSON($string){
        return is_string($string) && is_array(json_decode($string, true)) ? true : false;
    }

    public static function wayforpay_callback(LRequest $request) {

        $result_way4pay = $request->getContent();
        Log::info('JSON: '.(string)$result_way4pay);

//        $result_way4pay = json_decode($result_way4pay, true);


        Log::info('CallBack: --------------------------------------------------------------------------------------');

//        $result_way4pay = str_replace("_", ".", $result_way4pay);

        if(self::isJSON($result_way4pay)){

            Log::info('result_way4pay: '.$result_way4pay);
            $result_way4pay = json_decode($result_way4pay, true);

//        Log::info('result_way4pay: '.$result_way4pay);
//            Log::info('---ARRAY---');
//            foreach ($result_way4pay as $key => $value) {
//
//                Log::info($key.': '.$value);
//
//            }
//            Log::info('---END ARRAY---');

            Log::info('---MERCHANT: '.$result_way4pay['merchantAccount']);

            //        Log::info('merchantAccount: '.$result_way4pay['merchantAccount']);

            $string = ''.$result_way4pay['merchantAccount'].';'.$result_way4pay['orderReference'].';'.$result_way4pay['amount'].';'.$result_way4pay['currency'].';'.$result_way4pay['authCode'].';'.$result_way4pay['cardPan'].';'.$result_way4pay['transactionStatus'].';'.$result_way4pay['reasonCode'].'';
            $merchantAccount = env('WAYFORPAY_MERCHANT_ACCOUNT');
            $key = env('WAYFORPAY_MERCHANT_KEY');
            $hash = hash_hmac("md5", $string, $key);

            $order_external_id = $result_way4pay['orderReference'] - 500000;

            Log::info('order_external id: '.$order_external_id);
            Log::info('merchantSignature: '.$result_way4pay['merchantSignature']);
            Log::info('hash: '.$hash);

            if ($result_way4pay['merchantSignature'] == $hash) {

                $time = time();

                $string_answer = $result_way4pay['orderReference'].';'.'accept'.';'.$time;
                $hash_answer = hash_hmac("md5", $string_answer, $key);

                $answer_arr = ['orderReference' => $result_way4pay['orderReference'], 'status' => 'accept', 'time' => $time, 'signature' => $hash_answer];
                $answer_json = json_encode($answer_arr, true);
                Log::info('answer_json: '.$answer_json);

                echo $answer_json;

                $merchantAccount = $result_way4pay['merchantAccount'];
                $orderReference = $result_way4pay['orderReference'];
                $merchantSignature = $result_way4pay['merchantSignature'];
                $amount = $result_way4pay['amount'];
                $currency = $result_way4pay['currency'];
                $authCode = $result_way4pay['authCode'];
                $email = $result_way4pay['email'];
                $phone = $result_way4pay['phone'];
                $createdDate = $result_way4pay['createdDate'];
                $processingDate = $result_way4pay['processingDate'];
                $cardPan = $result_way4pay['cardPan'];
                $cardType = $result_way4pay['cardType'];
                $issuerBankCountry = $result_way4pay['issuerBankCountry'];
                $issuerBankName = $result_way4pay['issuerBankName'];
                $recToken = $result_way4pay['recToken'];
                $transactionStatus = $result_way4pay['transactionStatus'];
                $reason = $result_way4pay['reason'];
                $reasonCode = $result_way4pay['reasonCode'];
                $fee = $result_way4pay['fee'];
                $paymentSystem = $result_way4pay['paymentSystem'];
                $acquirerBankName = $result_way4pay['acquirerBankName'];
//            $cardProduct = $result_way4pay['cardProduct'];
//            $clientName = $result_way4pay['clientName'];

                if ($reason == 'Ok' && $transactionStatus == 'Approved') {

//                    $order_external_id = $orderReference;
                    $order = BotOrdersNew::where('external_id', $order_external_id)->first();

                    $user_id = $order->user_id;

                    if (isset($order_external_id) && $order_external_id !== null && $order->pay_yes == 0) {

                        BotOrdersNew::where('external_id', $order_external_id)->update(['pay_yes' => 1]);
                        PrestaShop_Orders::where('id_order', $order_external_id)->update(['current_state' => 8]);
//                        SimplaOrders::where('id', $simpla_id)->update(['paid' => 1, 'payment_date' => date("Y-m-d H:i:s"), 'note' => '']);
//                        SimplaOrdersDuble::where('id', $simpla_id)->update(['paid' => 1, 'payment_date' => date("Y-m-d H:i:s"), 'note' => '']);

                        $text_ins = BotTextsController::getText($user_id, 'LiqPay', 'success');
                        $text_ins = str_replace("___ID___", $order_external_id, $text_ins);

                        $data['chat_id'] = $user_id;
                        $data['parse_mode'] = 'html';
                        $data['text'] = $text_ins;
                        $result = Request::sendMessage($data);

                        Log::info('Отправил сообщение: '.$user_id);

//                    BotPaymentsCallback::where('id', $id_payment)->update(['result1' => $result]);

                        $data['chat_id'] = '483966473';
                        $data['parse_mode'] = 'html';
                        $data['text'] = 'Появился новый заказ безнал.' . PHP_EOL . 'order_text: ' . $order_external_id;
                        $result = Request::sendMessage($data);

                        Log::info('Отправил сообщение: Ozzi');

//                    BotPaymentsCallback::where('id', $id_payment)->update(['result1' => $result]);

                        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);
                        sleep(3);
                        StartCommandController::send_hello($user_id);

                    }

                }
                else echo 'Error';

            }

        }
        else Log::info('NOT JSON!!!');

        Log::info('End CallBack: --------------------------------------------------------------------------------------');

    }

    public static function wayforpay_ubuntu_callback(LRequest $request) {


        $result_way4pay = $request->getContent();
        Log::info('JSON: '.$result_way4pay);

//        $result_way4pay = json_decode($result_way4pay, true);


        Log::info('UBUNTU CallBack: --------------------------------------------------------------------------------------');

//        $result_way4pay = str_replace("_", ".", $result_way4pay);

        if(self::isJSON($result_way4pay)){

            Log::info('result_way4pay: '.$result_way4pay);

            $result_way4pay = json_decode($result_way4pay, true);
            Log::info('---MERCHANT: '.$result_way4pay['merchantAccount']);


//        Log::info('result_way4pay: '.$result_way4pay);
//            Log::info('---ARRAY---');
//            foreach ($result_way4pay as $key => $value) {
//
//                Log::info($key.': '.$value);
//
//            }
//            Log::info('---END ARRAY---');

            //        Log::info('merchantAccount: '.$result_way4pay['merchantAccount']);

            $string = ''.$result_way4pay['merchantAccount'].';'.$result_way4pay['orderReference'].';'.$result_way4pay['amount'].';'.$result_way4pay['currency'].';'.$result_way4pay['authCode'].';'.$result_way4pay['cardPan'].';'.$result_way4pay['transactionStatus'].';'.$result_way4pay['reasonCode'].'';
            $merchantAccount = env('WAYFORPAY_MERCHANT_ACCOUNT');
            $key = env('WAYFORPAY_MERCHANT_KEY');
            $hash = hash_hmac("md5", $string, $key);

            $archi_id = $result_way4pay['orderReference'];

            Log::info('archi id: '.$archi_id);
            Log::info('merchantSignature: '.$result_way4pay['merchantSignature']);
            Log::info('hash: '.$hash);

            if ($result_way4pay['merchantSignature'] == $hash) {

                $time = time();

                $string_answer = $result_way4pay['orderReference'].';'.'accept'.';'.$time;
                $hash_answer = hash_hmac("md5", $string_answer, $key);

                $answer_arr = ['orderReference' => $result_way4pay['orderReference'], 'status' => 'accept', 'time' => $time, 'signature' => $hash_answer];
                $answer_json = json_encode($answer_arr, true);
                Log::info('answer_json: '.$answer_json);

                echo $answer_json;

                $merchantAccount = $result_way4pay['merchantAccount'];
                $orderReference = $result_way4pay['orderReference'];
                $merchantSignature = $result_way4pay['merchantSignature'];
                $amount = $result_way4pay['amount'];
                $currency = $result_way4pay['currency'];
                $authCode = $result_way4pay['authCode'];
                $email = $result_way4pay['email'];
                $phone = $result_way4pay['phone'];
                $createdDate = $result_way4pay['createdDate'];
                $processingDate = $result_way4pay['processingDate'];
                $cardPan = $result_way4pay['cardPan'];
                $cardType = $result_way4pay['cardType'];
                $issuerBankCountry = $result_way4pay['issuerBankCountry'];
                $issuerBankName = $result_way4pay['issuerBankName'];
                $recToken = $result_way4pay['recToken'];
                $transactionStatus = $result_way4pay['transactionStatus'];
                $reason = $result_way4pay['reason'];
                $reasonCode = $result_way4pay['reasonCode'];
                $fee = $result_way4pay['fee'];
                $paymentSystem = $result_way4pay['paymentSystem'];
                $acquirerBankName = $result_way4pay['acquirerBankName'];
//            $cardProduct = $result_way4pay['cardProduct'];
//            $clientName = $result_way4pay['clientName'];

                if ($reason == 'Ok' && $transactionStatus == 'Approved') {

                    $archi_id = $orderReference;
                    EcoPizzaPaymentsHistrory::where('order_id', $archi_id)->update(['payd' => 1, 'status' => $transactionStatus, 'date_edit' => date("Y-m-d H:i:s")]);
                    Log::info('Order '.$archi_id.' PAYD');

                }
                elseif ($reason == 'Ok' && $transactionStatus == 'Refunded') {

                    $archi_id = $orderReference;
                    EcoPizzaPaymentsHistrory::where('order_id', $archi_id)->update(['payd' => 0, 'status' => $transactionStatus, 'date_edit' => date("Y-m-d H:i:s")]);
                    Log::info('Order '.$archi_id.' Refunded');

                }
                elseif ($transactionStatus == 'Declined') {

                    $archi_id = $orderReference;
                    EcoPizzaPaymentsHistrory::where('order_id', $archi_id)->update(['payd' => 0, 'status' => $transactionStatus, 'reason' => $reason, 'date_edit' => date("Y-m-d H:i:s")]);
                    Log::info('Order '.$archi_id.' Declined');

                }
                else echo 'Error';

            }

        }
        else Log::info('NOT JSON!!!');

        Log::info('End UBUNTU CallBack: --------------------------------------------------------------------------------------');

    }

}

