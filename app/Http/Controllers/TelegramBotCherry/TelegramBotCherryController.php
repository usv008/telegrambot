<?php

namespace App\Http\Controllers\TelegramBotCherry;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramBotCherryController extends Controller
{

    private string $api_key;
    private string $url;

    public function __construct()
    {
        $this->api_key = env('DRINKCHERRY_TELEGRAM_BOT_API_KEY');
        $this->url = "https://api.telegram.org/bot$this->api_key";
    }

    public function handle(Request $request)
    {
        $input = $request->json()->all();
        $inline_keyboard = null;
        Log::warning('TELEGRAM DRINKCHERRY:');
        Log::warning($input);

        // Проверяем, что пришло текстовое сообщение
        if (isset($input['message']['text'])) {
            $chat_id = $input['message']['chat']['id'];
            $text = $input['message']['text'];

            // Обрабатываем команду /start
            if ($text == '/start') {
                $text_message = 'Привіт! Я бот "П\'яної вишні"';
                $inline_keyboard = [
                    [
                        ['text' => 'Test', 'callback_data' => 'test___'],
                        ['text' => 'Test', 'callback_data' => 'test___'],
                        ['text' => 'Test', 'callback_data' => 'test___'],
                    ],
                    [
                        ['text' => 'Test', 'callback_data' => 'test___'],
                        ['text' => 'Test', 'callback_data' => 'test___'],
                        ['text' => 'Test', 'callback_data' => 'test___'],
                    ]
                ];
            } else {
                $text_message = 'Ти сказав: ' . $text;
            }

            // Отправляем ответ обратно в Telegram
            $this->sendTelegramMessage($chat_id, $text_message, $inline_keyboard);
        }
        // Проверяем, что пришло событие о нажатой кнопке button inline
        if (isset($input['callback_query'])) {
            if (isset($input['callback_query']['data'])) {
                CallbackQueryCherryController::execute($input['callback_query']);
            }
        }

        return 'ok';
    }

    public function sendTelegramMessage($chat_id, $text, $inline_keyboard = null)
    {
        $url = $this->url . "/sendMessage";
        $params = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => 'html',
        ];
        if ($inline_keyboard) {
            $inline_keyboard = json_encode(['inline_keyboard' => $inline_keyboard]);
            $params['reply_markup'] = $inline_keyboard;
        }
        return $this->sendRequest($url, $params);
    }

    public function answerCallbackQuery($callback_query_id, $text = null, $show_alert = false)
    {
        $url = $this->url . "/answerCallbackQuery";
        $params = [
            'callback_query_id' => $callback_query_id,
            'text' => $text,
            'show_alert' => $show_alert,
            'cache_time' => 0,
        ];
        $this->sendRequest($url, $params);
    }

    public function editMessageText($chat_id, $message_id, $text, $reply_markup, $check_inline_keyboard = true)
    {
        $url = $this->url . "/editMessageText";
        $params = [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $text,
            'parse_mode' => 'html',
        ];
        if ($reply_markup && $check_inline_keyboard) {
            $inline_keyboard = json_encode(['inline_keyboard' => $reply_markup]);
            $params['reply_markup'] = $inline_keyboard;
        }
        return $this->sendRequest($url, $params);
    }

    public function editMessageReplyMarkup($chat_id, $message_id, $reply_markup, $check_inline_keyboard = true)
    {
        $url = $this->url . "/editMessageReplyMarkup";
        $params = [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
        ];
        if ($reply_markup && $check_inline_keyboard) {
            $inline_keyboard = json_encode(['inline_keyboard' => $reply_markup]);
            $params['reply_markup'] = $inline_keyboard;
        }
        return $this->sendRequest($url, $params);
    }

    public function sendRequest($url, $params)
    {
        $client = new \GuzzleHttp\Client();
        $request = $client->post($url, ['json' => $params]);
        $result = $request->getBody();
        Log::warning('SEND TO TELEGRAM REQUEST DRINK_CHERRY:');
        if ($result) {
            Log::warning($result);
        }
        else {
            Log::warning('Щось зажурилося...');
        }
        return $result;
    }

}
