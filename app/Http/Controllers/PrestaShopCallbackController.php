<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as Request;
use Illuminate\Support\Facades\Log;

class PrestaShopCallbackController extends Controller
{

    public static function execute(Request $request)
    {
        $input = $request->except('_token');
        Log::info('-------CALLBACK PRESTA------------');
        Log::info($input);

        $order_id = $input['order_id'];
//        $order = BotOrdersNew::where('external_id', $order_id)->first();
        $user_id = '522750680';
//        $user_id = $order->user_id;

        $data = ['chat_id' => $user_id];
        $data['parse_mode'] = 'html';
        $data['text'] = 'callback ok';
        $send_message = \Longman\TelegramBot\Request::sendMessage($data);
//        var_dump($input);
        return 'bot callback ok';
    }

}
