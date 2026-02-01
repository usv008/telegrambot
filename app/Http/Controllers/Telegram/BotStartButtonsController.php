<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotCart;
use App\Models\BotMenu;
use App\Models\BotSettings;
use App\Models\BotUsersNav;
use App\Models\Simpla_Categories;
use App\Models\Simpla_Complect_Products;
use App\Models\Simpla_Images;
use App\Models\Simpla_Options;
use App\Models\Simpla_Products;
use App\Models\Simpla_Variants;
use Illuminate\Http\Request as LRequest;
use Longman\TelegramBot\Request;
use App\Http\Controllers\Controller;
use App\Models\BotSettingsButtons;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use PHPUnit\ExampleExtension\Comparable;

class BotStartButtonsController extends Controller
{

    public static function get_hello_buttons($user_id, $check_win = 0)
    {

        $command = 'Start';

        $inline_keyboard = new InlineKeyboard([]);

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'begin');
        if ($check_win == 0) {
            $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'web_app' => ['url' => route('show_new_menu', ['user_id' => $user_id])]]));
        }
        else {
            $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));
        }

//        if (BotOrderController::countOrderFromUserId($user_id) > 0) {
//            list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'More', 'my_orders');
//            $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'switch_inline_query_current_chat' => $data]));
//        }

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'contact_us');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'game');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Start', 'lang');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'more');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

//        if ($user_id == '522750680') {
//            $inline_keyboard->addRow(new InlineKeyboardButton(['text' => 'test wayforpay', 'callback_data' => 'test_wayforpay']));
//        }

//        if ($user_id == '522750680') {
//            $inline_keyboard->addRow(new InlineKeyboardButton(['text' => 'Оценки', 'callback_data' => 'feedback_start___']));
//        }

        return $inline_keyboard;

    }

}
