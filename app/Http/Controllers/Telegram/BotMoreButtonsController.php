<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotRaffle;
use App\Models\BotRaffleTest;
use App\Models\BotSettings;
use App\Http\Controllers\Controller;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

class BotMoreButtonsController extends Controller
{

    public static function get_more_buttons($user_id) {

        $inline_keyboard = new InlineKeyboard([]);

//       list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'More', 'webcamera');
//        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'url' => $data]));
//        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'url' => BotSettings::getWebCameraLink()]));

        list($text1, $data1) = BotButtonsInlineController::getButtonInline($user_id, 'More', 'cashback');
//        list($text2, $data2) = BotButtonsInlineController::getButtonInline($user_id, 'More', 'my_orders');
        $inline_keyboard->addRow(
            new InlineKeyboardButton(['text' => $text1, 'callback_data' => $data1])//,
//            new InlineKeyboardButton(['text' => $text2, 'switch_inline_query_current_chat' => $data2])
        );

        list($text1, $data1) = BotButtonsInlineController::getButtonInline($user_id, 'More', 'stocks');
        list($text2, $data2) = BotButtonsInlineController::getButtonInline($user_id, 'More', 'reviews');
        $inline_keyboard->addRow(
            new InlineKeyboardButton(['text' => $text1, 'callback_data' => $data1]),
            new InlineKeyboardButton(['text' => $text2, 'callback_data' => $data2])
        );

//        list($text1, $data1) = BotButtonsInlineController::getButtonInline($user_id, 'More', 'about_us');
//        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'More', 'help');
//        $inline_keyboard->addRow(
//            new InlineKeyboardButton(['text' => $text1, 'callback_data' => $data1]),
//            new InlineKeyboardButton(['text' => $text2, 'callback_data' => $data2])
//        );
//        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        list($text1, $data1) = BotButtonsInlineController::getButtonInline($user_id, 'More', 'our_channel');
        list($text2, $data2) = BotButtonsInlineController::getButtonInline($user_id, 'More', 'share');
        $inline_keyboard->addRow(
            new InlineKeyboardButton(['text' => $text1, 'url' => $data1]),
            new InlineKeyboardButton(['text' => $text2, 'switch_inline_query' => $data2])
        );
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => '😎 Розробка бота 🎯', 'url' => 'https://t.me/Artyom_Morozoff']));

        return $inline_keyboard;

    }

    public static function get_actions_buttons($user_id) {

        $inline_keyboard = new InlineKeyboard([]);

        list($text1, $data1) = BotButtonsInlineController::getButtonInline($user_id, 'More', 'send_review_back');
//        list($text2, $data2) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'goto_start');
        $inline_keyboard->addRow(
            new InlineKeyboardButton(['text' => $text1, 'callback_data' => $data1])
        );
        return $inline_keyboard;

    }

    public static function get_reviews_buttons($user_id) {

        $inline_keyboard = new InlineKeyboard([]);

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'More', 'send_review');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        list($text1, $data1) = BotButtonsInlineController::getButtonInline($user_id, 'More', 'send_review_back');
        list($text2, $data2) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'goto_start');
        $inline_keyboard->addRow(
            new InlineKeyboardButton(['text' => $text1, 'callback_data' => $data1]),
            new InlineKeyboardButton(['text' => $text2, 'callback_data' => $data2])
        );

        return $inline_keyboard;

    }

    public static function get_send_review_ok_buttons($user_id) {

        $inline_keyboard = new InlineKeyboard([]);

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'More', 'send_review_ok');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_cashback_buttons($user_id) {

        $inline_keyboard = new InlineKeyboard([]);

        list($text1, $data1) = BotButtonsInlineController::getButtonInline($user_id, 'More', 'send_review_back');
        list($text2, $data2) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'goto_start');
        $inline_keyboard->addRow(
            new InlineKeyboardButton(['text' => $text1, 'callback_data' => $data1]),
            new InlineKeyboardButton(['text' => $text2, 'callback_data' => $data2])
        );

        return $inline_keyboard;

    }

}

