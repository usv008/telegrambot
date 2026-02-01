<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotCart;
use App\Models\BotSettings;
use App\Models\BotUsersNav;
use App\Models\Simpla_Variants;
use Illuminate\Http\Request as LRequest;
use Longman\TelegramBot\Request;
use App\Http\Controllers\Controller;
use App\Models\BotSettingsButtons;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use PHPUnit\ExampleExtension\Comparable;

class BotCartButtonsController extends Controller
{

    public static function get_buttons_product_delete($user_id, $id) {

        $command = 'Cart';

        $inline_keyboard = new InlineKeyboard([]);

        list($yes_text, $yes_data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'product_ask_delete_yes');
        list($no_text, $no_data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'product_ask_delete_no');

        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $yes_text, 'callback_data' => $yes_data.$id]) );
        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $no_text, 'callback_data' => $no_data.$id]) );

        return $inline_keyboard;

    }

    public static function get_cart_button_edit($user_id)
    {

        $command = 'Cart';

        $inline_keyboard = new InlineKeyboard([]);
        list($edit_text, $edit_data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'edit');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $edit_text . '', 'callback_data' => $edit_data]));

        return $inline_keyboard;

    }

    public static function get_button_edit($user_id, $id)
    {

        $command = 'Cart';

        $inline_keyboard = new InlineKeyboard([]);
        list($edit_text, $edit_data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'product_edit');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $edit_text . '', 'callback_data' => $edit_data.$id]));

        return $inline_keyboard;

    }

    public static function get_button_cart_product_edit($user_id, $product_id, $variant_id)
    {

        $command = 'Cart';

        $inline_keyboard = new InlineKeyboard([]);
        $variant = BotCartController::getProductVariant($variant_id);

        $ins_text = '';
        $currency = BotTextsController::getText($user_id, 'System', 'currency');
        if ($variant['name'] !== '') $ins_text .= $variant['name'] . ' / ' . $variant['price'] . ' ' . $currency;
        elseif ($variant['sku'] !== '') $ins_text .= $variant['sku'] . ' / ' . $variant['price'] . ' ' . $currency;
        else $ins_text .= $variant['price'] . ' ' . $currency;

        list($text_plus, $data_plus) = BotButtonsInlineController::getButtonInline($user_id, 'Cart', 'edit_cart_product_plus');
        list($text_minus, $data_minus) = BotButtonsInlineController::getButtonInline($user_id, 'Cart', 'edit_cart_product_minus');
        $inline_keyboard->addRow(
            new InlineKeyboardButton(['text' => $text_minus . '', 'callback_data' => $data_minus.$user_id.'___'.$product_id.'___'.$variant['id']]),
//            new InlineKeyboardButton(['text' => $ins_text . '', 'callback_data' => 'no'.$variant['id']]),
            new InlineKeyboardButton(['text' => $text_plus . '', 'callback_data' => $data_plus.$user_id.'___'.$product_id.'___'.$variant['id']])
        );

        if (BotCartController::checkProductIdInCart($user_id, $product_id) > 0 && BotCartController::getComplectProductCount($product_id) > 0) {

            list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Cart', 'product_add_ingredient');
            $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));
//            $inline_keyboard = self::get_buttons_complect_product($user_id, $product_id, $inline_keyboard);

        }

        list($back_text, $back_data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'edit_back0');
        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $back_text, 'callback_data' => $back_data]) );

        return $inline_keyboard;

    }

    public static function get_buttons_edit($user_id)
    {

        $command = 'Cart';

        $products_num = BotCartController::countProductsInCart($user_id);

        $inline_keyboard = new InlineKeyboard([]);

        if ($products_num > 0) {

            $products = BotCartController::getProductsInCart($user_id);
            $i = 0;
            $k = 0;
            $ins = [];
            foreach ($products as $product) {

                $i++;
                $k++;
//                $ins[$k] = new InlineKeyboardButton(['text' => $i, 'callback_data' => 'edit_cart_product___'.$product['id']]);
                $ins[$k] = new InlineKeyboardButton(['text' => BotCartController::replace_digit($i), 'callback_data' => 'edit_cart_product___'.$product['id']]);
                if ($k == 4) {
                    $inline_keyboard->addRow($ins[1], $ins[2], $ins[3], $ins[4]);
                    $k = 0;
                }

            }
            if ($k == 3) $inline_keyboard->addRow($ins[1], $ins[2], $ins[3]);
            if ($k == 2) $inline_keyboard->addRow($ins[1], $ins[2]);
            if ($k == 1) $inline_keyboard->addRow($ins[1]);

        }

//        list($text_menu, $data_menu) = BotButtonsInlineController::getButtonInline($user_id, 'Menu', 'menu');
//        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text_menu, 'callback_data' => $data_menu]) );

        list($back_text, $back_data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'edit_back0');
        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $back_text, 'callback_data' => $back_data]) );

        return $inline_keyboard;

    }

    public static function get_buttons_product_edit($user_id, $id, $inline_keyboard) {

        $command = 'Cart';

        list($plus_text, $plus_data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'edit_product_plus');
        list($minus_text, $minus_data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'edit_product_minus');
        list($delete_text, $delete_data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'edit_product_delete');

        $inline_keyboard->addRow(
            new InlineKeyboardButton(['text' => $plus_text, 'callback_data' => $plus_data.$id]),
            new InlineKeyboardButton(['text' => $minus_text, 'callback_data' => $minus_data.$id]),
            new InlineKeyboardButton(['text' => $delete_text, 'callback_data' => $delete_data.$id])
        );

        $buttons = BotCartController::getComplectProduct($user_id, $id);
        foreach ($buttons as $button) {
            $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $button['name'], 'callback_data' => 'test']) );
        }

        list($back_text, $back_data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'product_back');
        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $back_text, 'callback_data' => $back_data.$id]) );

        return $inline_keyboard;

    }

    public static function get_buttons_delivery_edit($user_id) {

        $lang = BotUserSettingsController::getLang($user_id);
        $text_lang = 'text_value_'.$lang;

        $inline_keyboard = new InlineKeyboard([]);

        $buttons = BotCartController::get_delivery_type($user_id);
        foreach ($buttons as $button) {
            $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $button[$text_lang], 'callback_data' => 'change_delivery_type___'.$button['id']]) );
        }

        return $inline_keyboard;

    }

    public static function get_buttons_cancel_order($user_id) {

        $inline_keyboard = new InlineKeyboard([]);

        list($text_menu, $data_menu) = BotButtonsInlineController::getButtonInline($user_id, 'Cart', 'cancel_order_yes');
        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text_menu, 'callback_data' => $data_menu]) );

        list($text_menu, $data_menu) = BotButtonsInlineController::getButtonInline($user_id, 'Cart', 'cancel_order_no');
        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text_menu, 'callback_data' => $data_menu]) );

        return $inline_keyboard;

    }

    public static function get_buttons_cart_total($user_id) {

        $command = 'Cart';

        $min_sum_order = BotSettingsController::getSettings($user_id,'min_sum_order')['settings_value'];
        $total_without_delivery = BotCartController::count_sum_total_without_delivery($user_id);

        $inline_keyboard = new InlineKeyboard([]);

        list($text_menu, $data_menu) = BotButtonsInlineController::getButtonInline($user_id, 'Menu', 'menu');
        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text_menu, 'callback_data' => $data_menu]) );

        list($edit_text, $edit_data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'edit');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $edit_text . '', 'callback_data' => $edit_data]));

        if (BotRaffleController::checkUserActionPizzaInCart($user_id) == 0) {

            list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'change_delivery');
            $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]) );

//        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'discount');
//        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]) );

            $delivery_discount = BotUsersNavController::get_delivery_from_user_id($user_id)['discount'];
//            if ((BotCashbackController::getUserCashback($user_id) > 0 && ($total_without_delivery >= $min_sum_order || $delivery_discount == 1)) || ($total_without_delivery >= 350 && BotCashbackController::getUserCashbackAction($user_id) > 0)) {
//
//                list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'cashback');
//                $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]) );
//
//            }

            list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'birthday');
            $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]) );

            list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'cancel_order');
            $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]) );

            if ($total_without_delivery >= $min_sum_order || $delivery_discount == 1) {

                list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'order');
                $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]) );

            }

        }
        else {

            if ($total_without_delivery >= 199) {

                list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'change_delivery');
                $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]) );

                $delivery_discount = BotUsersNavController::get_delivery_from_user_id($user_id)['discount'];
//                if (BotCashbackController::getUserCashback($user_id) > 0 && ($total_without_delivery >= $min_sum_order || $delivery_discount == 1)) {
//
//                    list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'cashback');
//                    $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]) );
//
//                }

                list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'birthday');
                $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]) );

                list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'cancel_order');
                $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]) );

                if ($total_without_delivery >= $min_sum_order || $delivery_discount == 1) {

                    list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'order');
                    $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]) );

                }

            }
            else {
                list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'goto_start');
                $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));
            }

        }

        return $inline_keyboard;

    }

    public static function add_button_menu($user_id, $inline_keyboard) {

        list($text_menu, $data_menu) = BotButtonsInlineController::getButtonInline($user_id, 'Menu', 'menu');
        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text_menu, 'callback_data' => $data_menu]) );

        return $inline_keyboard;
    }

    public static function order_repeat_buttons($user_id, $order_id) {

        $inline_keyboard = new InlineKeyboard([]);

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'MyOrder', 'repeat_order');
        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'callback_data' => $data.$order_id]) );

        return $inline_keyboard;
    }

}
