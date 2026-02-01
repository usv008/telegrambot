<?php

namespace App\Http\Controllers\TelegramBotCherry;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Telegram\BotRaffleCherryController;
use App\Http\Controllers\Telegram\BotTextsController;
use App\Models\BotRaffleCherryTakeaway;
use App\Models\BotRaffleUsers;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;

class CallbackQueryCherryController extends Controller
{

    public static function execute($callback_data)
    {
//        Log::warning('CALLBACK DATA:');
//        Log::warning($callback_data);

        $callback_query_id = $callback_data['id'];
        $data = $callback_data['data'];
        $message = $callback_data['message'];
        $message_id = $message['message_id'];
        $chat = $message['chat'];
        $chat_id = $chat['id'];

        $text_answer = '';
        $show_alert = false;

        $cherry_bot = new TelegramBotCherryController;

        if (stripos($data, 'test___') !== false) {
            $text_answer = $text_answer.'test';
            $inline_keyboard = [[
                ['text' => 'Test2', 'callback_data' => 'test2___'],
                ['text' => 'Test2', 'callback_data' => 'test2___'],
            ]];
            $cherry_bot->editMessageReplyMarkup($chat_id, $message_id, $inline_keyboard);
        }
        elseif (stripos($data, 'test2___') !== false) {
            $text_answer = $text_answer.'test2';
            $inline_keyboard = [
                ['text' => 'Test', 'callback_data' => 'test___'],
                ['text' => 'Test', 'callback_data' => 'test___'],
            ];
            $cherry_bot->editMessageReplyMarkup($chat_id, $message_id, $inline_keyboard);
        }
        elseif (stripos($data, 'cherry_takeaway___') !== false) {
            $arr = explode("___", $data);
            $command = $arr[0];
            $user_id = $arr[1];
            $takeaway_id = $arr[2];
            $phone = $arr[3];
            $user_win = BotRaffleCherryTakeaway::getUserByUserIdAndId($user_id, $takeaway_id);
            $message_ids = json_decode($user_win->message_ids);
            $text = '✅ '.$user_win->name.PHP_EOL.$user_win->phone.PHP_EOL."Отримав чекушку П'яної вишні";

            if ($user_win->received == 0) {
                Log::warning('USER_WIN:');
                Log::warning('user_id: '.$user_id.'; take: '.$takeaway_id.'; phone: '.$phone);
                Log::warning('USER_WIN:');
                Log::warning($user_win);

                $text = $user_win->name.PHP_EOL.$user_win->phone.PHP_EOL."виграв чекушку П'яної вишні".PHP_EOL.PHP_EOL."Нехай переможець скаже тобі код, який я йому відправив.".PHP_EOL."А ти натисни на кномпу з цим кодом.";

                $codes = [];
                $client_code = random_int(100, 999);
                $update_code = BotRaffleCherryTakeaway::updateCodeByTekeawayIdAndUserId($user_id, $takeaway_id, $client_code);
                $codes[] = $client_code;
                for ($i = 1; $i <= 5; $i++) {
                    $random_code = random_int(100, 999);
                    if ($random_code !== $client_code && !in_array($random_code, $codes)) {
                        $codes[] = $random_code;
                    }
                    else {
                        $stop_random = 0;
                        while($stop_random == 0) {
                            $new_random_code = random_int(100, 999);
                            if ($random_code !== $client_code && !in_array($new_random_code, $codes)) {
                                $codes[] = $new_random_code;
                                $stop_random = 1;
                            }
                        }
                    }
                }
                shuffle($codes);
                $text_codes = 'code = '.$client_code.PHP_EOL;

                $keyboard_row = [];
                $keyboard_array = [];
                $i = 0;
                foreach ($codes as $code) {
                    $i++;
                    $keyboard_row[] = ['text' => $code, 'callback_data' => 'cherry_code___'.$user_id.'___'.$takeaway_id.'___'.$code];
                    $text_codes .= $code.'; ';
                    if ($i == 3) {
                        $keyboard_array[] = $keyboard_row;
                        $keyboard_row = [];
                        $i = 0;
                    }
                }
                $keyboard_array[] = [['text' => '⬅️ Назад', 'callback_data' => 'cherry_code_back___'.$user_id.'___'.$takeaway_id]];
                $inline_keyboard = $keyboard_array;

                $data_text = ['chat_id' => $user_id];
                $data_text['text'] = 'Скажи цей код, щоб забрати свій приз: '.PHP_EOL.$client_code;
                $data_text['reply_markup'] = Keyboard::remove(['selective' => true]);
                $send = Request::sendMessage($data_text);

                $text_answer = null;
                foreach ($message_ids as $admin_id => $admin_messsage_id) {
                    $cherry_bot->editMessageText($admin_id, $admin_messsage_id, $text, $inline_keyboard);
                }
                return true;
            }
            else {
                foreach ($message_ids as $admin_id => $admin_messsage_id) {
                    $cherry_bot->editMessageText($admin_id, $admin_messsage_id, $text, []);
                }
                return $cherry_bot->answerCallbackQuery($callback_query_id, ' ✅ вже отримано', false);
            }

        }
        elseif (stripos($data, 'cherry_code___') !== false) {
            $arr = explode("___", $data);
            $user_id = $arr[1];
            $takeaway_id = $arr[2];
            $code = $arr[3];
            $user_win = BotRaffleCherryTakeaway::getUserByUserIdAndId($user_id, $takeaway_id);
            $message_ids = json_decode($user_win->message_ids);
            $client_code = $user_win->code;
            if ($client_code == $code) {

                $clear_win = BotRaffleCherryController::clearWinByUserIdAndTAkeawayId($user_id, $takeaway_id);
                $setTakeaway = BotRaffleCherryTakeaway::setTakeawayByUserIdAndTakewayId($user_id, $takeaway_id);
                $text = '✅ '.$user_win->name.PHP_EOL.$user_win->phone.PHP_EOL."отримав чекушку П'яної вишні";

                foreach ($message_ids as $admin_id => $admin_messsage_id) {
                    $cherry_bot->editMessageText($admin_id, $admin_messsage_id, $text, []);
                }

                $data_text = ['chat_id' => $user_id];
                $data_text['text'] = 'Дякуємо за участь в акції!'.PHP_EOL.'Чекаємо на вас знову😉';
                $data_text['reply_markup'] = Keyboard::remove(['selective' => true]);
                $send = Request::sendMessage($data_text);

                return $cherry_bot->answerCallbackQuery($callback_query_id, ' ✅ Отримано', false);
            }
            else {
                $text = '🧐 '.$user_win->name.PHP_EOL.$user_win->phone.PHP_EOL."виграв чекушку П'яної вишні";
                $inline_keyboard = [
                    [
                        ['text' => 'Віддати йому', 'callback_data' => 'cherry_takeaway___'.$user_id.'___'.$takeaway_id.'___'.$user_win->phone],
                    ]
                ];

                foreach ($message_ids as $admin_id => $admin_messsage_id) {
                    $cherry_bot->editMessageText($admin_id, $admin_messsage_id, $text, $inline_keyboard);
                }

                return $cherry_bot->answerCallbackQuery($callback_query_id, 'Код невірний ❌', true);
            }
        }
        elseif (stripos($data, 'cherry_code_back___') !== false) {
            $arr = explode("___", $data);
            $user_id = $arr[1];
            $takeaway_id = $arr[2];
            $user_win = BotRaffleCherryTakeaway::getUserByUserIdAndId($user_id, $takeaway_id);
            $message_ids = json_decode($user_win->message_ids);

            $text = '🧐 '.$user_win->name.PHP_EOL.$user_win->phone.PHP_EOL."виграв чекушку П'яної вишні";
            $inline_keyboard = [
                [
                    ['text' => 'Віддати йому', 'callback_data' => 'cherry_takeaway___'.$user_id.'___'.$takeaway_id.'___'.$user_win->phone],
                ]
            ];
            foreach ($message_ids as $admin_id => $admin_messsage_id) {
                $cherry_bot->editMessageText($admin_id, $admin_messsage_id, $text, $inline_keyboard);
            }

        }
        else {
            $text_answer = $text_answer.'???';
            return $cherry_bot->answerCallbackQuery($callback_query_id, $text_answer, $show_alert);
        }

    }

}
