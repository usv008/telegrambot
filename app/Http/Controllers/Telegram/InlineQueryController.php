<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotMenu;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Telegram\BotUserSettingsController;
use App\Models\Simpla_Categories;
use App\Models\Simpla_Options;
use App\Models\Simpla_Products;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\InputMessageContent\InputTextMessageContent;

use App\Models\BotSettingsTexts;
use App\Models\BotSettingsButtonsInline;
use App\Models\BotSettingsButtons;


class InlineQueryController extends Controller
{

    public static function search($user_id, $next_offset, $take, $text) {

        $lang = BotUserSettingsController::getLang($user_id);
        $menu_lang = 'menu_value_'.$lang;
        $menu_data = 'menu_data_'.$lang;

        $menu_options_arr = BotMenuController::getMenuOptionsArr($user_id);
        $articles = [];

        $cat = BotMenu::where($menu_data, 'like', '%'.$text.'%')->first();
        if ($cat) $cat = $cat->menu_key;

        // находим и убираем из текста категорию, например, "пицца"
        $menus = BotMenu::orderBy('menu_sort', 'asc')->get();
        foreach ($menus as $menu) {

            if (strripos($text, $menu->$menu_data) !== false) $text = str_replace($menu->$menu_data, "", $text);

        }

        $product_search = Simpla_Products::where('name', 'like', '%'.$text.'%')->count();

        if ($cat !== null) {

            if ($cat == 'pizza') {
                MenuCommandController::show_menu_options($user_id, null);
                MenuCommandController::execute($user_id, 'send', null);
            }
            $cat_id = Simpla_Categories::where('url', $cat)->first()['id'];
            $products = BotMenuController::get_menu_sql($user_id, $cat_id);
            $articles = MenuCommandController::get_articles($user_id, 'products', $products);

        }
        elseif ($product_search > 0) {

            $products = BotMenuController::searchForInlineQuery($user_id, $text);
            $articles = MenuCommandController::get_articles($user_id, 'products', $products);

        }
        elseif (in_array($text, $menu_options_arr)) {

            $products = BotMenuController::menuOptionsForInlineQuery($user_id, $text);
            $articles = MenuCommandController::get_articles($user_id, 'products', $products);

        }
        elseif ($text == 'my_orders') {

            $arr = BotOrderController::getOrderFromUserId($user_id);
            $articles = MenuCommandController::get_articles($user_id, 'my_orders', $arr);

        }
        else {

            $text_ins = '';
            if (stripos($text, 'start=share') !== false) $text_ins = BotTextsController::getText($user_id, 'More', 'share');
            elseif (stripos($text, 'start=raffle') !== false) $text_ins = BotTextsController::getText($user_id, 'Raffle', 'share');

            if ($text_ins !== '') {

                $text_ins = str_replace("___USER_ID___", $user_id, $text_ins);
                $text_ins = str_replace("___BOT_NAME___", env('PHP_TELEGRAM_BOT_NAME'), $text_ins);

                $title = BotTextsController::getText($user_id, 'Inline', 'title');
                $description = BotTextsController::getText($user_id, 'Inline', 'description');

                $articles[] = [
                    'type'                  => 'article',
                    'id'                    => 'id1',
                    'title'                 => $title,
                    'description'           => $description,
                    'thumb_url'             => env('PHP_TELEGRAM_BOT_URL').'assets/img/share.png',
                    'thumb_width'           => 64,
                    'thumb_height'          => 64,
//                    'url'                   => 'https://t.me/emdebugbot',
//                    'hide_url'              => true,
//                    'message_text'          => 'test test test',
                    'input_message_content' => new InputTextMessageContent(['message_text' => $text_ins, 'disable_web_page_preview' => true, 'parse_mode' => 'html']),
                ];

            }
            else {

                $articles[] = [
                    'type'                  => 'article',
                    'id'                    => 'id1',
                    'title'                 => '---',
                    'description'           => '',
//                'thumb_url'             => $img,
                    'thumb_width'           => 64,
                    'thumb_height'          => 64,
//                'url'                   => 'https://t.me/emdebugbot',
//                'hide_url'              => true,
//                'message_text'          => 'test test test',
                    'input_message_content' => new InputTextMessageContent(['message_text' => '---']),
                ];

            }

        }

        $next_offset = null;

        return array($next_offset, $articles);

    }

    public static function get_menu($user_id, $next_offset, $take) {

        $articles = [];

        $cat_id = Simpla_Categories::where('url', 'pizza')->first()['id'];

        $products = BotMenuController::get_menu_sql($user_id, $cat_id);

        $articles = MenuCommandController::get_articles($user_id, 'products', $products);

        $next_offset = null;

        return array($next_offset, $articles);

    }

}
