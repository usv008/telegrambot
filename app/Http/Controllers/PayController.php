<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as LRequest;

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

use App\Models\BotPaymentsCallback;
use App\Models\BotPaymentsWayforpay;

class PayController extends Controller
{

    public static function test_serviceUrl(LRequest $request) {

//        dd($request->all());
//        $data = (object) $request->json()->all();
//        dd($data);

//        dd($request);
        $input = $request->except('_token');
        $result = $input;
//        $result = html_entity_decode($result);
//        $myArray = json_decode($decodedText, true);
        $date_z = date("Y-m-d H:i:s");


        $result = json_encode($result);
        $result2 = json_encode($request);

//        $id = BotPaymentsWayforpay::insertGetId(['date_reg' => $date_z, 'result1' => $result]);
//        dd($result);

    }

    public static function test() {

//        $pay = BotPaymentsWayforpay::where('id', '37')->first();
//        $res = $pay->result1;
//        $res = json_encode($res, true);
//        $res = json_decode($res, true);
//        dd($res);
//        $text = '';



//        $pays = BotPaymentsWayforpay::skip(0)->take(10)->orderBy('id', 'desc')->get();
        $pays = BotPaymentsWayforpay::where('id', '36')->get();

        $arr = '';

        foreach ($pays as $key => $value) {

            $res = $value->result1;
            $res = html_entity_decode($res);
            $res = json_decode($res, true);

            foreach ($res as $k => $v) {

                $text2 = $k;
                $test = preg_split('/,/', $text2);
                $i = 0;
                foreach ($test as $t) {

                    $i++;
                    if ($i > 1) $arr .= ',';

                    $test2 = preg_split('/:/', $t);

                    if ($test2[0] == '"amount"') {
                        $test2[1] = '"'.$test2[1].'"';
                        $test2[1] = str_replace("_", "." , $test2[1]);
                    }
                    if ($test2[0] == '"fee"') {
                        $test2[1] = '"'.$test2[1].'"';
                        $test2[1] = str_replace("_", "." , $test2[1]);
                    }

                    $arr .= $test2[0].':';

                    $arr .= $test2[1];
                }

            }

        }

        dd(json_decode($arr, true));



        $time = time();
//        echo $time.'<br />';
//        $time = 1578022244;

        $merchantAccount = 'test_merch_n1';
//        $merchantAccount = 'ecopizzaua';
        $merchantDomainName = 'telegrambot.ecopizza.com.ua';
        $orderReference = 'order'.$time;
        $amount = 0.99;
        $currency = 'UAH';
        $productName = ['Test Pizza'];
        $productCount = ['1'];
        $productPrice = ['0.99'];

        $string = "$merchantAccount;$merchantDomainName;$orderReference;$time;$amount;$currency;$productName[0];$productCount[0];$productPrice[0]";
        $key = "flk3409refn54t54t*FNJRET";
//        $key = "aa41d922bfe28cf8bc814e7cb7101587916295bd";
        $hash = hash_hmac("md5", $string, $key);

//        echo $hash.'<br />';

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
//            'orderTimeout' => '49000',
            'productName' => $productName,
            'productCount' => $productCount,
            'productPrice' => $productPrice,
            'defaultPaymentSystem' => 'card',
            'clientPhone' => '380680222278',
            'serviceUrl' => 'https://telegrambot.ecopizza.com.ua/test_serviceUrl',
        ];

        $data_string = json_encode($data);
//        $data = json_decode($data);

//        dd($data);

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

        $result = json_decode($response);

        if ($result && isset($result->reason) && $result->reason == 'Ok' && isset($result->invoiceUrl) && $result->invoiceUrl !== null) {
            echo '<a href="'.$result->invoiceUrl.'" target="_blank">Оплатить</a><br />';
        }
        else echo 'Error: '.$result->reason;

                dd($result);


//        $ch = curl_init($website);
//
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
//        curl_setopt($ch, CURLOPT_POST, 1);
////        curl_setopt($ch, CURLOPT_URL, $website);
////        curl_setopt($ch, CURLOPT_POST, 1);
////        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
//
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
//        $server_output = curl_exec($ch);
//        $result = json_decode($server_output);
//
//        curl_close ($ch);
//
//        curl_setopt($ch, CURLOPT_POSTFIELDS,
//            "postvar1=value1&postvar2=value2&postvar3=value3");

//        if( $curl = curl_init($website.$data) ) {
//            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//            $result = curl_exec($curl);
//            curl_close($curl);
//
//            if (stripos($result, 'RESULT=OK') !== false) {
//                $res = str_replace('RESULT=OK', '', $result);
//                if (stripos($res, 'DATA=') !== false) {
//                    $res = str_replace('DATA=', '', $res);
//                    $res = trim($res);
//                }
//
//            } else $res = null;
//            $res = json_decode($res, true);
//
//        }
//        else $res = null;

//        echo $hash.'<br />';

        echo '
        <form method="post" action="https://secure.wayforpay.com/pay" accept-charset="utf-8">
  <input name="merchantAccount" value="test_merch_n1">
  <input name="merchantAuthType" value="SimpleSignature">
  <input name="merchantDomainName" value="telegrambot.ecopizza.com.ua">
  <input name="merchantSignature" value="'.$hash.'">
  <input name="orderReference" value="eco003">
  <input name="orderDate" value="'.$time.'">
  <input name="amount" value="0.99">
  <input name="currency" value="UAH">
  <input name="orderTimeout" value="49000">
  <input name="productName[]" value="Test Pizza">
  <input name="productPrice[]" value="0.99">
  <input name="productCount[]" value="1">
  <input name="defaultPaymentSystem" value="card">
  <button type="submit">Оплатить</button>
</form>
        ';


//        $summa = 0;
//        $pays = BotPaymentsCallback::where('date_reg', 'like', '2019-11-%')->orderBy('id', 'desc')->get();
//        foreach ($pays as $pay) {
//
//            $result = $pay['result1'];
//            $result = json_decode($result);
////            dd($result->ok);
//            if (isset($result) && isset($result->ok) && $result->ok == 'true') {
//
//                $result = $result->result->text;
//                //            $text_arr = explode(" ", json_encode($result));
//                $text_arr = explode(" ", $result);
////            $order_id = str_replace("\u2116", "", $text_arr[1]);
//                $order_id = str_replace("№", "", $text_arr[1]);
////            dd($order_id);
//
//                $price = SimplaOrders::where('id', $order_id)->first()['total_price'];
//                echo 'order_id: '.$order_id.'; price: '.$price.'<br />';
//                $summa += $price;
//
//            }
//
//        }

//        echo '<b>'.$summa.'</b>';
//        dd($result);

    }


}
