<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotMenu;
use App\Models\BotOrder;
use App\Models\BotOrderContent;
use App\Models\BotOrders;
use App\Models\BotSettingsSticker;
use App\Models\BotUser;
use App\Models\BotUsersNav;
use App\Http\Controllers\Controller;

use App\Http\Controllers\SimplaRegionsController;
use App\Models\Simpla_Categories;
use App\Models\Simpla_Images;
use App\Models\Simpla_Products;
use App\Models\Simpla_Regions;
use App\Models\Simpla_Variants;
use Illuminate\Http\Request as LRequest;

use Longman\TelegramBot\Entities\InputMessageContent\InputTextMessageContent;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;

use App\Models\BotSettingsTexts;
use App\Models\BotSettingsButtonsInline;

class MenuCommandController extends Controller
{

    public static function execute ($user_id, $act, $message_id)
    {

        $command = 'Menu';
        $name = 'show_menu';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command);

        self::show_menu_options($user_id, null);

//        $inline_keyboard = new InlineKeyboard([]);
//
//        $lang = BotUserSettingsController::getLang($user_id);
//        $menu_lang = 'menu_value_' . $lang;
//        $menu_data = 'menu_data_' . $lang;
//
//        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Menu', 'pizza');
//
//        $menus = BotMenuController::getMenu();
//
//        $i = 0;
//        foreach ($menus as $menu) {
//
//            $i++;
//            if ($i == 1) {
////                if ($menu['menu_key'] == 'pizza') $ins1 = new InlineKeyboardButton(['text' => $text, 'callback_data' => $data.$menu['menu_key']]);
////                else $ins1 = new InlineKeyboardButton(['text' => $menu[$menu_lang], 'switch_inline_query_current_chat' => $menu[$menu_data]]);
//                $ins1 = new InlineKeyboardButton(['text' => $menu[$menu_lang], 'switch_inline_query_current_chat' => $menu[$menu_data]]);
//            }
//            if ($i == 2) {
////                if ($menu['menu_key'] == 'pizza') $ins2 = new InlineKeyboardButton(['text' => $text, 'callback_data' => $data.$menu['menu_key']]);
////                else $ins2 = new InlineKeyboardButton(['text' => $menu[$menu_lang], 'switch_inline_query_current_chat' => $menu[$menu_data]]);
//                $ins2 = new InlineKeyboardButton(['text' => $menu[$menu_lang], 'switch_inline_query_current_chat' => $menu[$menu_data]]);
//                $inline_keyboard->addRow($ins1, $ins2);
//                $i = 0;
//            }
//
//        }
//        if ($i == 1) $inline_keyboard->addRow($ins1);
//
//        // Вытягиваем из БД текст для сообщения
//        $data_text = ['chat_id' => $user_id];
//        $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
//        $data_text['parse_mode'] = 'html';
//        $data_text['reply_markup'] = $inline_keyboard;
//
//        if ($act == 'send') {
//            $send_text = Request::sendMessage($data_text);
//        }
//        elseif ($act == 'edit') {
//            $data_text['message_id'] = $message_id;
//            Request::editMessageText($data_text);
//        }

    }

    public static function show_menu_options ($user_id, $message_id) {

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', 'Menu');

        $region_id = BotUser::getValue($user_id, 'city_id');
        if ($region_id !== null && (int)$region_id > 0) {
            $region = SimplaRegionsController::getRegionNameFromId($user_id, $region_id);
            $text = BotTextsController::getText($user_id, 'Menu', 'show_menu_new');
            $text .= '<b>'.$region.'</b>';
            BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'to_delete_message_id', null);
            $inline_keyboard = BotMenuButtonsController::get_menu_buttons($user_id, $region_id);

            // Вытягиваем из БД текст для сообщения
            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = $text;
            $data_text['parse_mode'] = 'html';
            $data_text['reply_markup'] = $inline_keyboard;
//        $data_text['message_id'] = $message_id;
//        Request::editMessageText($data_text);
            $send_text = Request::sendMessage($data_text);
            if ($send_text->getResult() !== null) {
                $message_id = $send_text->getResult()->getMessageId();

                $message_old_id = BotMenuController::getMenuMessageId($user_id);
                if ($message_old_id !== null) {
                    $data_delete = ['chat_id' => $user_id];
                    $data_delete['message_id'] = $message_old_id;
                    Request::deleteMessage($data_delete);
                }
                BotMenuController::updateMenuMessageId($user_id, $message_id);
            }
            BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'order_message_id', null);
        }
        else {
            StartCommandController::change_city($user_id, $message_id);
        }


    }

    public static function show_menu_filter_options ($user_id, $message_id) {

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', 'Filter');

        BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'to_delete_message_id', null);

        $inline_keyboard = BotMenuButtonsController::get_menu_filter_buttons($user_id);

//        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Menu', 'menu_option_back');
//        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]) );

        // Вытягиваем из БД текст для сообщения
        $data_text = ['chat_id' => $user_id];
        $data_text['parse_mode'] = 'html';
        $data_text['reply_markup'] = $inline_keyboard;
        $data_text['message_id'] = $message_id;
        Request::editMessageReplyMarkup($data_text);

    }

    public static function show_menu_filter_back_options ($user_id, $message_id) {

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', 'Menu');

        BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'to_delete_message_id', null);

        $region_id = BotUser::getValue($user_id, 'city_id');
        $inline_keyboard = BotMenuButtonsController::get_menu_buttons($user_id, $region_id);

//        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Menu', 'menu_option_back');
//        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]) );

        // Вытягиваем из БД текст для сообщения
        $data_text = ['chat_id' => $user_id];
        $data_text['parse_mode'] = 'html';
        $data_text['reply_markup'] = $inline_keyboard;
        $data_text['message_id'] = $message_id;
        Request::editMessageReplyMarkup($data_text);

    }

    public static function get_articles ($user_id, $act, $arrs)
    {

        $articles = [];
        $currency = BotTextsController::getText($user_id, 'System', 'currency');

        $title = BotTextsController::getText($user_id, 'Menu', 'menu_back');
        $description = BotTextsController::getText($user_id, 'Menu', 'menu_back_descr');
        $text_ins = BotTextsController::getText($user_id, 'Menu', 'menu_back_text');

        $articles[] = [
            'type'                  => 'article',
            'id'                    => 'id1',
            'title'                 => $title,
            'description'           => $description,
            'thumb_url'             => env('PHP_TELEGRAM_BOT_URL').'assets/img/back_menu.png',
            'thumb_width'           => 64,
            'thumb_height'          => 64,
//                    'url'                   => 'https://t.me/emdebugbot',
//                    'hide_url'              => true,
//                    'message_text'          => 'test test test',
            'input_message_content' => new InputTextMessageContent(['message_text' => $text_ins]),
        ];

        foreach ($arrs as $arr) {

//            $img = route('thumb').'?filename='.$product['filename'];
//            $img = route('thumb') . '?product_id=' . $product['id'];
//            $img = env('APP_URL') . 'assets/img/thumb/' . $product['id'] . '.png';

            if ($act == 'products') {
                $img = BotMenuController::getThumbAddr($arr['id']);
                $description = strip_tags($arr['description']);
                $description = preg_replace('/\,(?!\,)/', ', ', $description);
                $description = str_replace("&nbsp;", " ", $description);

                list($price, $name, $stock) = BotCartController::getMaxSumFromVariantFromProductId($arr['id']);

                $not_available = BotTextsController::getText($user_id, 'Menu', 'not_available');

                $text_ins = $stock === null || $stock > 0 ? $name !== '' ? '('.$name.' / '.$price.' '.$currency.') ' : '('.$price.' '.$currency.') ' : $not_available.' ';
                $name_ins = $arr['name'];
                $content_ins = $arr['name'];
            }
            elseif ($act == 'my_orders') {
                $my_order_text = BotTextsController::getText($user_id, 'MyOrders', 'my_orders');
                $name_ins = $my_order_text.$arr['simpla_id'].' ('.date("d.m.Y", strtotime($arr['order_date_reg'])).')';
                $content_ins = $my_order_text.$arr['simpla_id'];

                $description = '';
                $orders = BotOrderContent::where('order_id', $arr['id'])->where('parent_product_id', null)->get();
                $total = count($orders);
                $counter = 0;
                foreach ($orders as $order) {

                    $counter++;
                    if (stripos($order['product_name'], 'подарочная') !== false) $description .= '';
                    else {
                        $description .= $order['product_name'].' '.BotCartController::getProductVariant($order['id_size'])['name'].'';
                        $description .= $counter < $total ? ', ' : '';
                    }

                }

//                $description = ''.$arr['order_price'].' '.$currency;
                $text_ins = '';
                $img = env('APP_URL') . 'assets/img/order2.png';
            }

////            $data_t = ['chat_id' => $user_id];
//            $data_t = ['chat_id' => '522750680'];
////            $data_t['text'] = 'inlinequery: '.$product['id'].'; '.$product['name'].'; '.$text_ins.$description.PHP_EOL.$img;
//            $data_t['text'] = 'inlinequery: '.PHP_EOL.$arr['id'].'; '.$name_ins.'; '.$text_ins.$description.PHP_EOL.$img;
//            $send_t = Request::sendMessage($data_t);



            $articles[] = [
                'type' => 'article',
                'id' => $arr['id'],
                'title' => $name_ins,
                'description' => $text_ins.$description,
                'thumb_url' => $img,
                'thumb_width' => 64,
                'thumb_height' => 64,
//                'url'                   => 'https://t.me/emdebugbot',
//                'hide_url'              => true,
//                'message_text'          => 'test test test',
                'input_message_content' => new InputTextMessageContent(['message_text' => $content_ins]),
            ];

        }

        return $articles;

    }

    public static function get_product_from_id ($product_id, $user_id) {

        $product = BotMenuController::get_product_sql($user_id, $product_id);

        BotUserHistoryController::insertToHistory($user_id, 'send', $product['name']);

        $photo = 'http://ecopizza.com.ua/files/originals/'.$product['filename'];

        $description = strip_tags($product['description']);
        $description = preg_replace('/\,(?!\,)/', ', ', $description);
        $description = str_replace("&nbsp;", " ", $description);

        // Отправляем фото товара
        $data = ['chat_id' => $user_id];
        $data['caption'] = '<b>'.$product['name'].'</b>'.PHP_EOL.$description;
        $data['photo'] = $photo;
        $data['parse_mode'] = 'html';
//        $data['reply_markup'] = $inline_keyboard;
        $result_photo = Request::sendPhoto($data);

        self::show_product_from_id($user_id, $product_id, null, 'menu');

    }

    public static function show_product_from_id ($user_id, $product_id, $variant_id, $act)
    {

        // Формируем кнопки для следующего сообщения
        $inline_keyboard = new InlineKeyboard([]);
        if ($act == 'menu') {
            $inline_keyboard = BotMenuButtonsController::get_buttons_buy($user_id, $product_id);
        } elseif ($act == 'cart') {
            $inline_keyboard = BotMenuButtonsController::get_buttons_edit_buy($user_id, $product_id, $variant_id);
        }
        $inline_keyboard = BotMenuButtonsController::get_other_buttons($user_id, $inline_keyboard);

        // Отправляем сообщение о предложении добавить в заказ данный товар
        $product = BotMenuController::get_product_sql($user_id, $product_id);
        $text = BotTextsController::getText($user_id, 'Menu', 'ask_product_add');
        $ins = $act == 'cart' ? ' (' . BotCartController::getProductVariant($variant_id)['name'] . ')' : '';
        $text = str_replace("___PRODUCT_NAME___", '<b>' . $product['name'] . $ins . '</b>', $text);

        if ($act == 'menu') {

            if (BotCartController::checkProductIdInCart($user_id, $product_id) > 0) {

                $text = BotTextsController::getText($user_id, 'Menu', 'update_message_add') . ':' . PHP_EOL;
                $cart_products = BotCartController::getProductInCartFromProductId($user_id, $product_id);
                $text = self::get_foreach_product_from_id($user_id, $cart_products, $text);

            }

        } elseif ($act == 'cart') {

            if (BotCartController::checkProductAndVariantIdInCart($user_id, $product_id, $variant_id) > 0) {

                $text = BotTextsController::getText($user_id, 'Menu', 'message_edit') . ':' . PHP_EOL;
                $cart_products = BotCartController::getProductAndVariantInCartFromProductId($user_id, $product_id, $variant_id);
                $text = self::get_foreach_product_from_id($user_id, $cart_products, $text);

            }

        }

        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = $text;
        $data_text['parse_mode'] = 'html';
        $data_text['reply_markup'] = $inline_keyboard;

        $send_text = Request::sendMessage($data_text);

        if ($act == 'menu' && $send_text->getResult() !== null) {
            $message_id = $send_text->getResult()->getMessageId();
            self::by_product_from_id($user_id, $product_id, $message_id);
        }

    }

    public static function by_product_from_id ($user_id, $product_id, $message_id) {

        $variants = Simpla_Variants::where('product_id', $product_id)->orderBy('position', 'desc')->get();
        $variant_id = null;
        $price = 0;
        foreach ($variants as $variant) {
            if ($variant['price'] >= $price) {
                $price = $variant['price'];
                $variant_id = $variant['id'];
            }
        }

        $variant = Simpla_Variants::find($variant_id);
        if ($variant['stock'] === null || $variant['stock'] > 0) {
            BotCartController::addToCart($user_id, $product_id, $variant_id, $message_id, 'menu');
        }


    }

    public static function get_foreach_product_from_id ($user_id, $cart_products, $text) {

        $currency = BotTextsController::getText($user_id, 'System', 'currency');

        foreach ($cart_products as $cart_product) {

//                $text .= '<pre>' . $cart_product['product_name'] . ' (' . BotCartController::getProductVariant($cart_product['id_size'])['name'] . ') ' . PHP_EOL . $cart_product['price'] . ' x ' . $cart_product['quantity'] . ' = ' . $cart_product['price_all'] . ' ' . $currency . '</pre>'.PHP_EOL.PHP_EOL;

            $text .= '<pre>' . BotCartController::replace_pizza_emoji($cart_product['category']) . $cart_product['product_name'] . ' (' . BotCartController::getProductVariant($cart_product['id_size'])['name'] . ') ' . PHP_EOL . $cart_product['price'] . ' × ' . $cart_product['quantity'] . ' = ' . $cart_product['price_all'] . ' ' . $currency . '</pre>' . PHP_EOL;
            $bortiks_in_cart = BotCartController::findBortiksInCartFromProductCardId($user_id, $cart_product['id']);
            foreach ($bortiks_in_cart as $bortik_in_cart) {
                $text .= '<pre>  + ' . BotCartController::replace_emoji($bortik_in_cart['product_name']).$bortik_in_cart['product_name'] . ' (' . BotCartController::getProductVariant($bortik_in_cart['id_size'])['name'] . ') ' . PHP_EOL . '  ' . $bortik_in_cart['price'] . ' × ' . $bortik_in_cart['quantity'] . ' = ' . $bortik_in_cart['price_all'] . ' ' . $currency . '</pre>' . PHP_EOL;
            }
            $text .= PHP_EOL;

        }

        return $text;
    }

//    public static function updateMessageAdd($user_id, $product_id, $variant_id, $message_id) {
//
//        $product = BotMenuController::get_product_sql($product_id);
//        $variant = BotCartController::getProductVariant($variant_id);
//
//        $ins_text = '';
//        $currency = BotTextsController::getText($user_id, 'System', 'currency');
//        if ($variant['name'] !== '') $ins_text .= $variant['name'] . ' / ' . $variant['price'] . ' ' . $currency;
//        elseif ($variant['sku'] !== '') $ins_text .= $variant['sku'] . ' / ' . $variant['price'] . ' ' . $currency;
//        else $ins_text .= $variant['price'] . ' ' . $currency;
//
//        $text = BotTextsController::getText($user_id, 'Menu', 'update_message_add');
//        $text .= ' <b>'.$product['name'].' ('.$ins_text.')</b>';
//
//        $inline_keyboard = self::get_buttons_buy($user_id, $product_id);
//        $inline_keyboard = self::get_other_buttons($user_id, $inline_keyboard);
//
//        $data_edit = ['chat_id' => $user_id];
//        $data_edit['reply_markup'] = $inline_keyboard;
//        $data_edit['text'] = $text;
//        $data_edit['parse_mode'] = 'html';
//        $data_edit['message_id'] = $message_id;
//        Request::editMessageText($data_edit);
//
//    }


}
