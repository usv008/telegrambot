<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotGameSeaBattleFields;
use App\Models\BotGameSeaBattleGames;
use App\Models\BotGameSeaBattleIcons;
use App\Models\BotGameSeaBattleImages;
use App\Models\BotGameSeaBattleRates;
use App\Models\BotGameSeaBattleShots;
use App\Models\BotGameSeaBattleUsers;
use App\Models\BotUser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;

class BotGameSeaBattleController extends Controller
{

    public static $ships_need = 4;
    public static $bombs_need = 1;
    public static $cashback_bet = 5;
    public static $bot_user_id = 638802969;

    public static function showWarning($user_id)
    {
        $command = 'GameSeaBattle';
        $name = 'warning';

        $send_sticker = BotStickerController::sendSticker($user_id, 'Danger');

        $data_message = ['chat_id' => $user_id];
        $data_message['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_message['parse_mode'] = 'html';
        $data_message['reply_markup'] = BotButtonsInlineController::getButtonsInline($user_id, $command, $name);
        $result = Request::sendMessage($data_message);

//        $data_edit = ['chat_id' => $user_id];
//        $data_edit['text'] = BotTextsController::getText($user_id, $command, $name);
//        $data_edit['parse_mode'] = 'html';
//        $data_edit['reply_markup'] = BotButtonsInlineController::getButtonsInline($user_id, $command, $name);
//        $data_edit['message_id'] = $message_id;
////        Request::editMessageReplyMarkup($data_edit);
//        $result = Request::editMessageText($data_edit);

        return $result;
    }

    public static function openGame($user_id, $message_id = null)
    {
        $command = 'GameSeaBattle';

        $cashback_all = BotCashbackController::getUserCashbackAll($user_id);
        if ($cashback_all >= self::$cashback_bet) {
            $name = 'open_game';
        }
        else {
            $name = 'no_cashback';
        }

        $text = BotTextsController::getText($user_id, $command, $name);
        $text = str_replace("___CASHBACK___", $cashback_all, $text);
        $currency = BotTextsController::getText($user_id, 'System', 'currency');
        $text = str_replace("___CURRENCY___", $currency, $text);

        $data_message = ['chat_id' => $user_id];
        $data_message['text'] = $text;
        $data_message['parse_mode'] = 'html';
        $data_message['reply_markup'] = BotButtonsInlineController::getButtonsInline($user_id, $command, $name);

        if ($message_id == null) {
            $send_sticker = BotStickerController::sendSticker($user_id, 'Start');
            $result = Request::sendMessage($data_message);
        }
        else {
            $data_message['message_id'] = $message_id;
            $result = Request::editMessageText($data_message);
        }
        return $result;
    }

    public static function noCashbackBack($user_id, $message_id)
    {
//        $data_field_message = ['chat_id' => $user_id];
//        $data_field_message['message_id'] = $message_id;
//        $deleteFieldMessage = Request::deleteMessage($data_field_message);
        $result = BotGamesController::selectGame($user_id);
        return $result;
    }

    public static function showRules($user_id, $message_id)
    {
        $command = 'GameSeaBattle';
        $name = 'rules';

        $data_edit = ['chat_id' => $user_id];
        $data_edit['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_edit['parse_mode'] = 'html';
        $data_edit['reply_markup'] = BotButtonsInlineController::getButtonsInline($user_id, $command, $name);
        $data_edit['message_id'] = $message_id;
//        $result =  Request::editMessageReplyMarkup($data_edit);
        $result = Request::editMessageText($data_edit);

        return $result;
    }

    public static function playGame($user_id)
    {
        $command = 'GameSeaBattle';
        $name = 'play';

        $find_user = BotGameSeaBattleUsers::findUser($user_id);
//        $sea_battle_user = BotGameSeaBattleUsers::getUser($user_id);

        $deleteFields = self::deleteUserFields($user_id);

        $new_field = new BotGameSeaBattleFields;
        $new_field->user_id = $user_id;
        $new_field->updated_at = date("Y-m-d H:i:s");
        $new_field->save();

        $fields_id = isset($new_field->id) && $new_field->id !== null ? $new_field->id : null;

        if ($fields_id !== null) {
            $photo = BotGameSeaBattleImages::getFileIdByN(4);
            $text = BotTextsController::getText($user_id, $command, $name);
            $currency = BotTextsController::getText($user_id, 'System', 'currency');
            $text = str_replace("___CURRENCY___", $currency, $text);

            $data_photo = ['chat_id' => $user_id];
            $data_photo['photo'] = $photo;
            $data_photo['caption'] = $text;
            $data_photo['parse_mode'] = 'html';
            $data_photo['reply_markup'] = BotButtonsInlineController::getButtonsInlineForGameSeaBattle($user_id, $fields_id);
            $result = Request::sendPhoto($data_photo);
            if ($result->getResult() !== null) {
                $message_id = $result->getResult()->getMessageId();
                $update_message_id = BotGameSeaBattleFields::updateFieldMessageId($fields_id, $message_id);
            }
            return $result;
        }
        return null;
    }

    public static function updateField($user_id, $message_id, $field_id, $f_number)
    {
        $command = 'GameSeaBattle';
        $name = 'play';

        $field_icons = BotGameSeaBattleFields::countIconsInFieldAndGetField($field_id);
        $count_icons = $field_icons['icons'];
        $count_ships = $field_icons['ships'];
        $count_bombs = $field_icons['bombs'];

        $value_ins = null;
        if ($count_ships < self::$ships_need && $count_bombs == 0) {
            $count_ships++;
            if ($count_ships < self::$ships_need) {
                $text = BotTextsController::getText($user_id, $command, 'play2');
            }
            else {
                $text = BotTextsController::getText($user_id, $command, 'play3');
            }
            $text = str_replace("___SHIPS___", $count_ships, $text);
            $value_ins = BotGameSeaBattleIcons::$ship_id;
        }
        elseif ($count_ships == self::$ships_need && $count_bombs == 0) {
            $text = BotTextsController::getText($user_id, $command, 'play4');
            $count_bombs++;
            $text = str_replace("___SHIPS___", $count_ships, $text);
            $text = str_replace("___BOMBS___", $count_bombs, $text);
            $value_ins = BotGameSeaBattleIcons::$bomb_id;
        }
        else {
            $text = BotTextsController::getText($user_id, $command, 'play4');
            $text = str_replace("___SHIPS___", $count_ships, $text);
            $text = str_replace("___BOMBS___", $count_bombs, $text);
        }

        if ($value_ins !== null) {
            $field = BotGameSeaBattleFields::getField($field_id);
            if ($field['f'.$f_number] == null && $field->enabled == 0){
                $update_field = BotGameSeaBattleFields::updateField($field_id, $f_number, $value_ins);
            }

            $photo = BotGameSeaBattleImages::getFileIdByN(4);

            $currency = BotTextsController::getText($user_id, 'System', 'currency');
            $text = str_replace("___CURRENCY___", $currency, $text);

            $data_edit = ['chat_id' => $user_id];
            $data_edit['photo'] = $photo;
            $data_edit['caption'] = $text;
            $data_edit['parse_mode'] = 'html';
            $data_edit['reply_markup'] = BotButtonsInlineController::getButtonsInlineForGameSeaBattle($user_id, $field_id);
            $data_edit['message_id'] = $message_id;
            $result = Request::editMessageCaption($data_edit);
            return $result;
        }
        return null;
    }

    public static function clearField($user_id, $message_id, $field_id)
    {
        $command = 'GameSeaBattle';
        $name = 'play';

        $clear_field = BotGameSeaBattleFields::clearField($field_id);

        $photo = BotGameSeaBattleImages::getFileIdByN(4);

        $text = BotTextsController::getText($user_id, $command, $name);
        $currency = BotTextsController::getText($user_id, 'System', 'currency');
        $text = str_replace("___CURRENCY___", $currency, $text);

        $data_edit = ['chat_id' => $user_id];
        $data_edit['photo'] = $photo;
        $data_edit['caption'] = $text;
        $data_edit['parse_mode'] = 'html';
        $data_edit['reply_markup'] = BotButtonsInlineController::getButtonsInlineForGameSeaBattle($user_id, $field_id);
        $data_edit['message_id'] = $message_id;
        $result = Request::editMessageCaption($data_edit);
        return $result;
    }

    public static function searchForOpponent($user_id, $message_id, $field_id)
    {
        $command = 'GameSeaBattle';
        $name = 'play';

        $enableField = BotGameSeaBattleFields::enableField($field_id);
//        $minus_cashback = BotCashbackController::cashbackMinusForSeaBattle($user_id);
        $user_play = BotGameSeaBattleUsers::setUserPlayWaiting($user_id);

        $photo = BotGameSeaBattleImages::getFileIdByN(4);

        $text = BotTextsController::getText($user_id, $command, $name);
        $currency = BotTextsController::getText($user_id, 'System', 'currency');
        $text = str_replace("___CURRENCY___", $currency, $text);

        $data_edit = ['chat_id' => $user_id];
        $data_edit['photo'] = $photo;
        $data_edit['caption'] = $text;
        $data_edit['parse_mode'] = 'html';
        $data_edit['reply_markup'] = BotButtonsInlineController::getButtonsInlineForGameSeaBattle($user_id, $field_id, 1);
        $data_edit['message_id'] = $message_id;
        $result = Request::editMessageCaption($data_edit);
        Log::debug("SeaBattle result", ['result' => $result]);

        $waiting_users = BotGameSeaBattleUsers::getWaitingUsers($user_id);
        Log::debug("SeaBattle waiting users", ['waiting_users' => $waiting_users]);
        if ($waiting_users->count() > 0) {
            $opponent_user_id = $waiting_users->first()->user_id;
            $opponent_field = BotGameSeaBattleFields::getUserFieldForGame($opponent_user_id);
            Log::debug("SeaBattle opponent field", ['opponent_field' => $opponent_field]);
            if (!$opponent_field)
                return null;
            $search_message_id = $opponent_field->search_message_id;
            return self::playGameWithOpponent($user_id, $search_message_id, 0, $opponent_user_id);
        }

        $name = 'search_for_opponent';
        $data_message = ['chat_id' => $user_id];
        $data_message['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_message['parse_mode'] = 'html';
        $data_message['reply_markup'] = BotButtonsInlineController::getButtonsInline($user_id, $command, $name);
        $result = Request::sendMessage($data_message);
        if ($result->getResult() !== null) {
            $search_message_id = $result->getResult()->getMessageId();
            $updateSearchMessageId = BotGameSeaBattleFields::updateFieldSearchMessageId($field_id, $search_message_id);
        }
        return $result;
    }

    public static function cancelSearchAsk($user_id, $message_id)
    {
        $command = 'GameSeaBattle';
        $name = 'cancel_search';

        $data_edit = ['chat_id' => $user_id];
        $data_edit['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_edit['parse_mode'] = 'html';
        $data_edit['reply_markup'] = BotButtonsInlineController::getButtonsInline($user_id, $command, $name);
        $data_edit['message_id'] = $message_id;
        $result = Request::editMessageText($data_edit);
        return $result;
    }

    public static function cancelSearchNo($user_id, $message_id)
    {
        $command = 'GameSeaBattle';
        $name = 'search_for_opponent';

        $data_edit = ['chat_id' => $user_id];
        $data_edit['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_edit['parse_mode'] = 'html';
        $data_edit['reply_markup'] = BotButtonsInlineController::getButtonsInline($user_id, $command, $name);
        $data_edit['message_id'] = $message_id;
        $result = Request::editMessageText($data_edit);
        return $result;
    }

    public static function cancelSearchYes($user_id, $message_id)
    {
        $data_message = ['chat_id' => $user_id];
        $data_message['message_id'] = $message_id;
        $deleteMessage = Request::deleteMessage($data_message);
        $deleteFields = self::deleteUserFields($user_id);
//        $update_cashback = BotCashbackController::cashbackPlusForSeaBattle($user_id);
        $user_play = BotGameSeaBattleUsers::setUserNoPlay($user_id);
        return BotGamesController::selectGame($user_id);
    }

    public static function deleteUserFields($user_id)
    {
        $fields = BotGameSeaBattleFields::getUserNotFinishedFields($user_id);
        foreach ($fields as $field) {
            if ($field->message_id !== null && $field->message_id > 0) {
                $data_field_message = ['chat_id' => $user_id];
                $data_field_message['message_id'] = $field->message_id;
                $deleteFieldMessage = Request::deleteMessage($data_field_message);
            }
            $deleteField = BotGameSeaBattleFields::deleteUserField($user_id, $field->id);
        }
        return true;
    }

    public static function getNameByUserId($user_id)
    {
        return BotUser::getUser($user_id)['first_name'];
    }

    public static function playGameWithOpponent($user_id, $message_id, $playWithBot, $opponent_user_id = null)
    {
        $command = 'GameSeaBattle';
        $name = 'play_with_opponent';

        $photo = BotGameSeaBattleImages::getFileIdByN(4);
        $data_message = [];


        $data_photo = [];
        $data_photo['photo'] = $photo;
        $data_photo['parse_mode'] = 'html';

        $game_id = null;
        if ($playWithBot == 1) {
            $data_message['chat_id'] = $user_id;
            $text = BotTextsController::getText($user_id, $command, $name);
            $opponent = BotTextsController::getText($user_id, $command, 'play_with_opponent_bot');
            $text = str_replace("___OPPONENT___", $opponent, $text);
            $generate = self::generateFieldsForGameBot($user_id);
            if ($generate) $game_id = $generate['game_id'];
        }
        else {
            $text = BotTextsController::getText($opponent_user_id, $command, $name);
            $data_message['chat_id'] = $opponent_user_id;
            $opponent = self::getNameByUserId($user_id);
            $text = str_replace("___OPPONENT___", $opponent, $text);
            $generate = self::generateFieldsForGame($user_id, $opponent_user_id);
            if ($generate) $game_id = $generate['game_id'];

            $data_photo['chat_id'] = $opponent_user_id;
            $data_photo['caption'] = $text;
            $data_photo['reply_markup'] = BotButtonsInlineController::getButtonsInlineForGameSeaBattleStart($opponent_user_id, $playWithBot);
            $result = Request::sendPhoto($data_photo);
            if ($result->getResult() !== null) {
                $start_message_id = $result->getResult()->getMessageId();
                $update_message_id = BotGameSeaBattleGames::updateStartMessageId($game_id, 1, $start_message_id);
            }

            $opponent = self::getNameByUserId($opponent_user_id);
            $text = BotTextsController::getText($user_id, $command, $name);
            $text = str_replace("___OPPONENT___", $opponent, $text);
        }

        $data_message['message_id'] = $message_id;
        $deleteMessage = Request::deleteMessage($data_message);

        $data_photo['chat_id'] = $user_id;
        $data_photo['caption'] = $text;
        $data_photo['reply_markup'] = BotButtonsInlineController::getButtonsInlineForGameSeaBattleStart($user_id, $playWithBot);
        $result = Request::sendPhoto($data_photo);
        if ($result->getResult() !== null) {
            $start_message_id = $result->getResult()->getMessageId();
            $update_message_id = BotGameSeaBattleGames::updateStartMessageId($game_id, 2, $start_message_id);
        }
        return $result;
    }

    public static function generateFieldsForGame($user_id, $opponent_user_id)
    {
        $user_play = BotGameSeaBattleUsers::setUserPlayWithUser($user_id);
        $opponent_user_play = BotGameSeaBattleUsers::setUserPlayWithUser($opponent_user_id);

        $opponent_user_field = BotGameSeaBattleFields::getUserFieldForGame($opponent_user_id);
        $user_field = BotGameSeaBattleFields::getUserFieldForGame($user_id);

        // Создаем новую игру
        $new_game = BotGameSeaBattleGames::createNewGame(0, $opponent_user_id, $user_id, self::$cashback_bet, $opponent_user_field->id, $user_field->id);

        // Снимаем кешбэк
        $minus_cashback_player = BotCashbackController::cashbackMinusForSeaBattle($user_id, $new_game->id);
        $minus_cashback_opponent = BotCashbackController::cashbackMinusForSeaBattle($opponent_user_id, $new_game->id);

        // Создаем поля для выстрелов игроков
        $new_field_shot_opponent = BotGameSeaBattleShots::createNewShot($new_game->id, $user_field->id, $opponent_user_id, 1);
        $new_field_shot_user = BotGameSeaBattleShots::createNewShot($new_game->id, $opponent_user_field->id, $user_id);

        return $new_game && $new_field_shot_opponent && $new_field_shot_user ? ['game_id' => $new_game->id] : null;
    }

    public static function generateFieldsForGameBot($user_id)
    {
        $user_play = BotGameSeaBattleUsers::setUserPlayWithBot($user_id);

        // Создаем поле игры бота
        $new_field = BotGameSeaBattleFields::createNewField(self::$bot_user_id, 1);

        // Создаем новую игру
        $new_game = BotGameSeaBattleGames::createNewGame(1, self::$bot_user_id, $user_id, self::$cashback_bet, $new_field->id, BotGameSeaBattleFields::getUserFieldForGame($user_id)->id);

        // Снимаем кешбэк
        $minus_cashback_player = BotCashbackController::cashbackMinusForSeaBattle($user_id, $new_game->id);

        // Создаем поля для выстрелов игроков
        $new_field_shot_bot = BotGameSeaBattleShots::createNewShot($new_game->id, BotGameSeaBattleFields::getUserFieldForGame($user_id)->id, self::$bot_user_id, 1);
        $new_field_shot_user = BotGameSeaBattleShots::createNewShot($new_game->id, $new_field->id, $user_id);

        return $new_field && $new_game && $new_field_shot_bot && $new_field_shot_user ? ['game_id' => $new_game->id] : null;
    }

    public static function playGameStart($user_id, $message_id, $playWithBot, $fn = 0)
    {
        $command = 'GameSeaBattle';
        $name = $fn == 0 ? 'play_with_opponent_start' : 'play_with_opponent_processing';
        $img = BotGameSeaBattleImages::getFileIdByN(0);

        $game = BotGameSeaBattleGames::getActiveGameByUserId($user_id);
        if (!$game || !isset($game->finished) || $game->finished == 1) return null;

        $player = 'player1';
        $player_opponent = 'player2';
        if ($game->player2_user_id == $user_id) {
            $player = 'player2';
            $player_opponent = 'player1';
        }
        $opponent_user = $player_opponent.'_user_id';
        $opponent_user_id = $game->$opponent_user;
        $player_user_field = $player.'_field_id';
        $player_opponent_field = $player_opponent.'_field_id';
        $player_shots = $player.'_shots';
        $player_count_ships = $player.'_count_ships';
        $player_count_bombs = $player.'_count_bombs';
        $field_user_id = $game->$player_user_field;
        $field_id = $game->$player_opponent_field;

        if ($playWithBot == 0 && $fn == 0) {
            $checkPlayerStart = BotGameSeaBattleGames::checkPlayerStart($game, $player);
            if ($checkPlayerStart == null) $update_player_start = BotGameSeaBattleGames::updatePlayerStart($game->id, $player);
            $checkOpponentStart = BotGameSeaBattleGames::checkPlayerStart($game, $player_opponent);
            if ($checkOpponentStart == null) {
                $playerUser = $player.'_user_id';
                $playerMessage = $player.'_start_message_id';
                $removeButton = self::removeInlineKeyboardForPhoto($game->$playerUser, $game->$playerMessage);
                $data_message = ['chat_id' => $user_id];
                $data_message['text'] = BotTextsController::getText($user_id, $command, 'game_wait_for_start');
                $data_message['parse_mode'] = 'html';
                $result = Request::sendMessage($data_message);
                return $result;
            }
            else {
                $startMessageId = $player_opponent.'_start_message_id';
                $shotsMessageId = $player_opponent.'_shots_message_id';

                if ($game->$shotsMessageId == null) {
                    $text = BotTextsController::getText($opponent_user_id, $command, $name);
                    $opponent = self::getNameByUserId($user_id);
                    $text = str_replace("___OPPONENT___", $opponent, $text);
                    $field_user = BotGameSeaBattleFields::getField($field_id);
                    $shot = BotGameSeaBattleShots::getShotByFieldId($field_user_id);

                    $data_message = ['chat_id' => $opponent_user_id];
                    $data_message['message_id'] = $game->$startMessageId;
                    $deleteMessage = Request::deleteMessage($data_message);

                    $data_photo = ['chat_id' => $opponent_user_id];
                    $data_photo['photo'] = $img;
                    $data_photo['parse_mode'] = 'html';
                    $data_photo['caption'] = $text;
                    $data_photo['reply_markup'] = BotButtonsInlineController::getButtonsInlineForGameSeaBattlePlay($opponent_user_id, $playWithBot, $game->id, $field_user->id, $shot->id, $fn);
                    $result = Request::sendPhoto($data_photo);
                    if ($result->getResult() !== null) {
                        $shots_message_id = $result->getResult()->getMessageId();
                        $update_message_id = BotGameSeaBattleGames::updateShotsMessageId($game->id, $player_opponent, $shots_message_id);
                    }
                }
            }
        }

        $field = BotGameSeaBattleFields::getField($field_id);
        $field_user = BotGameSeaBattleFields::getField($field_user_id);
        $shot = BotGameSeaBattleShots::getShotByFieldId($field_id);

        $text = BotTextsController::getText($user_id, $command, $name);
        if ($playWithBot == 1) {
            $opponent = BotTextsController::getText($user_id, $command, 'play_with_opponent_bot');
        }
        else {
            $opponent = self::getNameByUserId($opponent_user_id);
        }
        $text = str_replace("___OPPONENT___", $opponent, $text);

        if ($fn > 0) {
            $col_name = 'f'.$fn;
            if ($field->finished == 0)
                $shot->$col_name = 1;

            $count_ships = 0;
            for ($i = 1; $i <= 16; $i++) {
                $col_name = 'f'.$i;
                if ($shot->$col_name == 1 && $field->$col_name == BotGameSeaBattleIcons::$ship_id) $count_ships++;
            }

            $col_name = 'f'.$fn;
            if ($count_ships <= self::$ships_need) {
                $img = BotGameSeaBattleImages::getFileIdByN($count_ships);
                if ($field->finished == 0) {
                    $update_shot = BotGameSeaBattleShots::updateFired($shot->id, $fn);
                    if ($update_shot) {
                        $game->increment($player_shots);
                        $update_count_ships = BotGameSeaBattleGames::updateValue($game->id, $player_count_ships, $count_ships);
                    }
                    if ($field->$col_name == BotGameSeaBattleIcons::$bomb_id) {
                        $field->finished = 1;
                        $field->save();
                        $game->increment($player_count_bombs);
                        $data_bomb = ['chat_id' => $user_id];
                        $data_bomb['photo'] = asset('assets/img/games/sea_battle/boom.jpg');
                        $data_bomb['caption'] = BotTextsController::getText($user_id, $command, 'bomb');;
                        $data_bomb['parse_mode'] = 'html';
                        if ($playWithBot == 1) {
                            $botShotGeneration = self::botShotGeneration($user_id, $game->id, $player_opponent, $field_user);
                            $data_bomb['reply_markup'] = BotButtonsInlineController::getButtonsInline($user_id, $command, 'game_over');
                        }
                        $result = Request::sendPhoto($data_bomb);
                        if ($playWithBot == 0) {
                            if (BotGameSeaBattleGames::checkGameOver($game->id)) {
                                $data_message = [];
                                $data_message['text'] = BotTextsController::getText($user_id, $command, 'game_over');
                                $data_message['parse_mode'] = 'html';
                                $data_message['reply_markup'] = BotButtonsInlineController::getButtonsInline($user_id, $command, 'game_over');
                                $data_message['chat_id'] = $user_id;
                                $result_user = Request::sendMessage($data_message);
                                $data_message['chat_id'] = $opponent_user_id;
                                $data_message['text'] = BotTextsController::getText($opponent_user_id, $command, 'game_over');
                                $data_message['reply_markup'] = BotButtonsInlineController::getButtonsInline($opponent_user_id, $command, 'game_over');
                                $result_user = Request::sendMessage($data_message);
                            } else {
                                $data_message = ['chat_id' => $user_id];
                                $data_message['text'] = BotTextsController::getText($user_id, $command, 'game_no_over');
                                $data_message['parse_mode'] = 'html';
                                $result = Request::sendMessage($data_message);
                            }
                        }
                    }
                    if ($count_ships == self::$ships_need) {
                        $field->finished = 1;
                        $field->save();
                        $data_message = ['chat_id' => $user_id];
                        $data_message['text'] = BotTextsController::getText($user_id, $command, 'game_over');
                        $data_message['parse_mode'] = 'html';
                        if ($playWithBot == 1) {
                            $botShotGeneration = self::botShotGeneration($user_id, $game->id, $player_opponent, $field_user);
                            $data_message['reply_markup'] = BotButtonsInlineController::getButtonsInline($user_id, $command, 'game_over');
                        }
                        $result = Request::sendMessage($data_message);
                        if ($playWithBot == 0) {
                            if (BotGameSeaBattleGames::checkGameOver($game->id)) {
                                $data_message = [];
                                $data_message['text'] = BotTextsController::getText($user_id, $command, 'game_over');
                                $data_message['parse_mode'] = 'html';
                                $data_message['reply_markup'] = BotButtonsInlineController::getButtonsInline($user_id, $command, 'game_over');
                                $data_message['chat_id'] = $user_id;
                                $result_user = Request::sendMessage($data_message);
                                $data_message['chat_id'] = $opponent_user_id;
                                $result_user = Request::sendMessage($data_message);
                            } else {
                                $data_message = ['chat_id' => $user_id];
                                $data_message['text'] = BotTextsController::getText($user_id, $command, 'game_no_over');
                                $data_message['parse_mode'] = 'html';
                                $result = Request::sendMessage($data_message);
                            }
                        }                    }
                }
            }
            $text = str_replace("___SHOTS___", $game->$player_shots, $text);
            $text = str_replace("___SHIPS___", $count_ships, $text);
        }

        $data_media = [
            'type' => 'photo',
            'media' => $img,
            'caption' => $text,
            'parse_mode' => 'html'
        ];

        $data_edit = ['chat_id' => $user_id];
        $data_edit['reply_markup'] = BotButtonsInlineController::getButtonsInlineForGameSeaBattlePlay($user_id, $playWithBot, $game->id, $field_id, $shot->id, $fn);
        $data_edit['message_id'] = $message_id;
//        $data_edit['media'] = json_encode($data_media);
        $data_edit['media'] = $data_media;
        $result = Request::editMessageMedia($data_edit);
        $update_shots_message_id = BotGameSeaBattleGames::updateShotsMessageId($game->id, $player, $message_id);
        return $result;
    }

    public static function botShotGeneration($user_id, $game_id, $player, $field)
    {
        $game = BotGameSeaBattleGames::getGame($game_id);
        $player_opponent = 'player2';
        if ($game->player2_user_id == self::$bot_user_id) {
            $player_opponent = 'player1';
        }
        $player_shots = $player.'_shots';
        $player_opponent_shots = $player_opponent.'_shots';
        $player_count_ships = $player.'_count_ships';
        $player_opponent_count_ships = $player_opponent.'_count_ships';
        $player_count_bombs = $player.'_count_bombs';
        $player_opponent_count_bombs = $player_opponent.'_count_bombs';

        $shot = BotGameSeaBattleShots::getShotByFieldId($field->id);
        $count_ships = 0;
        $count_bombs = 0;
        while ($count_ships < self::$ships_need && $count_bombs == 0) {
            $rand = rand(1,16);
            $f = 'f'.$rand;
            if ($shot->$f == null) {
                $shot->$f = 1;
                $update_shot = BotGameSeaBattleShots::updateFired($shot->id, $rand);
                if ($update_shot) $game->increment($player_shots);
                if ($field->$f == BotGameSeaBattleIcons::$ship_id) {
                    $count_ships++;
                    $game->increment($player_count_ships);
                }
                elseif ($field->$f == BotGameSeaBattleIcons::$bomb_id) {
                    $count_bombs++;
                    $game->increment($player_count_bombs);
                }
            }
        }
        $field->finished = 1;
        $field->save();

        $user_win = null;
        if ($game->$player_count_ships == self::$ships_need && $game->$player_opponent_count_ships == self::$ships_need) {
            if ($game->$player_shots < $game->$player_opponent_shots)
                $user_win = self::$bot_user_id;
            elseif ($game->$player_shots > $game->$player_opponent_shots)
                $user_win = $user_id;
        }
        elseif ($game->$player_count_ships == self::$ships_need && $game->$player_opponent_count_ships < self::$ships_need && $game->$player_opponent_count_bombs > 0) {
            $user_win = self::$bot_user_id;
        }
        elseif ($game->$player_opponent_count_ships == self::$ships_need && $game->$player_count_ships < self::$ships_need && $game->$player_count_bombs > 0) {
            $user_win = $user_id;
        }
        elseif ($game->$player_count_bombs > 0 && $game->$player_opponent_count_bombs > 0) {
            if ($game->$player_count_ships > $game->$player_opponent_count_ships)
                $user_win = self::$bot_user_id;
            if ($game->$player_count_ships < $game->$player_opponent_count_ships)
                $user_win = $user_id;
        }
        $game->win_user_id = $user_win;
        $game->finished = 1;
        $game->save();

        if ($user_win == $user_id) {
            $cashback_add = BotCashbackController::cashbackPlusForSeaBattleForWin($user_id, $game_id);
            $incWin = BotGameSeaBattleUsers::incrementUserWin($user_id);
        }
        elseif ($user_win == null) {
            $cashback_add = BotCashbackController::cashbackPlusForSeaBattle($user_id, $game_id);
        }
        elseif ($user_win == self::$bot_user_id) {
            $incWin = BotGameSeaBattleUsers::incrementUserWin(self::$bot_user_id);
        }

        $user_no_play = BotGameSeaBattleUsers::setUserNoPlay($user_id);

        return true;
    }

    public static function generateGameResults($game)
    {
        $game_id = $game->id;
        $player = 'player1';
        $player_opponent = 'player2';
        $player_user = $player.'_user_id';
        $player_opponent_user = $player_opponent.'_user_id';
        $player_user_id = $game->$player_user;
        $player_opponent_user_id = $game->$player_opponent_user;
        $player_shots = $player.'_shots';
        $player_opponent_shots = $player_opponent.'_shots';
        $player_count_ships = $player.'_count_ships';
        $player_opponent_count_ships = $player_opponent.'_count_ships';
        $player_count_bombs = $player.'_count_bombs';
        $player_opponent_count_bombs = $player_opponent.'_count_bombs';

        $user_win = null;
        if ($game->$player_count_ships == self::$ships_need && $game->$player_opponent_count_ships == self::$ships_need) {
            if ($game->$player_shots < $game->$player_opponent_shots)
                $user_win = $player_user_id;
            elseif ($game->$player_shots > $game->$player_opponent_shots)
                $user_win = $player_opponent_user_id;
//            $user_win = $game->$player_shots < $game->$player_opponent_shots ? $player_user_id : $player_opponent_user_id;
        }
        elseif ($game->$player_count_ships == self::$ships_need && $game->$player_opponent_count_ships < self::$ships_need && $game->$player_opponent_count_bombs > 0) {
            $user_win = $player_user_id;
        }
        elseif ($game->$player_opponent_count_ships == self::$ships_need && $game->$player_count_ships < self::$ships_need && $game->$player_count_bombs > 0) {
            $user_win = $player_opponent_user_id;
        }
        elseif ($game->$player_count_bombs > 0 && $game->$player_opponent_count_bombs > 0) {
            if ($game->$player_count_ships > $game->$player_opponent_count_ships) $user_win = $player_user_id;
            if ($game->$player_count_ships < $game->$player_opponent_count_ships) $user_win = $player_opponent_user_id;
        }
        $game->win_user_id = $user_win;
        $game->finished = 1;
        $game->save();

        if ($user_win == null) {
            $cashback_add = BotCashbackController::cashbackPlusForSeaBattle($player_user_id, $game_id);
            $cashback_add = BotCashbackController::cashbackPlusForSeaBattle($player_opponent_user_id, $game_id);
        }
        else {
            $cashback_add_win = BotCashbackController::cashbackPlusForSeaBattleForWin($user_win, $game_id);
            $incWin = BotGameSeaBattleUsers::incrementUserWin($user_win);
        }
        $user_no_play = BotGameSeaBattleUsers::setUserNoPlay($player_user_id);
        $user_no_play = BotGameSeaBattleUsers::setUserNoPlay($player_opponent_user_id);
        return true;
    }

    public static function showResults($user_id, $message_id = null)
    {
        if ($message_id !== null) {
            $data_edit = ['chat_id' => $user_id];
            $data_edit['reply_markup'] = new InlineKeyboard([]);
            $data_edit['message_id'] = $message_id;
            $result_edit = Request::editMessageReplyMarkup($data_edit);
        }

        $command = 'GameSeaBattle';
        $game = BotGameSeaBattleGames::getLastGameByUserId($user_id);
        if ($game->win_user_id == $user_id) {
            $name = $message_id !== null ? 'show_results_win' : 'show_results_win_forced';
        }
        elseif ($game->win_user_id == null) {
            $name = $message_id !== null ? 'show_results_draw' : 'show_results_draw_forced';
        }
        else $name = $message_id !== null ? 'show_results_lost' : 'show_results_lost_forced';

        $cashback_all = BotCashbackController::getUserCashbackAll($user_id);
        $player = 'player1';
        $player_opponent = 'player2';
        if ($game->player2_user_id == $user_id) {
            $player = 'player2';
            $player_opponent = 'player1';
        }
        $player_opponent_user = $player_opponent.'_user_id';
        $player_opponent_user_id = $game->$player_opponent_user;
        $player_shots = $player.'_shots';
        $player_opponent_shots = $player_opponent.'_shots';
        $player_count_ships = $player.'_count_ships';
        $player_opponent_count_ships = $player_opponent.'_count_ships';
        $player_count_bombs = $player.'_count_bombs';
        $player_opponent_count_bombs = $player_opponent.'_count_bombs';

        $text = BotTextsController::getText($user_id, $command, $name);
        $currency = BotTextsController::getText($user_id, 'System', 'currency');

        if ($game->player1_user_id == self::$bot_user_id || $game->player2_user_id == self::$bot_user_id) {
            $opponent = BotTextsController::getText($user_id, $command, 'play_with_opponent_bot');
        }
        else {
            $opponent = self::getNameByUserId($player_opponent_user_id);
        }

        $text = str_replace("___OPPONENT___", $opponent, $text);
        $text = str_replace("___PLAYER_OPPONENT_SHOTS___", $game->$player_opponent_shots, $text);
        $text = str_replace("___PLAYER_OPPONENT_COUNT_SHIPS___", $game->$player_opponent_count_ships, $text);
        $text = str_replace("___PLAYER_OPPONENT_COUNT_BOMBS___", $game->$player_opponent_count_bombs, $text);
        $text = str_replace("___PLAYER_SHOTS___", $game->$player_shots, $text);
        $text = str_replace("___PLAYER_COUNT_SHIPS___", $game->$player_count_ships, $text);
        $text = str_replace("___PLAYER_COUNT_BOMBS___", $game->$player_count_bombs, $text);
        $text = str_replace("___CASHBACK___", $cashback_all, $text);
        $text = str_replace("___CURRENCY___", $currency, $text);

        $data_message = ['chat_id' => $user_id];
        $data_message['text'] = $text;
        $data_message['parse_mode'] = 'html';
        $data_message['reply_markup'] = BotButtonsInlineController::getButtonsInline($user_id, $command, 'show_results');
        $result = Request::sendMessage($data_message);
    }

    public static function rateGame($user_id, $message_id)
    {
        $command = 'GameSeaBattle';
        $name = 'rate_game';

        $data_edit = ['chat_id' => $user_id];
        $data_edit['reply_markup'] = new InlineKeyboard([]);
        $data_edit['message_id'] = $message_id;
        $result_edit = Request::editMessageReplyMarkup($data_edit);

        $game = BotGameSeaBattleGames::getLastGameByUserId($user_id);

        $data_message = ['chat_id' => $user_id];
        $data_message['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_message['parse_mode'] = 'html';
        $data_message['reply_markup'] = BotButtonsInlineController::getButtonsInlineForGameSeaBattleRate($user_id, $game->id);
        $result = Request::sendMessage($data_message);
    }

    public static function rateGameComment($user_id, $message_id, $game_id, $rate)
    {
        $command = 'GameSeaBattle';
        $name = 'rate_game_comment';

        $data_edit = ['chat_id' => $user_id];
        $data_edit['reply_markup'] = new InlineKeyboard([]);
        $data_edit['message_id'] = $message_id;
        $result_edit = Request::editMessageReplyMarkup($data_edit);

        $game = BotGameSeaBattleGames::getGame($game_id);


        $new_rate = new BotGameSeaBattleRates;
        $new_rate->user_id = $user_id;
        $new_rate->game_id = $game_id;
        $new_rate->rate = $rate;
        $new_rate->save();

        $change_key = BotUsersNavController::updateValue($user_id, 'change_key', 'send_comment_game_sea_battle_rate');
        $update_rate_id = BotUsersNavController::updateValue($user_id, 'game_sea_battle_rate_id', $new_rate->id);

        $buttons = BotButtonsController::getButtons($user_id, 'Order', ['no_comments']);
        $keyboard_bottom = new Keyboard([]);
        foreach ($buttons as $button) {
            $keyboard_bottom->addRow($button);
        }
        $keyboard_b = $keyboard_bottom
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->setSelective(false);

        $data_message = ['chat_id' => $user_id];
        $data_message['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_message['parse_mode'] = 'html';
        $data_message['reply_markup'] = $keyboard_b;
        $result = Request::sendMessage($data_message);
        return $result;
    }

    public static function rateGameCommentSend($user_id, $text)
    {
        $command = 'GameSeaBattle';
        $name = 'rate_game_comment_send';

        $remove_keyboard = StartCommandController::removeKeyboardBottom($user_id);

        $rate_id = BotUsersNavController::getValue($user_id, 'game_sea_battle_rate_id');
        if (!is_null($rate_id)) {
            $send_comment = BotGameSeaBattleRates::sendComment($rate_id, $user_id, $text);
            BotUsersNavController::updateValue($user_id, 'change_key', null);
            BotUsersNavController::updateValue($user_id, 'game_sea_battle_rate_id', null);
        }

        $data_message = ['chat_id' => $user_id];
        $data_message['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_message['parse_mode'] = 'html';
        $data_message['reply_markup'] = BotButtonsInlineController::getButtonsInline($user_id, $command, $name);
        $result = Request::sendMessage($data_message);
        return $result;
    }

    public static function removeInlineKeyboardForPhoto($user_id, $message_id)
    {
        $data_edit = ['chat_id' => $user_id];
        $data_edit['photo'] = BotGameSeaBattleImages::getFileIdByN(0);
        $data_edit['parse_mode'] = 'html';
        $data_edit['reply_markup'] = new InlineKeyboard([]);
        $data_edit['message_id'] = $message_id;
        $result = Request::editMessageCaption($data_edit);
    }

    public static function sendMessgaeToUserGameLate($user_id, $name)
    {
        $command = 'GameSeaBattle';
        $data_message = ['chat_id' => $user_id];
        $data_message['text'] = BotTextsController::getText($user_id, $command, $name);;
        $data_message['parse_mode'] = 'html';
        $data_message['reply_markup'] = BotButtonsInlineController::getButtonsInline($user_id, $command, $name);
        $result = Request::sendMessage($data_message);
        return $result;
    }

    public static function finishFieldsAndGame($game, $forced = null)
    {
        $finish_field1 = BotGameSeaBattleFields::setFieldFinished($game->player1_field_id);
        $finish_field2 = BotGameSeaBattleFields::setFieldFinished($game->player2_field_id);
        $finish_game = BotGameSeaBattleGames::setGameLateFinished($game->id, $forced);
        return true;
    }

    public static function addCashbackToUsers($game)
    {
        $cashback_add1 = BotCashbackController::cashbackPlusForSeaBattle($game->player1_user_id, $game->id);
        $cashback_add2 = BotCashbackController::cashbackPlusForSeaBattle($game->player2_user_id, $game->id);
        return true;
    }

    public static function setGameLate($game, $forced = null)
    {
        $user1_no_play = BotGameSeaBattleUsers::setUserNoPlay($game->player1_user_id);
        $user2_no_play = BotGameSeaBattleUsers::setUserNoPlay($game->player2_user_id);
        if ($forced == null) {
            $finish = self::finishFieldsAndGame($game);
            $cashbackAdd = self::addCashbackToUsers($game);
            $send = self::sendMessgaeGameLate($game);
        }
        else {
            $check_field1 = BotGameSeaBattleFields::checkFieldFinished($game->player1_field_id);
            $check_field2 = BotGameSeaBattleFields::checkFieldFinished($game->player2_field_id);
            $user_win = null;
            $player_win = null;
            $player_lost = null;
            if ($check_field1 == 1 && $check_field2 == 0) {
                $user_win = $game->player2_user_id;
                $player_win = 'player2';
                $player_lost = 'player1';
            }
            elseif ($check_field1 == 0 && $check_field2 == 1) {
                $user_win = $game->player1_user_id;
                $player_win = 'player1';
                $player_lost = 'player2';
            }
            $update_win = $user_win !== null ? BotGameSeaBattleGames::updateUserWin($game->id, $user_win) : null;
            $finish = self::finishFieldsAndGame($game, $forced);
            if ($player_win !== null && $player_lost !== null) {
                $user_id_win = $player_win.'_user_id';
                $cashbackAddForWin = BotCashbackController::cashbackPlusForSeaBattleForWin($game->$user_id_win, $game->id);
            }
            else {
                $cashbackAdd = self::addCashbackToUsers($game);
            }
            $send1 = self::showResults($game->player1_user_id);
            $send2 = self::showResults($game->player2_user_id);

        }
        return true;
    }

    public static function sendMessgaeGameLate($game)
    {
        if ($game->player1_start_message_id !== null) {
            $result = self::removeInlineKeyboardForPhoto($game->player1_user_id, $game->player1_start_message_id);
            $send1 = self::sendMessgaeToUserGameLate($game->player1_user_id, 'game_late');
        }

        if ($game->player2_start_message_id !== null) {
            $result = self::removeInlineKeyboardForPhoto($game->player2_user_id, $game->player2_start_message_id);
            $send2 = self::sendMessgaeToUserGameLate($game->player2_user_id, 'game_late');
        }
        return true;
    }

}
