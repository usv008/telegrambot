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

class BotFeedBackButtonsController extends Controller
{

    public static function get_feedback_buttons($user_id) {

        $inline_keyboard = new InlineKeyboard([]);

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'FeedBack', 'feedback_yes');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'FeedBack', 'feedback_no');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_feedback_buttons_step($user_id, $feedback_id, $step) {

        $inline_keyboard = new InlineKeyboard([]);

        $k = 0;
        $button = [];
        for($i = 1; $i <= 10; $i++) {

            $k++;
            $button[$k] = new InlineKeyboardButton(['text' => $i, 'callback_data' => 'feedback___'.$feedback_id.'___'.$step.'___'.$i]);
            if ($k == 5) {
                $inline_keyboard->addRow($button[1], $button[2], $button[3], $button[4], $button[5]);
                $k = 0;
            }

        }

        return $inline_keyboard;

    }

    public static function get_feedback_buttons_finish($user_id) {

        $inline_keyboard = new InlineKeyboard([]);

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'CallMe', 'call_me_ok');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'More', 'share');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'switch_inline_query' => $data]));

        return $inline_keyboard;

    }

}
