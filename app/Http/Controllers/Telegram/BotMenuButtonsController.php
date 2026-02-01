<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotCart;
use App\Models\BotMenu;
use App\Models\BotSettings;
use App\Models\BotUser;
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

class BotMenuButtonsController extends Controller
{

    public static function get_menu_buttons($user_id, $region_id) {

        $inline_keyboard = new InlineKeyboard([]);

//        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Menu', 'pizza_all');
//        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'switch_inline_query_current_chat' => '']) );

        $lang = BotUserSettingsController::getLang($user_id);
        $menu_lang = 'name_' . $lang;
        $menu_data = 'menu_data_' . $lang;

        if ($region_id !== null && (int)$region_id > 0) {

            list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Menu', 'pizza_filter');
            $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]) );

//            $menus = BotMenuController::getMenu();
//            $i = 0;
//            foreach ($menus as $menu) {
//                if ($menu['menu_key'] !== 'pizza') {
//                    if ($menu['groups'] == 0) {
//                        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $menu[$menu_lang], 'switch_inline_query_current_chat' => $menu[$menu_data]]));
//                    }
//                    else {
//                        $i++;
//                        $ins[$i] = new InlineKeyboardButton(['text' => $menu[$menu_lang], 'switch_inline_query_current_chat' => $menu[$menu_data]]);
//                        if ($i == 2) {
//                            $inline_keyboard->addRow($ins[1], $ins[2]);
//                            $i = 0;
//                        }
//                    }
//                }
//            }
//            if ($i == 1) $inline_keyboard->addRow($ins[1]);

            $buttons = Simpla_Products::join('s_products_regions', 's_products_regions.product_id', 's_products.id')
                ->join('s_products_categories', 's_products_categories.product_id', 's_products.id')
                ->join('s_categories', 's_categories.id', 's_products_categories.category_id')
                ->where('s_products.visible', 1)
                ->where('s_products_regions.region_id', $region_id)
                ->where('s_categories.visible', 1)
                ->whereIn('s_categories.parent_id', [0, 57])
                ->where('s_categories.'.$menu_lang, '!=', null)
                ->groupBy('s_categories.'.$menu_lang)
                ->orderBy('s_categories.position', 'asc')
                ->get(['s_categories.'.$menu_lang, 's_categories.'.$menu_data, 's_categories.url', 's_categories.menu_groups']);

            $i = 0;
            $ins = [];
            foreach ($buttons as $button) {
                if ($button->url !== 'pizza') {
                    if ($button->menu_groups == 0) {
                        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $button->$menu_lang, 'switch_inline_query_current_chat' => $button->$menu_data]));
                    }
                    else {
                        $i++;
                        $ins[$i] = new InlineKeyboardButton(['text' => $button->$menu_lang, 'switch_inline_query_current_chat' => $button->$menu_data]);
                        if ($i == 2) {
                            $inline_keyboard->addRow($ins[1], $ins[2]);
                            $i = 0;
                        }
                    }
                }
            }
            if ($i == 1) $inline_keyboard->addRow($ins[1]);

//            list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'City', 'change_city');
//            $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]) );
        }
//        else {
//            list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'City', 'change_city');
//            $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]) );
//        }

        return $inline_keyboard;

    }

    public static function get_menu_filter_buttons($user_id) {

        $options = BotMenuController::getMenuOptions($user_id);
        $inline_keyboard = new InlineKeyboard([]);

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Menu', 'pizza_all');
        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'switch_inline_query_current_chat' => '']) );

        $i = 0;
        foreach ($options as $option) {

            $i++;
            if ($i == 1) {
                $ins1 = new InlineKeyboardButton(['text' => '🍕 '.$option['value'], 'switch_inline_query_current_chat' => $option['value']]);
            }
            if ($i == 2) {
                $ins2 = new InlineKeyboardButton(['text' => '🍕 '.$option['value'], 'switch_inline_query_current_chat' => $option['value']]);
                $inline_keyboard->addRow($ins1, $ins2);
                $i = 0;
            }

        }
        if ($i == 1) $inline_keyboard->addRow($ins1);

        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Menu', 'pizza_filter_back');
        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]) );

        return $inline_keyboard;

    }

    public static function get_buttons_buy($user_id, $product_id) {

        $inline_keyboard = new InlineKeyboard([]);
        $variants = Simpla_Variants::where('product_id', $product_id)->orderBy('position', 'desc')->get();

        foreach ($variants as $variant) {

            $ins_text = '';
            $name = '';
            $price = '';
            $currency = BotTextsController::getText($user_id, 'System', 'currency');
            if ($variant['name'] !== '') { $name = $variant['name']; $price = $variant['price'] . ' ' . $currency; }
            elseif ($variant['sku'] !== '') { $name = $variant['sku']; $price = $variant['price'] . ' ' . $currency; }
            else { $price = $variant['price'] . ' ' . $currency; }

            $in_cart = BotCartController::findProductInCart($user_id, $product_id, $variant['id']);

            $pcs = BotTextsController::getText($user_id, 'System', 'pcs');
            $buy = BotTextsController::getText($user_id, 'Menu', 'buy');

            list($text_plus, $data_plus) = BotButtonsInlineController::getButtonInline($user_id, 'Menu', 'edit_product_plus');
            list($text_minus, $data_minus) = BotButtonsInlineController::getButtonInline($user_id, 'Menu', 'edit_product_minus');

            if ($variant['stock'] === null || $variant['stock'] > 0) {

                if ($in_cart == null) {
//                $ins_text .= $name !== '' ? $name . ' / ' . $price : $price;
//                $inline_keyboard->addRow(
//                    new InlineKeyboardButton(['text' => $buy.' '.$ins_text . '', 'callback_data' => $data_plus.$user_id.'___'.$product_id.'___'.$variant['id'].'___menu'])
//                );
                    $ins_text .= $name !== '' ? $name . ' (0' . $pcs . ')' : '(0' . $pcs . ')';
                    $inline_keyboard->addRow(
                        new InlineKeyboardButton(['text' => $text_minus . '', 'callback_data' => $data_minus.$user_id.'___'.$product_id.'___'.$variant['id'].'___menu']),
                        new InlineKeyboardButton(['text' => $ins_text . '', 'callback_data' => $data_plus.$user_id.'___'.$product_id.'___'.$variant['id'].'___menu']),
                        new InlineKeyboardButton(['text' => $text_plus . '', 'callback_data' => $data_plus.$user_id.'___'.$product_id.'___'.$variant['id'].'___menu'])
                    );
                }
                else {
                    $ins_text .= $name !== '' ? $name . ' (' . $in_cart['quantity'] . $pcs . ')' : '(' . $in_cart['quantity'] . $pcs . ')';
                    $inline_keyboard->addRow(
                        new InlineKeyboardButton(['text' => $text_minus . '', 'callback_data' => $data_minus.$user_id.'___'.$product_id.'___'.$variant['id'].'___menu']),
                        new InlineKeyboardButton(['text' => $ins_text . '', 'callback_data' => $data_plus.$user_id.'___'.$product_id.'___'.$variant['id'].'___menu']),
                        new InlineKeyboardButton(['text' => $text_plus . '', 'callback_data' => $data_plus.$user_id.'___'.$product_id.'___'.$variant['id'].'___menu'])
                    );
                }

            }
            else {
                $not_available = BotTextsController::getText($user_id, 'Menu', 'not_available');
                $inline_keyboard->addRow(
                    new InlineKeyboardButton(['text' => $not_available, 'callback_data' => 'nonono'.time()])
                );
            }


        }

        $no_bortiks = BotCartController::getArrNoBortiks();
        if (!in_array($product_id, $no_bortiks) && BotCartController::checkProductIdInCart($user_id, $product_id) > 0 && BotCartController::getComplectProductCount($product_id) > 0) {
            list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Cart', 'product_add_ingredient');
            $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data.$product_id]));
        }

        return $inline_keyboard;
    }

    public static function get_buttons_edit_buy($user_id, $product_id, $variant_id) {

        $inline_keyboard = new InlineKeyboard([]);
        $variant = Simpla_Variants::where('id', $variant_id)->first();

        $ins_text = '';
        $currency = BotTextsController::getText($user_id, 'System', 'currency');
        if ($variant['name'] !== '') $ins_text .= $variant['name'] . ' / ' . $variant['price'] . ' ' . $currency;
        elseif ($variant['sku'] !== '') $ins_text .= $variant['sku'] . ' / ' . $variant['price'] . ' ' . $currency;
        else $ins_text .= $variant['price'] . ' ' . $currency;

        $in_cart = BotCartController::findProductInCart($user_id, $product_id, $variant_id);

        $pcs = BotTextsController::getText($user_id, 'System', 'pcs');
        $buy = BotTextsController::getText($user_id, 'Menu', 'buy');
        $ins_text = ''.$ins_text.'';

        $ins_pizza = $in_cart !== null ? BotCartController::replace_pizza_emoji($in_cart['category']) : '';

        list($text_plus, $data_plus) = BotButtonsInlineController::getButtonInline($user_id, 'Menu', 'edit_product_plus');
        list($text_minus, $data_minus) = BotButtonsInlineController::getButtonInline($user_id, 'Menu', 'edit_product_minus');

        if ($in_cart == null) {
            $inline_keyboard->addRow(
                new InlineKeyboardButton(['text' => $buy.' '.$ins_text . '', 'callback_data' => $data_plus.$user_id.'___'.$product_id.'___'.$variant['id'].'___cart'])
            );
        }
        else {
            $inline_keyboard->addRow(
                $in_cart !== null ? new InlineKeyboardButton(['text' => $text_minus . '', 'callback_data' => $data_minus.$user_id.'___'.$product_id.'___'.$variant['id'].'___cart']) : new InlineKeyboardButton(['text' => ' ', 'callback_data' => 'no']),
                new InlineKeyboardButton(['text' => $ins_pizza.$ins_text . '', 'callback_data' => $data_plus.$user_id.'___'.$product_id.'___'.$variant['id'].'___cart']),
                new InlineKeyboardButton(['text' => $text_plus . '', 'callback_data' => $data_plus.$user_id.'___'.$product_id.'___'.$variant['id'].'___cart'])
            );
        }

        $no_bortiks = BotCartController::getArrNoBortiks();
        if (!in_array($product_id, $no_bortiks) && BotCartController::checkProductAndVariantIdInCart($user_id, $product_id, $variant_id) > 0 && BotCartController::getComplectProductCount($product_id) > 0) {
            $ins = $in_cart !== null && BotCartController::countBortiksFromCartId($user_id, $in_cart['id']) > 0 ? 'product_cart_edit_ingredient' : 'product_cart_add_ingredient';
            list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Cart', $ins);
            $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data.$product_id.'___'.$variant_id.'___'.'cart']));
        }

        return $inline_keyboard;
    }

    public static function get_other_buttons($user_id, $inline_keyboard) {

        list($text_menu, $data_menu) = BotButtonsInlineController::getButtonInline($user_id, 'Menu', 'menu');
        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text_menu, 'callback_data' => $data_menu]) );
        list($text_cart, $data_cart) = BotButtonsInlineController::getButtonInline($user_id, 'Cart', 'cart');
        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text_cart, 'callback_data' => $data_cart]) );

        return $inline_keyboard;

    }

    public static function get_buttons_bortik($user_id, $product_id, $variant_id, $act) {

        $inline_keyboard = new InlineKeyboard([]);

        $bortiks = BotCartController::getDistinctBortiks($user_id);
        foreach ($bortiks as $bortik) {
            $inline_keyboard->addRow(new InlineKeyboardButton(['text' => BotCartController::replace_emoji($bortik['name']).$bortik['name'], 'callback_data' => 'select_bortik___'.$product_id.'___'.$variant_id.'___'.$bortik['id'].'___'.$act]));
        }

        $ins = $act == 'cart' ? 'edit_cart_back' : 'edit_back';
        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Menu', $ins);
        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'callback_data' => $data.$product_id.'___'.$variant_id.'___'.$act]) );

        return $inline_keyboard;

    }

    public static function get_buttons_select_bortik($user_id, $product_id, $variant_id, $bortik_product_id, $act) {

        $currency = BotTextsController::getText($user_id, 'System', 'currency');
        list($text_plus, $data_plus) = BotButtonsInlineController::getButtonInline($user_id, 'Menu', 'edit_product_plus');
        list($text_minus, $data_minus) = BotButtonsInlineController::getButtonInline($user_id, 'Menu', 'edit_product_minus');

        $inline_keyboard = new InlineKeyboard([]);

        $bortiks = BotCartController::getBortiksFromProductId($user_id, $bortik_product_id);
        foreach ($bortiks as $bortik) {

            if ($act == 'cart') {
                $ckeck_products = BotCartController::checkProductInCartFromProductAndVariantIdLikeVariant($user_id, $product_id, $variant_id, $bortik['variant_name']);
            }
            else $ckeck_products = BotCartController::checkProductInCartFromProductIdLikeVariant($user_id, $product_id, $bortik['variant_name']);


            if ($ckeck_products > 0) {

                $product_in_cart = BotCartController::getProductInCartFromProductIdLikeVariant($user_id, $product_id, $bortik['variant_name']);
                $bortik_in_cart = BotCartController::findBortikInCart($user_id, $product_in_cart['id'], $bortik['id'], $bortik['variant_id']);
                $bortik_num = 0;
                $bs_in_cart = BotCartController::findBortiksInCartFromProductCardId($user_id, $product_in_cart['id']);
                foreach ($bs_in_cart as $b_in_cart) {
                    $bortik_num += $b_in_cart['quantity'];
                }
                $data_add = 'add_bortik___'.$product_in_cart['id'].'___'.$bortik['id'].'___'.$bortik['variant_id'].'___'.$act;
                $data_remove = 'remove_bortik___'.$product_in_cart['id'].'___'.$bortik['id'].'___'.$bortik['variant_id'].'___'.$act;
                $inline_keyboard->addRow(
                    $bortik_in_cart !== null ? new InlineKeyboardButton(['text' => $text_minus . '', 'callback_data' => $data_remove]) : new InlineKeyboardButton(['text' => ' ', 'callback_data' => 'no']),
                    $bortik_num < $product_in_cart['quantity'] ? new InlineKeyboardButton(['text' => $bortik['variant_name'].' / '.$bortik['price'].' '.$currency, 'callback_data' => $data_add]) : new InlineKeyboardButton(['text' => $bortik['variant_name'].' / '.$bortik['price'].' '.$currency, 'callback_data' => 'no']),
                    $bortik_num < $product_in_cart['quantity'] ? new InlineKeyboardButton(['text' => $text_plus . '', 'callback_data' => $data_add]) : new InlineKeyboardButton(['text' => ' ', 'callback_data' => 'no'])
                );

            }

        }

        $ins = $act == 'cart' ? 'edit_cart_back' : 'edit_back';
        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Menu', $ins);
        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text, 'callback_data' => $data.$product_id.'___'.$variant_id.'___'.$act]) );

        $inline_keyboard = self::get_other_buttons($user_id, $inline_keyboard);

        return $inline_keyboard;

    }


}
