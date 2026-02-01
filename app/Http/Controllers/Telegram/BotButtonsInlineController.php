<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotGameSeaBattleFields;
use App\Models\BotGameSeaBattleGames;
use App\Models\BotGameSeaBattleIcons;
use App\Models\BotGameSeaBattleShots;
use App\Models\BotSettingsButtonsInline;
use App\Http\Controllers\Controller;
use App\Models\PrestaShop_Product;
use App\Models\PrestaShop_Product_Attribute;
use App\Models\PrestaShop_Product_Attribute_Combination;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Request;

class BotButtonsInlineController extends Controller
{
    public static function getButtonsInline($user_id, $button_command, $button_name) {

        $lang = BotUserSettingsController::getLang($user_id);

        $botton_lang = 'button_value_'.$lang;

        $inline_keyboard = new InlineKeyboard([]);

        if (!isset($button_name) || $button_name == null || $button_name == '') $buttons = BotSettingsButtonsInline::where('button_command', $button_command)->where('enabled', 1)->orderBy('button_sort', 'asc')->get();
        else $buttons = BotSettingsButtonsInline::where('button_command', $button_command)->where('button_name', $button_name)->where('enabled', 1)->orderBy('button_sort', 'asc')->get();

        foreach ($buttons as $button) {

//            $data_text = ['chat_id' => $user_id];
//            $data_text['text'] = 'text: '.$button->$botton_lang.'; data: '.$button->button_data;
//            Request::sendMessage($data_text);

            if ($button->button_name == 'share') {

                $inline_keyboard->addRow(new InlineKeyboardButton([
                    'text'          => $button->$botton_lang,
                    'switch_inline_query' => BotTextsController::getText($user_id, $button_command, 'share')
                ]));

            }
            else {

                $inline_keyboard->addRow(new InlineKeyboardButton([
                    'text'          => $button->$botton_lang,
                    'callback_data' => $button->button_data
                ]));

            }
        }

        return $inline_keyboard;

    }

    public static function getButtonInline($user_id, $button_command, $button_name) {

        $lang = BotUserSettingsController::getLang($user_id);

        $botton_lang = 'button_value_'.$lang;

        if (!isset($button_name) || $button_name == null || $button_name == '') $buttons = BotSettingsButtonsInline::where('button_command', $button_command)->where('enabled', 1)->orderBy('button_sort', 'asc')->get();
        else $buttons = BotSettingsButtonsInline::where('button_command', $button_command)->where('button_name', $button_name)->where('enabled', 1)->orderBy('button_sort', 'asc')->get();

        $text = null;
        $data = null;

        foreach ($buttons as $button) {

//            $data_text = ['chat_id' => $user_id];
//            $data_text['text'] = 'text: '.$button->$botton_lang.'; data: '.$button->button_data;
//            Request::sendMessage($data_text);

            if ($button->button_name == 'share') {

                $text = $button->$botton_lang;
                $data = BotTextsController::getText($user_id, $button_command, 'share');
                $data = str_replace("___USER_ID___", $user_id, $data);
                $data = str_replace("___BOT_NAME___", env('PHP_TELEGRAM_BOT_NAME'), $data);

            }
            else {

                $text = $button->$botton_lang;
                $data = $button->button_data;

            }
        }

        return [$text, $data];

    }

    public static function getRafflePizzasButtonsInline($user_id, $inline_keyboard) {

        $lang = BotUserSettingsController::getLang($user_id);
        $botton_lang = 'name_'.$lang;

//        $products = BotRaffleController::getRafflePizzas();
//        foreach ($products as $product) {
//            $inline_keyboard->addRow(new InlineKeyboardButton([
//                'text'          => ''.$product->$botton_lang.' '.$product->variant_name,
//                'callback_data' => 'add_action_pizza___'.$product->id.'___'.$product->variant_id
//            ]));
//        }

        $product_attributes = PrestaShop_Product_Attribute::all();
        $products = PrestaShop_Product::join('ps_product_lang', 'ps_product_lang.id_product', 'ps_product.id_product')
            ->where('ps_product.id_category_default', 38)
            ->where('ps_product_lang.id_lang', 2)
            ->get();
        foreach ($products as $product) {
            $product_attribute = $product_attributes->where('id_product', $product->id_product)->where('default_on', 1)->first();
            if (!$product_attribute)
                $product_attribute = $product_attributes->where('id_product', $product->id_product)->first();
            $product->attribute = $product_attribute;
            $product->price = $product_attribute ? $product_attribute->price : 0;
            if ($product_attribute) {
                $id_product_attribute = $product_attribute->id_product_attribute;
                $variant = PrestaShop_Product_Attribute_Combination::join('ps_attribute_lang', 'ps_attribute_lang.id_attribute', 'ps_product_attribute_combination.id_attribute')
                    ->where('ps_product_attribute_combination.id_product_attribute', $id_product_attribute)
                    ->where('ps_attribute_lang.id_lang', 2)
                    ->first()
                    ->name;
                $product->variant = $variant;
                $inline_keyboard->addRow(new InlineKeyboardButton([
                    'text'          => ''.$product->name.' '.$product->variant,
                    'callback_data' => 'add_action_pizza___'.$product->id_product.'___'.$id_product_attribute
                ]));
            }
        }
        return $inline_keyboard;
    }

    public static function deleteInlineKeyboardFromMessageId($user_id, $key)
    {

        $message_old_id = BotUsersNavController::getValue($user_id, $key);
        if ($message_old_id !== null) {
            $data_delete = ['chat_id' => $user_id];
            $data_text['reply_markup'] = new InlineKeyboard([]);
            $data_delete['message_id'] = $message_old_id;
            Request::editMessageReplyMarkup($data_delete);
        }

    }

    public static function getGamesButtonsInline($user_id)
    {

        $lang = BotUserSettingsController::getLang($user_id);
        $botton_lang = 'button_value_'.$lang;
        $inline_keyboard = new InlineKeyboard([]);

        $buttons = BotSettingsButtonsInline::where('button_command', 'Game')->where('button_name', 'games')->where('enabled', 1)->orderBy('button_sort', 'asc')->get();
        foreach ($buttons as $button) {
            $inline_keyboard->addRow(new InlineKeyboardButton([
                'text'          => $button->$botton_lang,
                'callback_data' => $button->button_data.''.$button->emoji.'___'.$button->value
            ]));
        }
        return $inline_keyboard;

    }

    public static function getButtonsInlineForGameSeaBattle($user_id, $field_id, $go = 0)
    {
        $inline_keyboard = new InlineKeyboard([]);
        $field_icons = BotGameSeaBattleFields::countIconsInFieldAndGetField($field_id);
        $count_icons = $field_icons['icons'];
        $count_ships = $field_icons['ships'];
        $count_bombs = $field_icons['bombs'];

        if ($count_icons > 0) {
            $lang = BotUserSettingsController::getLang($user_id);
            $botton_lang = 'button_value_'.$lang;
            $field = $field_icons['field'];
            $icons = BotGameSeaBattleIcons::getIcons();

            $n = 0;
            $button = [];
            for ($i = 1; $i <= 16; $i++) {
                $n++;
                $field_n = 'f'.$i;
                $button[$n] = new InlineKeyboardButton(['text' => $field->$field_n != null ? $icons->where('id', $field->$field_n)->first()['icon'] : '+', 'callback_data' => 'game_sea_battle_field_set___'.$field_id.'___'.$i]);
                if ($n == 4) {
                    $inline_keyboard->addRow($button[1], $button[2], $button[3], $button[4]);
                }
                if ($n == 4) {
                    $n = 0;
                    $button = [];
                }
            }
            if ($go == 0) {
                $buttons = BotSettingsButtonsInline::where('button_command', 'GameSeaBattle')->where('button_name', 'field_clear')->where('enabled', 1)->orderBy('button_sort', 'asc')->get();
                foreach ($buttons as $button) {
                    $inline_keyboard->addRow(new InlineKeyboardButton([
                        'text'          => $button->$botton_lang,
                        'callback_data' => $button->button_data.''.$field_id
                    ]));
                }
                if ($count_ships == 4 && $count_bombs == 1) {
                    $buttons = BotSettingsButtonsInline::where('button_command', 'GameSeaBattle')->where('button_name', 'go')->where('enabled', 1)->orderBy('button_sort', 'asc')->get();
                    foreach ($buttons as $button) {
                        $inline_keyboard->addRow(new InlineKeyboardButton([
                            'text'          => $button->$botton_lang,
                            'callback_data' => $button->button_data.''.$field_id
                        ]));
                    }
                }
            }
        }
        else {
            $n = 0;
            $button = [];
            for ($i = 1; $i <= 16; $i++) {
                $n++;
                $button[$n] = new InlineKeyboardButton(['text' => '+', 'callback_data' => 'game_sea_battle_field_set___'.$field_id.'___'.$i]);
                if ($n == 4) {
                    $inline_keyboard->addRow($button[1], $button[2], $button[3], $button[4]);
                    $n = 0;
                    $button = [];
                }
            }
        }
        return $inline_keyboard;
    }

    public static function getButtonsInlineForGameSeaBattlePlay($user_id, $playWithBot, $game_id, $field_id, $shot_id, $f = 0)
    {
        $inline_keyboard = new InlineKeyboard([]);
        $icons = BotGameSeaBattleIcons::getIcons();
        $game = BotGameSeaBattleGames::getGame($game_id);
        $field = BotGameSeaBattleFields::getField($field_id);
        $shot = BotGameSeaBattleShots::getShot($shot_id);

        $n = 0;
        $button = [];
        for ($i = 1; $i <= 16; $i++) {
            $n++;
            $ins = '❓';
            $field_n = 'f'.$i;
            if ($shot->$field_n == 1) {
                if ($field->$field_n == BotGameSeaBattleIcons::$ship_id || $field->$field_n == BotGameSeaBattleIcons::$bomb_id) $ins = $icons->where('id', $field->$field_n)->first()['icon'];
                elseif ($field->$field_n == null) $ins = '😐';
            }

            $button[$n] = new InlineKeyboardButton(['text' => $ins, 'callback_data' => 'game_sea_battle_field_shot___'.$playWithBot.'___'.$game_id.'___'.$field_id.'___'.$shot_id.'___'.$i]);
            if ($n == 4) {
                $inline_keyboard->addRow($button[1], $button[2], $button[3], $button[4]);
                $n = 0;
                $button = [];
            }
        }
        return $inline_keyboard;
    }

    public static function getButtonsInlineForGameSeaBattleStart($user_id, $playWithBot)
    {
        $inline_keyboard = new InlineKeyboard([]);
        $lang = BotUserSettingsController::getLang($user_id);
        $botton_lang = 'button_value_'.$lang;

        $buttons = BotSettingsButtonsInline::where('button_command', 'GameSeaBattle')->where('button_name', 'play_with_opponent')->where('enabled', 1)->orderBy('button_sort', 'asc')->get();
        foreach ($buttons as $button) {
            $inline_keyboard->addRow(new InlineKeyboardButton([
                'text'          => $button->$botton_lang,
                'callback_data' => $button->button_data.$playWithBot
            ]));
        }
        return $inline_keyboard;
    }

    public static function getButtonsInlineForGameSeaBattleRate($user_id, $game_id)
    {
        $inline_keyboard = new InlineKeyboard([]);
        $n = 0;
        $buttons = [];
        for ($i = 1; $i <= 10; $i++) {
            $n++;
            $buttons[$n] = new InlineKeyboardButton(['text' => $i, 'callback_data' => 'game_sea_battle_set_rate___'.$game_id.'___'.$i]);
            if ($n == 5) {
                $inline_keyboard->addRow($buttons[1], $buttons[2], $buttons[3], $buttons[4], $buttons[5]);
                $n = 0;
                $buttons = [];
            }
        }
        return $inline_keyboard;
    }

}
