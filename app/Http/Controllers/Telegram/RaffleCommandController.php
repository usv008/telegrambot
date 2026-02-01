<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotCart;
use App\Models\BotCartNew;
use App\Models\BotMenu;
use App\Models\BotOrder;
use App\Models\BotRaffle;
use App\Models\BotSettingsSticker;
use App\Models\BotUsersNav;
use App\Http\Controllers\Controller;

use App\Models\Simpla_Categories;
use App\Models\Simpla_Images;
use App\Models\Simpla_Products;
use App\Models\Simpla_Variants;
use Illuminate\Http\Request as LRequest;

use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\InputMessageContent\InputTextMessageContent;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;

use App\Models\BotSettingsTexts;
use App\Models\BotSettingsButtonsInline;
use SebastianBergmann\CodeCoverage\Report\PHP;

class RaffleCommandController extends Controller
{

    public static function execute($user_id, $act, $message_id)
    {

        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        $command = 'Raffle';
        $name = 'show_menu';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command);

        BotRaffleController::updateTryForGameToday($user_id);

        $raffle_try = BotRaffleController::getRaffleTry($user_id);
        $chances = BotRaffleController::getTextChances($user_id, $raffle_try);

        $text = BotTextsController::getText($user_id, $command, $name);
        $text = str_replace("___RAFFLE_TRY___", $raffle_try, $text);
        $text = str_replace("___RAFFLE_TRY_CHANCES___", $chances, $text);

        $inline_keyboard = BotRaffleButtonsController::get_menu_buttons($user_id, $raffle_try);

        // Вытягиваем из БД текст для сообщения
        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = $text;
        $data_text['parse_mode'] = 'html';
        $data_text['reply_markup'] = $inline_keyboard;

        if ($act == 'edit') {

            $data_text['message_id'] = $message_id;
            $send_text = Request::editMessageText($data_text);

        }
        elseif ($act == 'send') {

            // Отправляем стикер
            $data_sticker = ['chat_id' => $user_id];
            $data_sticker['sticker'] = BotStickerController::getSticker($user_id, $command);
            $data_sticker['reply_markup'] = Keyboard::remove(['selective' => true]);
            $send_sticker = Request::sendSticker($data_sticker);

            $send_text = Request::sendMessage($data_text);

        }

    }

    public static function show_rules($user_id, $act, $message_id)
    {

        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        $command = 'Raffle';
        $name = 'show_rules';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command.' ('.$name.')');


        // Вытягиваем из БД текст для сообщения
        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_text['parse_mode'] = 'html';
        if ($act == 'edit') {
            $data_text['reply_markup'] = BotButtonsInlineController::getButtonsInline($user_id, $command, $name);
            $data_text['message_id'] = $message_id;
            $send_text = Request::editMessageText($data_text);
        }
        elseif ($act == 'send') {
            $data_text['reply_markup'] = BotRaffleButtonsController::get_buttons_rules_send($user_id);
            $send_text = Request::sendMessage($data_text);
        }

    }

    public static function start_game($user_id)
    {

        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        $command = 'Raffle';
        $name = 'start_game';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command.' ('.$name.')');

        $raffle_try = BotRaffleController::getRaffleTry($user_id);
        $check_win = BotRaffleController::checkUserWin($user_id);
        if ($check_win == 0 && $raffle_try > 0) {

            $inline_keyboard = new InlineKeyboard([]);

            $raffle_id = BotRaffleController::newGame($user_id);
            if ($raffle_id !== null && $raffle_id > 0) {

                $inline_keyboard = BotRaffleButtonsController::get_buttons_start_game($user_id, $raffle_id);

                $raffle_try_today = BotRaffleController::getRaffleTryToday($user_id);

                $chances = BotRaffleController::getTextChances($user_id, $raffle_try);
                $chances_today = BotRaffleController::getTextChances($user_id, $raffle_try_today);

                $text = BotTextsController::getText($user_id, $command, 'message_raffle_try');
                $text = str_replace("___RAFFLE_TRY___", $raffle_try, $text);
                $text = str_replace("___RAFFLE_TRY_TODAY___", $raffle_try_today, $text);
                $text = str_replace("___RAFFLE_TRY_CHANCES___", $chances, $text);
                $text = str_replace("___RAFFLE_TRY_CHANCES_TODAY___", $chances_today, $text);

                $data_text = ['chat_id' => $user_id];
                $data_text['text'] = $text;
                $data_text['parse_mode'] = 'html';
                $send_text = Request::sendMessage($data_text);

//                $img = 'p0.png';
                $n = 0;
                $img = BotRaffleController::get_image($n);
                // Вытягиваем из БД текст для сообщения
                $data_text = ['chat_id' => $user_id];
                $data_text['caption'] = BotTextsController::getText($user_id, $command, $name);
//                $data_text['photo'] = env('PHP_TELEGRAM_BOT_URL').'assets/img/raffle/'.$img.'?'.time();
                $data_text['photo'] = $img;
                $data_text['parse_mode'] = 'html';
                $data_text['reply_markup'] = $inline_keyboard;
                $send_text = Request::sendPhoto($data_text);

                if ($send_text->getResult() !== null) {
                    $message_id = $send_text->getResult()->getMessageId();
                    $update = BotRaffleController::updateRaffleMessageId($user_id, $raffle_id, $message_id);
                    if ($update !== null) BotRaffleController::updateRaffleTry($user_id,'minus');
                }

            }

        }
        elseif ($raffle_try == 0) {

            $text = BotTextsController::getText($user_id, $command, 'message_no_win_no_try');
            $inline_keyboard = BotRaffleButtonsController::get_no_win_no_try_buttons($user_id);

            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = $text;
            $data_text['parse_mode'] = 'html';
            $data_text['reply_markup'] = $inline_keyboard;
            $send_text = Request::sendMessage($data_text);

        }
        else {

            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = BotTextsController::getText($user_id, $command, 'message_no_game');
            $data_text['parse_mode'] = 'html';
            $data_text['reply_markup'] = BotRaffleButtonsController::get_no_game_buttons($user_id);
            $send_text = Request::sendMessage($data_text);

        }

    }

    public static function update_game($user_id, $raffle_id, $p, $message_id)
    {

        $command = 'Raffle';
        $name = 'update_game';

        $selectBlock = BotRaffleController::selectBlock($user_id, $raffle_id, $p);
        if (is_array($selectBlock)) {

            $guessed = $selectBlock[0];
            $raffle_try = $selectBlock[1];
            $raffle_try_left = 8 - $raffle_try;
            $block = $selectBlock[2];
            $win = $guessed == 6 ? 1 : 0;

            $attempts = BotRaffleController::getTextAtempts($user_id, $raffle_try_left);
            $text = BotTextsController::getText($user_id, $command, $name);

            $text = str_replace("___RAFFLE_TRY___", $raffle_try_left, $text);
            $text = str_replace("___RAFFLE_TRY_ATTEMPTS___", $attempts, $text);

//            $img = 'p'.$guessed.'.png';
            $img = BotRaffleController::get_image($guessed);

            $data_media = [
                'type' => 'photo',
                'media' => $img,
                'caption' => $text,
                'parse_mode' => 'html'
            ];

            $data_photo = ['chat_id' => $user_id];
//            $data_photo['photo'] = $img;
            $data_photo['message_id'] = $message_id;
//            $data_photo['media'] = json_encode($data_media);
            $data_photo['media'] = $data_media;
            $data_photo['reply_markup'] = BotRaffleButtonsController::get_buttons_update_game($user_id, $raffle_id);
            $media = Request::editMessageMedia($data_photo);

            $chances = BotRaffleController::getRaffleTry($user_id);

            if ($win == 1) {

                BotRaffleController::updateRaffleWin($user_id, $raffle_id);
                $data_text = ['chat_id' => $user_id];
                $data_text['text'] = BotTextsController::getText($user_id, $command, 'message_win');
                $data_text['parse_mode'] = 'html';
                $data_text['reply_markup'] = BotRaffleButtonsController::get_win_buttons($user_id);
                $send_text = Request::sendMessage($data_text);

            }
            elseif ($raffle_try == 8) {

                if ($win == 0) {

                    $text = $chances >= 1 ? BotTextsController::getText($user_id, $command, 'message_raffle_again') : BotTextsController::getText($user_id, $command, 'message_no_win_no_try');
                    $inline_keyboard = $chances >= 1 ? BotRaffleButtonsController::get_no_win_buttons($user_id) : BotRaffleButtonsController::get_no_win_no_try_buttons($user_id);

                    $data_text = ['chat_id' => $user_id];
                    $data_text['text'] = $text;
                    $data_text['parse_mode'] = 'html';
                    $data_text['reply_markup'] = $inline_keyboard;
                    $send_text = Request::sendMessage($data_text);

                }

            }

            return $media;

        }
        else return null;

    }

    public static function add_action_pizza($user_id, $product_id, $variant_id, $message_id)
    {

        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        $check = BotCartNew::where('user_id', $user_id)->where('product_action', 1)->count();
        if ($check == 0) {

            $command = 'Raffle';
            $name = 'add_pizza';

            // Записываемся в историю
            BotUserHistoryController::insertToHistory($user_id, 'open', $command . ' (' . $name . ')');

            BotRaffleController::addActionPizzaToCart($user_id, $product_id, $variant_id);

            $inline_keyboard = new InlineKeyboard([]);
            list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Start', 'begin');
            $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'web_app' => ['url' => route('show_new_menu', ['user_id' => $user_id])]]));

            // Вытягиваем из БД текст для сообщения
            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = BotTextsController::getText($user_id, $command, 'message_added_pizza');
            $data_text['parse_mode'] = 'html';
            $data_text['reply_markup'] = $inline_keyboard;
            $send_text = Request::sendMessage($data_text);

            $data_edit = ['chat_id' => $user_id];
            $data_edit['message_id'] = $message_id;
            $data_edit['reply_markup'] = new InlineKeyboard([]);
            $update_message = Request::editMessageReplyMarkup($data_edit);

//            Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);
//            sleep(1);

//            MenuCommandController::execute($user_id, 'send', null);

        }

    }

    public static function add_guest($user_id)
    {

        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        $command = 'Raffle';
        $name = 'add_guest';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command.' ('.$name.')');

        $raffle_try = BotRaffleController::getAllRaffleTry($user_id);
        $chances = BotRaffleController::getTextChances($user_id, $raffle_try);

        $text = BotTextsController::getText($user_id, $command, $name);
        $text = str_replace("___RAFFLE_TRY___", $raffle_try, $text);
        $text = str_replace("___RAFFLE_TRY_CHANCES___", $chances, $text);

        // Вытягиваем из БД текст для сообщения
        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = $text;
        $data_text['reply_markup'] = BotButtonsInlineController::getButtonsInline($user_id, $command, $name);
        $data_text['parse_mode'] = 'html';
        $send_text = Request::sendMessage($data_text);

    }

    public static function start_test_game($user_id)
    {

        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        $command = 'Raffle';
        $name = 'start_test_game';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command.' ('.$name.')');

        $inline_keyboard = new InlineKeyboard([]);

        $raffle_id = BotRaffleController::newTestGame($user_id);
        if ($raffle_id !== null && $raffle_id > 0) {

            $inline_keyboard = BotRaffleButtonsController::get_buttons_start_test_game($user_id, $raffle_id);

            $n = 0;
            $img = BotRaffleController::get_image($n);
//            $img = 'p0.png';
            // Вытягиваем из БД текст для сообщения
            $data_text = ['chat_id' => $user_id];
            $data_text['caption'] = BotTextsController::getText($user_id, $command, $name);
//            $data_text['photo'] = env('PHP_TELEGRAM_BOT_URL').'assets/img/raffle/'.$img.'?'.time();
            $data_text['photo'] = $img;
            $data_text['parse_mode'] = 'html';
            $data_text['reply_markup'] = $inline_keyboard;
            $send_text = Request::sendPhoto($data_text);

            if ($send_text->getResult() !== null) {
                $message_id = $send_text->getResult()->getMessageId();
                BotRaffleController::updateTestRaffleMessageId($user_id, $raffle_id, $message_id);
            }

        }

    }

    public static function update_test_game($user_id, $raffle_id, $p, $message_id)
    {

        $command = 'Raffle';
        $name = 'update_game';

        $selectBlock = BotRaffleController::selectTestBlock($user_id, $raffle_id, $p);
        if (is_array($selectBlock)) {

            $guessed = $selectBlock[0];
            $raffle_try = $selectBlock[1];
            $raffle_try_left = 8 - $raffle_try;
            $block = $selectBlock[2];
            $win = $guessed == 6 ? 1 : 0;

            $attempts = BotRaffleController::getTextAtempts($user_id, $raffle_try_left);
            $text = BotTextsController::getText($user_id, $command, $name);

            $text = str_replace("___RAFFLE_TRY___", $raffle_try_left, $text);
            $text = str_replace("___RAFFLE_TRY_ATTEMPTS___", $attempts, $text);

//            $img = 'p'.$guessed.'.png';
            $img = BotRaffleController::get_image($guessed);
            $data_media = [
                'type' => 'photo',
                'media' => $img,
                'caption' => $text,
                'parse_mode' => 'html'
            ];

            $data_photo = ['chat_id' => $user_id];
//            $data_photo['photo'] = env('PHP_TELEGRAM_BOT_URL').'assets/img/raffle/'.$img;
            $data_photo['photo'] = $img;
            $data_photo['reply_markup'] = BotRaffleButtonsController::get_buttons_update_test_game($user_id, $raffle_id);
            $data_photo['message_id'] = $message_id;
//            $data_photo['media'] = json_encode($data_media);
            $data_photo['media'] = $data_media;

            $media = Request::editMessageMedia($data_photo);

            if ($win == 1) {

                $data_text = ['chat_id' => $user_id];
                $data_text['text'] = BotTextsController::getText($user_id, $command, 'message_test_win');
                $data_text['parse_mode'] = 'html';
                $data_text['reply_markup'] = BotRaffleButtonsController::get_test_win_buttons($user_id);
                $send_text = Request::sendMessage($data_text);

            }
            elseif ($raffle_try == 8) {

                if ($win == 0) {

                    $text = BotTextsController::getText($user_id, $command, 'message_raffle_again');
                    $inline_keyboard = BotRaffleButtonsController::get_no_test_win_buttons($user_id);

                    $data_text = ['chat_id' => $user_id];
                    $data_text['text'] = $text;
                    $data_text['parse_mode'] = 'html';
                    $data_text['reply_markup'] = $inline_keyboard;
                    $send_text = Request::sendMessage($data_text);

                }

            }

            return $media;

        }
        else return null;

    }

//    public static function show_rules ($user_id)
//    {
//
//        $command = 'Raffle';
//        $name = 'show_rules';
//
//        // Записываемся в историю
//        BotUserHistoryController::insertToHistory($user_id, 'open', $command);
//
////        $inline_keyboard = BotButtonsInlineController::getButtonsInline($user_id, $command, $name);
//
//        // Вытягиваем из БД текст для сообщения
//        $data_text = ['chat_id' => $user_id];
//        $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
//        $data_text['parse_mode'] = 'html';
////        $data_text['reply_markup'] = $inline_keyboard;
//        $send_text = Request::sendMessage($data_text);
//
//    }

}
