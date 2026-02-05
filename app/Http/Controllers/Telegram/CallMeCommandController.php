<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotOrder;
use App\Models\BotOrdersNew;
use App\Models\BotSettingsSticker;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request as LRequest;

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;

use App\Models\BotSettingsTexts;
use App\Models\BotSettingsButtonsInline;

class CallMeCommandController extends Controller
{

    public static function enter_name($user_id, $text) {

        $command = 'CallMe';
        $name = 'call_me0';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command);

        // Вытягиваем из БД текст для сообщения
        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_text['parse_mode'] = 'html';

        $keyboard_bottom = new Keyboard([]);

        $orders = BotOrdersNew::where('user_id', $user_id)->groupby('name')->distinct()->get();
        foreach ($orders as $order) {
            $keyboard_bottom->addRow($order->name);
        }
        $buttons = BotButtonsController::getButtons($user_id,'System', ['cancel']);
        foreach ($buttons as $button) {

            $keyboard_bottom->addRow($button);

        }

        $keyboard_b = $keyboard_bottom
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->setSelective(false);

        $data_text['reply_markup'] = $keyboard_b;

        $send_text = Request::sendMessage($data_text);


    }

    public static function enter_phone($user_id, $text) {

        $command = 'CallMe';
        $name = 'call_me1';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command);

        // Вытягиваем из БД текст для сообщения
        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_text['parse_mode'] = 'html';

        $keyboard_bottom = new Keyboard([]);

        $orders = BotOrdersNew::where('user_id', $user_id)->groupby('phone')->distinct()->get();
        foreach ($orders as $order) {
            $keyboard_bottom->addRow($order->phone);
        }

        $buttons = BotButtonsController::getButtons($user_id,'System', ['contact']);
        foreach ($buttons as $button) {
            $keyboard_bottom->addRow((new KeyboardButton($button))->setRequestContact(true));
        }

        $buttons = BotButtonsController::getButtons($user_id,'System', ['cancel']);
        foreach ($buttons as $button) {
            $keyboard_bottom->addRow($button);
        }

        $keyboard_b = $keyboard_bottom
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->setSelective(false);

        $data_text['reply_markup'] = $keyboard_b;

        $send_text = Request::sendMessage($data_text);

    }

    public static function send_ok($user_id, $user_name, $user_tel) {

        $command = 'CallMe';
        $name = 'call_me_ok';

        /////////////////// To Me //////////////////////////////
        $data_admin = ['chat_id' => '522750680'];
        $data_admin['parse_mode'] = 'html';
        $data_admin['text'] = '<a href="https://telegrambot.ecopizza.com.ua/admin/bot/users/'.$user_id.'">'.$user_name.'</a> ждет звонка по тел: '.$user_tel;
        Request::sendMessage($data_admin);

        ///////////////// To Chat //////////////////////////
        $data_admin = ['chat_id' => '-1002252943437'];
        $data_admin['parse_mode'] = 'html';
        $data_admin['text'] = '<a href="https://telegrambot.ecopizza.com.ua/admin/bot/users/'.$user_id.'">'.$user_name.'</a> ждет звонка по тел: '.$user_tel;
        $g = Request::sendMessage($data_admin);

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command);

        $data_text = ['chat_id' => $user_id];
        $data_text['parse_mode'] = 'html';

        // Удаляем клавиатуру снизу
        $data_text['text'] = BotTextsController::getText($user_id, $command, $name.'0');
        $data_text['reply_markup'] = Keyboard::remove(['selective' => true]);
        $send_text = Request::sendMessage($data_text);

        // Вытягиваем из БД текст для сообщения
        $data_text['text'] = BotTextsController::getText($user_id, $command, $name);

        // Формируем кнопки Inline
        $inline_keyboard = BotButtonsInlineController::getButtonsInline($user_id, $command, null);

        $data_text['reply_markup'] = $inline_keyboard;

        $send_text = Request::sendMessage($data_text);

    }


}
