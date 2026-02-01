<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotFeedBack;
use App\Models\BotMenu;
use App\Models\BotOrder;
use App\Models\BotOrderContent;
use App\Models\BotOrders;
use App\Models\BotOrdersNew;
use App\Models\BotRaffle;
use App\Models\BotSettingsSticker;
use App\Models\BotUsersNav;
use App\Http\Controllers\Controller;

use Longman\TelegramBot\Entities\InputMessageContent\InputTextMessageContent;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;

use App\Models\BotSettingsTexts;
use App\Models\BotSettingsButtonsInline;
use SebastianBergmann\CodeCoverage\Report\PHP;

class FeedBackCommandController extends Controller
{

    public static function execute($user_id)
    {

        if ($user_id !== null) {

            Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

            $command = 'FeedBack';
            $name = 'feedback';

            // Записываемся в историю
            BotUserHistoryController::insertToHistory($user_id, 'open', $command);

            // Отправляем стикер приветствия
            $data_sticker = ['chat_id' => $user_id];
            $data_sticker['sticker'] = BotStickerController::getSticker($user_id, $command);
            $data_sticker['reply_markup'] = Keyboard::remove(['selective' => true]);
            $send_sticker = Request::sendSticker($data_sticker);

            // Вытягиваем из БД текст для сообщения
            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
            $data_text['parse_mode'] = 'html';
            $data_text['reply_markup'] = BotFeedBackButtonsController::get_feedback_buttons($user_id);
            $send_text = Request::sendMessage($data_text);

            $result = json_decode($send_text);
            if ($result->ok == 1) {
                return true;
            }
            else {
                return false;
            }

        }
        else return null;

    }

    public static function step_sushi($user_id, $message_id)
    {

        $feedback_id = BotFeedBackController::addFeedBack($user_id);

        if ($feedback_id !== null) {

            $simpla_id = BotFeedBack::where('id', $feedback_id)->first()['order_id'];
            $order_id = BotOrdersNew::where('external_id', $simpla_id)->first()['id'];
            $check_sushi = BotOrderContent::where('order_id', $order_id)
                ->where(function ($query) {
                    $query->where('category', 'sushi')
                        ->orWhere('category', 'sushi-i-sety')
                        ->orWhere('category', 'roli')
                        ->orWhere('category', 'sushki')
                        ->orWhere('category', 'sety')
                        ->orWhere('category', 42)
                        ->orWhere('category', 43)
                        ->orWhere('category', 44);
                })
                ->count();

            if ($check_sushi > 0) {

                Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

                $command = 'FeedBack';
                $name = 'feedback_step_0';

                // Записываемся в историю
                BotUserHistoryController::insertToHistory($user_id, 'open', $command.'('.$name.')');

                // Вытягиваем из БД текст для сообщения
                $data_text = ['chat_id' => $user_id];
                $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
                $data_text['parse_mode'] = 'html';
                $data_text['reply_markup'] = BotFeedBackButtonsController::get_feedback_buttons_step($user_id, $feedback_id, 0);
                $data_text['message_id'] = $message_id;
                $send_text = Request::editMessageText($data_text);

            }
            else self::step_1($user_id, $feedback_id, $message_id);

        }

    }

    public static function step_1($user_id, $feedback_id, $message_id)
    {
        $simpla_id = BotFeedBack::where('id', $feedback_id)->first()['order_id'];
        $order_id = BotOrdersNew::where('external_id', $simpla_id)->first()['id'];
        $check_pizza = BotOrderContent::where('order_id', $order_id)
            ->where(function ($query) {
                $query->where('category', 'pizza')
                    ->orWhere('category', 'salaty')
                    ->orWhere('category', 'zakuski')
                    ->orWhere('category', 3)
                    ->orWhere('category', 5)
                    ->orWhere('category', 6)
                    ->orWhere('category', 7)
                    ->orWhere('category', 8);
            })
            ->count();

        if ($check_pizza > 0) {

            Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

            $command = 'FeedBack';
            $name = 'feedback_step_1';

            // Записываемся в историю
            BotUserHistoryController::insertToHistory($user_id, 'open', $command.'('.$name.')');

            if ($feedback_id !== null) {
                // Вытягиваем из БД текст для сообщения
                $data_text = ['chat_id' => $user_id];
                $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
                $data_text['parse_mode'] = 'html';
                $data_text['reply_markup'] = BotFeedBackButtonsController::get_feedback_buttons_step($user_id, $feedback_id, 1);
                $data_text['message_id'] = $message_id;
                $send_text = Request::editMessageText($data_text);
            }
        }
        else self::step_2($user_id, $feedback_id, $message_id);

    }

    public static function step_2($user_id, $feedback_id, $message_id)
    {

        $simpla_id = BotFeedBack::where('id', $feedback_id)->first()['order_id'];
        $order = BotOrdersNew::where('external_id', $simpla_id)->first();

        if ($order->delivery_id == 6) {
            Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

            $command = 'FeedBack';
            $name = 'feedback_step_2';

            // Записываемся в историю
            BotUserHistoryController::insertToHistory($user_id, 'open', $command.'('.$name.')');

            if ($feedback_id !== null) {
                // Вытягиваем из БД текст для сообщения
                $data_text = ['chat_id' => $user_id];
                $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
                $data_text['parse_mode'] = 'html';
                $data_text['reply_markup'] = BotFeedBackButtonsController::get_feedback_buttons_step($user_id, $feedback_id, 2);
                $data_text['message_id'] = $message_id;
                $send_text = Request::editMessageText($data_text);
            }
        }
        else self::step_3($user_id, $feedback_id, $message_id);

    }

    public static function step_3($user_id, $feedback_id, $message_id)
    {

        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        $command = 'FeedBack';
        $name = 'feedback_step_3';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command.'('.$name.')');

        if ($feedback_id !== null) {
            // Вытягиваем из БД текст для сообщения
            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
            $data_text['parse_mode'] = 'html';
            $data_text['reply_markup'] = BotFeedBackButtonsController::get_feedback_buttons_step($user_id, $feedback_id, 3);
            $data_text['message_id'] = $message_id;
            $send_text = Request::editMessageText($data_text);
        }

    }

    public static function step_4($user_id, $feedback_id, $message_id)
    {

        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        $command = 'FeedBack';
        $name = 'feedback_comment';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command.'('.$name.')');

        if ($feedback_id !== null) {

            $data_text = ['chat_id' => $user_id];
            $data_text['message_id'] = $message_id;
            Request::deleteMessage($data_text);

            // Вытягиваем из БД текст для сообщения
            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
            $data_text['parse_mode'] = 'html';
            $keyboard_bottom = new Keyboard([]);

            $buttons = BotButtonsController::getButtons($user_id, 'Order', ['no_comments']);
            foreach ($buttons as $button) {
                $keyboard_bottom->addRow($button);
            }

            $keyboard_b = $keyboard_bottom
                ->setResizeKeyboard(true)
                ->setOneTimeKeyboard(true)
                ->setSelective(false);
            $data_text['reply_markup'] = $keyboard_b;
            $send_text = Request::sendMessage($data_text);
            BotUsersNavController::updateValue($user_id, 'change_key', 'feedback_comment');

        }

    }

    public static function step_finish($user_id, $feedback_id)
    {

        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        $command = 'FeedBack';
        $name = 'feedback_finish';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command.'('.$name.')');

        $remove_keyboard = StartCommandController::removeKeyboardBottom($user_id);

        if ($feedback_id !== null) {
            // Вытягиваем из БД текст для сообщения
            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
            $data_text['parse_mode'] = 'html';
            $data_text['reply_markup'] = BotFeedBackButtonsController::get_feedback_buttons_finish($user_id);
            $send_text = Request::sendMessage($data_text);
        }

    }

}
