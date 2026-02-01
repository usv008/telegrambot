<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotRaffle;
use App\Models\BotRaffleCherry;
use App\Models\BotRaffleCherryTest;
use App\Models\BotRaffleTest;
use App\Http\Controllers\Controller;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

class BotRaffleCherryButtonsController extends Controller
{

    private static string $command = 'Raffle_cherry';

    public static function get_menu_buttons($user_id, $raffle_try) {

        $command = 'Raffle_cherry';
        $inline_keyboard = new InlineKeyboard([]);

//        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'rules');
//        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));
//
//        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'start_game');
//        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));
//
//        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'start_test_game');
//        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));
//
//        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'goto_start');
//        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_buttons_start_game($user_id, $raffle_id) {

        $inline_keyboard = new InlineKeyboard([]);
        $inline_keyboard->addRow(
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry___'.$raffle_id.'___1']),
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry___'.$raffle_id.'___2']),
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry___'.$raffle_id.'___3']),
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry___'.$raffle_id.'___4'])
        );
        $inline_keyboard->addRow(
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry___'.$raffle_id.'___5']),
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry___'.$raffle_id.'___6']),
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry___'.$raffle_id.'___7']),
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry___'.$raffle_id.'___8'])
        );
        $inline_keyboard->addRow(
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry___'.$raffle_id.'___9']),
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry___'.$raffle_id.'___10']),
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry___'.$raffle_id.'___11']),
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry___'.$raffle_id.'___12'])
        );

        return $inline_keyboard;

    }

    public static function get_buttons_update_game($user_id, $raffle_id) {

        $inline_keyboard = new InlineKeyboard([]);

        $raffle_p = BotRaffleCherry::where('id', $raffle_id)->where('user_id', $user_id)->first();
        $raffle_try = $raffle_p['raffle_try'];

        $guessed = 0;
        $p = 0;
        $arr = [];
        for ($i = 1; $i <= 12; $i++) {

            $p++;
            if ($raffle_p['p'.$i] == '___🍒') {
                $guessed++;
                $text = '🍒';
            }
            elseif ($raffle_p['p'.$i] == '___😐') {
                $text = '😐';
            }
            else {
                $text = $raffle_try == 8 ? '❓ ('.$raffle_p['p'.$i].')' : '❓';
            }

            $arr[$p] = new InlineKeyboardButton(['text' => $text, 'callback_data' => 'rafflecherry___'.$raffle_id.'___'.$i]);
            if ($p == 4) {
                $inline_keyboard->addRow($arr[1], $arr[2], $arr[3], $arr[4]);
                $p = 0;
            }

        }

        return $inline_keyboard;

    }

    public static function get_no_win_buttons($user_id) {

        $inline_keyboard = new InlineKeyboard([]);

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, self::$command, 'raffle_again');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'goto_start');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_no_win_no_try_buttons($user_id) {

        $inline_keyboard = new InlineKeyboard([]);

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, self::$command, 'share');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'switch_inline_query' => $data]));

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'goto_start');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_win_buttons($user_id) {

        $inline_keyboard = new InlineKeyboard([]);

//        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, self::$command, 'send_rules');
//        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

//        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, self::$command, 'raffle_takeaway_cherry');
//        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));
//
//        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, self::$command, 'raffle_takeaway_cherry_ecopizza');
//        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'goto_start');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_no_game_buttons($user_id) {

        $inline_keyboard = new InlineKeyboard([]);

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, self::$command, 'raffle_takeaway_cherry');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Start', 'begin');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, self::$command, 'raffle_add_pizza_tomorrow');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_ask_pizza_add_buttons($user_id) {

        $inline_keyboard = new InlineKeyboard([]);

        $inline_keyboard = BotButtonsInlineController::getRafflePizzasButtonsInline($user_id, $inline_keyboard);

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, self::$command, 'raffle_no_add_pizza_new');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_buttons_rules_send($user_id) {

        $inline_keyboard = new InlineKeyboard([]);

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Start', 'begin');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'goto_start');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_buttons_start_test_game($user_id, $raffle_id) {

        $inline_keyboard = new InlineKeyboard([]);
        $inline_keyboard->addRow(
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry_test___'.$raffle_id.'___1']),
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry_test___'.$raffle_id.'___2']),
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry_test___'.$raffle_id.'___3']),
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry_test___'.$raffle_id.'___4'])
        );
        $inline_keyboard->addRow(
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry_test___'.$raffle_id.'___5']),
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry_test___'.$raffle_id.'___6']),
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry_test___'.$raffle_id.'___7']),
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry_test___'.$raffle_id.'___8'])
        );
        $inline_keyboard->addRow(
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry_test___'.$raffle_id.'___9']),
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry_test___'.$raffle_id.'___10']),
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry_test___'.$raffle_id.'___11']),
            new InlineKeyboardButton(['text' => '❓', 'callback_data' => 'rafflecherry_test___'.$raffle_id.'___12'])
        );

        return $inline_keyboard;

    }

    public static function get_buttons_update_test_game($user_id, $raffle_id) {

        $inline_keyboard = new InlineKeyboard([]);

        $raffle_p = BotRaffleCherryTest::where('id', $raffle_id)->where('user_id', $user_id)->first();
        $raffle_try = $raffle_p['raffle_try'];

        $guessed = 0;
        $p = 0;
        $arr = [];
        for ($i = 1; $i <= 12; $i++) {

            $p++;
            if ($raffle_p['p'.$i] == '___🍒') {
                $guessed++;
                $text = '🍒';
            }
            elseif ($raffle_p['p'.$i] == '___😐') {
                $text = '😐';
            }
            else {
                $text = $raffle_try == 8 ? '❓ ('.$raffle_p['p'.$i].')' : '❓';
            }

            $arr[$p] = new InlineKeyboardButton(['text' => $text, 'callback_data' => 'rafflecherry_test___'.$raffle_id.'___'.$i]);
            if ($p == 4) {
                $inline_keyboard->addRow($arr[1], $arr[2], $arr[3], $arr[4]);
                $p = 0;
            }

        }

        return $inline_keyboard;

    }

    public static function get_test_win_buttons($user_id) {

        $inline_keyboard = new InlineKeyboard([]);

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, self::$command, 'start_test_game_again');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, self::$command, 'send_rules');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'goto_start');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

    public static function get_no_test_win_buttons($user_id) {

        $inline_keyboard = new InlineKeyboard([]);

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, self::$command, 'start_test_game_again');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'goto_start');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));

        return $inline_keyboard;

    }

}
