<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use Longman\TelegramBot\Request;

class BotGameDiceController extends Controller
{

    public static function startGame($user_id, $data)
    {

        $emoji = $data['emoji'];
        $value = $data['value'];

        $data_message = ['chat_id' => $user_id];
        $raffle_try = BotRaffleController::getRaffleTry($user_id);
        $raffle_try = 1000;
        $check_win = BotRaffleController::checkUserWin($user_id);
        $check_win = 0;
        if ($check_win == 0 && $raffle_try > 0) {

            $data_message['emoji'] = $emoji;
            $send = Request::sendDice($data_message);
            if ($send->getOk()) {
//                $update_raffle_try = BotRaffleController::updateRaffleTry($user_id,'minus');
                $result = $send->result;
                $dice = $result->dice;
                sleep(4);
                $send_typing = Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);
                sleep(1);
                $data_message = ['chat_id' => $user_id];
                $data_message['parse_mode'] = 'html';
                if ($dice['value'] == $value) {
//                    $update_raffle_user = BotRaffleController::updateRaffleUsersWin($user_id);
                    $data_message['text'] = '🎉';
                }
                else $data_message['text'] = '🙁';
                $send_message = Request::sendMessage($data_message);

                $data_message = ['chat_id' => $user_id];
                $data_message['text'] = 'Игры';
                $data_message['parse_mode'] = 'html';
                $data_message['reply_markup'] = BotButtonsInlineController::getGamesButtonsInline($user_id);
                $send_text = Request::sendMessage($data_message);
            }

        }
        elseif ($raffle_try == 0) {

            $text = BotTextsController::getText($user_id, 'Raffle', 'message_no_win_no_try');
//            $inline_keyboard = BotRaffleButtonsController::get_no_win_no_try_buttons($user_id);

            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = $text;
            $data_text['parse_mode'] = 'html';
//            $data_text['reply_markup'] = $inline_keyboard;
            $send_text = Request::sendMessage($data_text);

        }
        else {

            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = BotTextsController::getText($user_id, 'Raffle', 'message_no_game');
            $data_text['parse_mode'] = 'html';
            $data_text['reply_markup'] = BotRaffleButtonsController::get_no_game_buttons($user_id);
            $send_text = Request::sendMessage($data_text);

        }
    }

}
