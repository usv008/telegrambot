<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\TelegramBotCherry\TelegramBotCherryController;
use App\Models\BotCart;
use App\Models\BotCartNew;
use App\Models\BotMenu;
use App\Models\BotOrder;
use App\Models\BotRaffle;
use App\Models\BotRaffleCherryTakeaway;
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

class RaffleCherryCommandController extends Controller
{

    private static string $command = 'Raffle_cherry';

    public static function execute($user_id, $act, $message_id)
    {

        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        $name = 'show_menu';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', self::$command);

        BotRaffleCherryController::updateCherryTryForGameToday($user_id);

        $raffle_try = BotRaffleCherryController::getRaffleTry($user_id);
        $chances = BotRaffleCherryController::getTextChances($user_id, $raffle_try);

        $text = BotTextsController::getText($user_id, self::$command, $name);
        $text = str_replace("___RAFFLE_TRY___", $raffle_try, $text);
        $text = str_replace("___RAFFLE_TRY_CHANCES___", $chances, $text);

        $inline_keyboard = BotRaffleCherryButtonsController::get_menu_buttons($user_id, $raffle_try);

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
            $data_sticker['sticker'] = BotStickerController::getSticker($user_id, self::$command);
            $data_sticker['reply_markup'] = Keyboard::remove(['selective' => true]);
            $send_sticker = Request::sendSticker($data_sticker);

            $send_text = Request::sendMessage($data_text);

        }

    }

    public static function show_rules($user_id, $act, $message_id)
    {

        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        $name = 'show_rules';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', self::$command.' ('.$name.')');

        // Вытягиваем из БД текст для сообщения
        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = BotTextsController::getText($user_id, self::$command, $name);
        $data_text['parse_mode'] = 'html';
        if ($act == 'edit') {
            $data_text['reply_markup'] = BotButtonsInlineController::getButtonsInline($user_id, self::$command, $name);
            $data_text['message_id'] = $message_id;
            $send_text = Request::editMessageText($data_text);
        }
        elseif ($act == 'send') {
            $data_text['reply_markup'] = BotRaffleCherryButtonsController::get_buttons_rules_send($user_id);
            $send_text = Request::sendMessage($data_text);
        }

    }

    public static function start_game($user_id)
    {

        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        $name = 'start_game';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', self::$command.' ('.$name.')');

        $raffle_try = BotRaffleCherryController::getRaffleTry($user_id);
        $check_win = BotRaffleCherryController::checkUserWin($user_id);
        if ($check_win == 0 && $raffle_try > 0) {

            $raffle_id = BotRaffleCherryController::newGame($user_id);
            if ($raffle_id !== null && $raffle_id > 0) {

                $inline_keyboard = BotRaffleCherryButtonsController::get_buttons_start_game($user_id, $raffle_id);

                $raffle_try_today = BotRaffleCherryController::getRaffleTryToday($user_id);

                $chances = BotRaffleCherryController::getTextChances($user_id, $raffle_try);
                $chances_today = BotRaffleCherryController::getTextChances($user_id, $raffle_try_today);

                $text = BotTextsController::getText($user_id, self::$command, 'message_raffle_try');
                $text = str_replace("___RAFFLE_TRY___", $raffle_try, $text);
                $text = str_replace("___RAFFLE_TRY_TODAY___", $raffle_try_today, $text);
                $text = str_replace("___RAFFLE_TRY_CHANCES___", $chances, $text);
                $text = str_replace("___RAFFLE_TRY_CHANCES_TODAY___", $chances_today, $text);

                $data_text = ['chat_id' => $user_id];
                $data_text['text'] = $text;
                $data_text['parse_mode'] = 'html';
                $send_text = Request::sendMessage($data_text);

                $n = 0;
                $img = BotRaffleCherryController::get_image($n);
                // Вытягиваем из БД текст для сообщения
                $data_text = ['chat_id' => $user_id];
                $data_text['caption'] = BotTextsController::getText($user_id, self::$command, $name);
                $data_text['photo'] = $img;
                $data_text['parse_mode'] = 'html';
                $data_text['reply_markup'] = $inline_keyboard;
                $send_text = Request::sendPhoto($data_text);

                if ($send_text->getResult() !== null) {
                    $message_id = $send_text->getResult()->getMessageId();
                    $update = BotRaffleCherryController::updateRaffleMessageId($user_id, $raffle_id, $message_id);
                    if ($update !== null) BotRaffleCherryController::updateRaffleTry($user_id,'minus');
                }

            }

        }
        elseif ($raffle_try == 0) {

            $text = BotTextsController::getText($user_id, self::$command, 'message_no_win_no_try');
            $inline_keyboard = BotRaffleCherryButtonsController::get_no_win_no_try_buttons($user_id);

            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = $text;
            $data_text['parse_mode'] = 'html';
            $data_text['reply_markup'] = $inline_keyboard;
            $send_text = Request::sendMessage($data_text);

        }
        else {

            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = BotTextsController::getText($user_id, self::$command, 'message_no_game');
            $data_text['parse_mode'] = 'html';
            $data_text['reply_markup'] = BotRaffleCherryButtonsController::get_no_game_buttons($user_id);
            $send_text = Request::sendMessage($data_text);

        }

    }

    public static function update_game($user_id, $raffle_id, $p, $message_id)
    {

        $name = 'update_game';

        $selectBlock = BotRaffleCherryController::selectBlock($user_id, $raffle_id, $p);
        if (is_array($selectBlock)) {

            $guessed = $selectBlock[0];
            $raffle_try = $selectBlock[1];
            $raffle_try_left = 8 - $raffle_try;
            $block = $selectBlock[2];
            $win = $guessed == 6 ? 1 : 0;

            $attempts = BotRaffleCherryController::getTextAtempts($user_id, $raffle_try_left);
            $text = BotTextsController::getText($user_id, self::$command, $name);

            $text = str_replace("___RAFFLE_TRY___", $raffle_try_left, $text);
            $text = str_replace("___RAFFLE_TRY_ATTEMPTS___", $attempts, $text);

            $img = BotRaffleCherryController::get_image($guessed);

            $data_media = [
                'type' => 'photo',
                'media' => $img,
                'caption' => $text,
                'parse_mode' => 'html'
            ];

            $data_photo = ['chat_id' => $user_id];
            $data_photo['message_id'] = $message_id;
            $data_photo['media'] = $data_media;
            $data_photo['reply_markup'] = BotRaffleCherryButtonsController::get_buttons_update_game($user_id, $raffle_id);
            $media = Request::editMessageMedia($data_photo);

            $chances = BotRaffleCherryController::getRaffleTry($user_id);

            if ($win == 1) {
                $update = BotRaffleCherryController::updateRaffleWin($user_id, $raffle_id);
                Log::warning('UPDATE WINNER');
                if ($update) {
//                    $data_text = ['chat_id' => $user_id];
//                    $data_text['text'] = BotTextsController::getText($user_id, self::$command, 'message_win');
//                    $data_text['parse_mode'] = 'html';
//                    $data_text['reply_markup'] = BotRaffleCherryButtonsController::get_win_buttons($user_id);
//                    $send_text = Request::sendMessage($data_text);
                    $ask_name = self::AskWinnerName($user_id, $message_id);
                }
            }
            elseif ($raffle_try == 8) {

                if ($win == 0) {

                    $text = $chances >= 1 ? BotTextsController::getText($user_id, self::$command, 'message_raffle_again') : BotTextsController::getText($user_id, self::$command, 'message_no_win_no_try');
                    $inline_keyboard = $chances >= 1 ? BotRaffleCherryButtonsController::get_no_win_buttons($user_id) : BotRaffleCherryButtonsController::get_no_win_no_try_buttons($user_id);

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

    public static function AskWinnerName($user_id, $message_id)
    {
        if (BotRaffleCherryController::checkUserRaffleWin($user_id) > 0) {
            Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);
            Log::warning('ASK WINNER NAME');
            if (BotRaffleCherryTakeaway::countUnReceivedByUserId($user_id) == 0) {
                $command = 'Raffle_cherry';
                $takeaway = new BotRaffleCherryTakeaway;
                $takeaway->user_id = $user_id;
                $takeaway->save();
                Log::warning('SAVED '.$takeaway->id);

                $text = BotTextsController::getText($user_id, $command, 'ask_winner_name');

                $data_text = ['chat_id' => $user_id];
                $data_text['text'] = $text;
                $data_text['parse_mode'] = 'html';

                $keyboard_bottom = new Keyboard([]);

                $buttons = BotButtonsController::getButtons($user_id, 'System', ['cancel']);
                foreach ($buttons as $button) {
                    $keyboard_bottom->addRow($button);
                }

                $keyboard_b = $keyboard_bottom
                    ->setResizeKeyboard(true)
                    ->setOneTimeKeyboard(true)
                    ->setSelective(false);
                $data_text['reply_markup'] = $keyboard_b;

                $send_text = Request::sendMessage($data_text);
                BotUsersNavController::updateValue($user_id, 'change_key', 'raffle_cherry_enter_name');
                BotUsersNavController::updateValue($user_id, 'raffle_cherry_takeaway_id', $takeaway->id);
            }
            return null;
        }
        return null;
    }

//    public static function AskWinnerName($user_id, $message_id)
//    {
//        if (BotRaffleCherryController::checkUserRaffleWin($user_id) > 0)
//        {
//            Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);
//            if (BotRaffleCherryTakeaway::getCountUnReceivedByUserId($user_id) == 0) {
//                $command = 'Raffle_cherry';
//                $takeaway = new BotRaffleCherryTakeaway;
//                $takeaway->user_id = $user_id;
//                $takeaway->save();
//
//                $text = BotTextsController::getText($user_id, $command, 'ask_winner_name');
//
//                $data_text = ['chat_id' => $user_id];
//                $data_text['text'] = $text;
//                $data_text['parse_mode'] = 'html';
//
//                $keyboard_bottom = new Keyboard([]);
//
//                $buttons = BotButtonsController::getButtons($user_id, 'System', ['cancel']);
//                foreach ($buttons as $button) {
//                    $keyboard_bottom->addRow($button);
//                }
//
//                $keyboard_b = $keyboard_bottom
//                    ->setResizeKeyboard(true)
//                    ->setOneTimeKeyboard(true)
//                    ->setSelective(false);
//                $data_text['reply_markup'] = $keyboard_b;
//
//                $send_text = Request::sendMessage($data_text);
//                BotUsersNavController::updateValue($user_id, 'change_key', 'raffle_cherry_enter_name');
//                BotUsersNavController::updateValue($user_id, 'raffle_cherry_takeaway_id', $takeaway->id);
//            }
//            return null;
//        }
//        return null;
//    }
//
    public static function askWinnerPhone($user_id, $name) {

        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);
        $takeaway_id = BotUsersNavController::getValue($user_id, 'raffle_cherry_takeaway_id');
        $update = BotRaffleCherryTakeaway::updateNameByUserIdAndId($user_id, $takeaway_id, $name);
        if ($update) {
            $command = 'Raffle_cherry';
            $text = BotTextsController::getText($user_id, $command, 'ask_winner_phone');
            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = $text;
            $data_text['parse_mode'] = 'html';

            $keyboard_bottom = new Keyboard([]);

            $buttons = BotButtonsController::getButtons($user_id, 'System', ['cancel']);
            foreach ($buttons as $button) {
                $keyboard_bottom->addRow($button);
            }

            $keyboard_b = $keyboard_bottom
                ->setResizeKeyboard(true)
                ->setOneTimeKeyboard(true)
                ->setSelective(false);
            $data_text['reply_markup'] = $keyboard_b;

            $send_text = Request::sendMessage($data_text);
            BotUsersNavController::updateValue($user_id, 'change_key', 'raffle_cherry_enter_phone');
        }
        return null;
    }

    public static function getTakeawayCherry($user_id, $phone)
    {
        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);
        $takeaway_id = BotUsersNavController::getValue($user_id, 'raffle_cherry_takeaway_id');
        $update = BotRaffleCherryTakeaway::updatePhoneByUserIdAndId($user_id, $takeaway_id, $phone);
        if ($update) {
            $command = 'Raffle_cherry';
            $user_win = BotRaffleCherryTakeaway::getUserByUserIdAndId($user_id, $takeaway_id);
            $telegram_drink_cherry = new TelegramBotCherryController;
//            $user_id_admin = env('USER_ID_OZZI');
            $user_id_admins = [
                env('USER_ID_OZZI'),
                env('USER_ID_SERGEY'),
                env('USER_ID_YAN'),
                env('USER_ID_DRINKCHERRY'),
            ];
            $text = $user_win->name.PHP_EOL.$user_win->phone.PHP_EOL."виграв чекушку П'яної вишні";
            $inline_keyboard = [
                [
                    ['text' => 'Віддати йому', 'callback_data' => 'cherry_takeaway___'.$user_id.'___'.$takeaway_id.'___'.$phone],
                ]
            ];
//            $inline_keyboard = json_encode(['inline_keyboard' => [$keyboard]]);
            $message_ids = [];
            foreach ($user_id_admins as $user_id_admin) {
                $send_message = $telegram_drink_cherry->sendTelegramMessage($user_id_admin, $text, $inline_keyboard);
                $result = json_decode($send_message);
                if ($result->ok) {
                    $message_ids[$user_id_admin] = $result->result->message_id;
                }

            }

            BotUsersNavController::updateValue($user_id, 'change_key', null);
            BotUsersNavController::updateValue($user_id, 'raffle_cherry_takeaway_id', null);
            BotRaffleCherryTakeaway::updateWaitingForReceiptAndMessageIdsByUserIdAndId($user_id, $takeaway_id, $message_ids);

            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = '...';
            $data_text['reply_markup'] = Keyboard::remove(['selective' => true]);
            $send = Request::sendMessage($data_text);

            $inline_keyboard = BotRaffleCherryButtonsController::get_win_buttons($user_id);
            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = BotTextsController::getText($user_id, $command, 'send_addresses_takeaway');
            $data_text['reply_markup'] = $inline_keyboard;
            $send = Request::sendMessage($data_text);

        }
        return null;
    }

    public static function add_guest($user_id)
    {

        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        $name = 'add_guest';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', self::$command.' ('.$name.')');

        $raffle_try = BotRaffleController::getAllRaffleTry($user_id);
        $chances = BotRaffleController::getTextChances($user_id, $raffle_try);

        $text = BotTextsController::getText($user_id, self::$command, $name);
        $text = str_replace("___RAFFLE_TRY___", $raffle_try, $text);
        $text = str_replace("___RAFFLE_TRY_CHANCES___", $chances, $text);

        // Вытягиваем из БД текст для сообщения
        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = $text;
        $data_text['reply_markup'] = BotButtonsInlineController::getButtonsInline($user_id, self::$command, $name);
        $data_text['parse_mode'] = 'html';
        $send_text = Request::sendMessage($data_text);

    }

    public static function start_test_game($user_id)
    {

        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        $name = 'start_test_game';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', self::$command.' ('.$name.')');

        $raffle_id = BotRaffleCherryController::newTestGame($user_id);
        if ($raffle_id) {

            $inline_keyboard = BotRaffleCherryButtonsController::get_buttons_start_test_game($user_id, $raffle_id);

            $n = 0;
            $img = BotRaffleCherryController::get_image($n);
            // Вытягиваем из БД текст для сообщения
            $data_text = ['chat_id' => $user_id];
            $data_text['caption'] = BotTextsController::getText($user_id, self::$command, $name);
            $data_text['photo'] = $img;
            $data_text['parse_mode'] = 'html';
            $data_text['reply_markup'] = $inline_keyboard;
            $send_text = Request::sendPhoto($data_text);

            if ($send_text->getResult() !== null) {
                $message_id = $send_text->getResult()->getMessageId();
                BotRaffleCherryController::updateTestRaffleMessageId($user_id, $raffle_id, $message_id);
            }

        }

    }

    public static function update_test_game($user_id, $raffle_id, $p, $message_id)
    {

        $name = 'update_game';

        $selectBlock = BotRaffleCherryController::selectTestBlock($user_id, $raffle_id, $p);
        if (is_array($selectBlock)) {

            $guessed = $selectBlock[0];
            $raffle_try = $selectBlock[1];
            $raffle_try_left = 8 - $raffle_try;
            $block = $selectBlock[2];
            $win = $guessed == 6 ? 1 : 0;

            $attempts = BotRaffleCherryController::getTextAtempts($user_id, $raffle_try_left);
            $text = BotTextsController::getText($user_id, self::$command, $name);

            $text = str_replace("___RAFFLE_TRY___", $raffle_try_left, $text);
            $text = str_replace("___RAFFLE_TRY_ATTEMPTS___", $attempts, $text);

//            $img = 'p'.$guessed.'.png';
            $img = BotRaffleCherryController::get_image($guessed);
            $data_media = [
                'type' => 'photo',
                'media' => $img,
                'caption' => $text,
                'parse_mode' => 'html'
            ];

            $data_photo = ['chat_id' => $user_id];
//            $data_photo['photo'] = env('PHP_TELEGRAM_BOT_URL').'assets/img/raffle/'.$img;
            $data_photo['photo'] = $img;
            $data_photo['reply_markup'] = BotRaffleCherryButtonsController::get_buttons_update_test_game($user_id, $raffle_id);
            $data_photo['message_id'] = $message_id;
//            $data_photo['media'] = json_encode($data_media);
            $data_photo['media'] = $data_media;

            $media = Request::editMessageMedia($data_photo);

            if ($win == 1) {

                $data_text = ['chat_id' => $user_id];
                $data_text['text'] = BotTextsController::getText($user_id, self::$command, 'message_test_win');
                $data_text['parse_mode'] = 'html';
                $data_text['reply_markup'] = BotRaffleCherryButtonsController::get_test_win_buttons($user_id);
                $send_text = Request::sendMessage($data_text);

            }
            elseif ($raffle_try == 8) {

                if ($win == 0) {

                    $text = BotTextsController::getText($user_id, self::$command, 'message_raffle_again');
                    $inline_keyboard = BotRaffleCherryButtonsController::get_no_test_win_buttons($user_id);

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

}
