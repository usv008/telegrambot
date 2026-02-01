<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotCart;
use App\Models\BotMenu;
use App\Models\BotMenuOptions;
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

class BotMenuController extends Controller
{

    public static function getMenuMessageId($user_id)
    {

        return BotUsersNav::where('user_id', $user_id)->first()['menu_message_id'];

    }

    public static function updateMenuMessageId($user_id, $message_id)
    {

        $cart = BotUsersNav::updateOrCreate(
            ['user_id' => $user_id],
            ['menu_message_id' => $message_id]
        );

    }

    public static function getMenu()
    {

        return BotMenu::where('enabled', 1)->orderBy('menu_sort', 'asc')->get();

    }

    public static function getMenuOptions($user_id)
    {

        $lang = BotUserSettingsController::getLang($user_id);
        $text_lang = '_'.$lang;
        $region_id = BotUser::getValue($user_id, 'city_id');
        return Simpla_Options::join('s_products', 's_products.id', 's_options.product_id')
            ->join('s_features', 's_features.id', 's_options.feature_id')
            ->join('bot_menu_options', 'bot_menu_options.name', 's_options.value')
            ->join('s_products_regions', 's_products_regions.product_id', 's_products.id')
            ->where('s_products_regions.region_id', $region_id)
            ->where('s_products.visible', 1)
            ->where('s_features.in_filter', 1)
            ->where('bot_menu_options.enabled', 1)
            ->groupBy('s_options.value')
            ->distinct('s_options.value')
            ->get(['bot_menu_options.name'.$text_lang.' as value']);

    }

    public static function getMenuOptionsArr($user_id)
    {

        $lang = BotUserSettingsController::getLang($user_id);
        $text_lang = '_'.$lang;
        $region_id = BotUser::getValue($user_id, 'city_id');
        $options = Simpla_Options::join('s_products', 's_products.id', 's_options.product_id')
            ->join('s_features', 's_features.id', 's_options.feature_id')
            ->join('bot_menu_options', 'bot_menu_options.name', 's_options.value')
            ->join('s_products_regions', 's_products_regions.product_id', 's_products.id')
            ->where('s_products_regions.region_id', $region_id)
            ->where('s_products.visible', 1)
            ->where('s_features.in_filter', 1)
            ->where('bot_menu_options.enabled', 1)
            ->groupBy('s_options.value')
            ->distinct('s_options.value')
            ->get(['bot_menu_options.name'.$text_lang.' as value']);

        $arr = [];
        foreach ($options as $option) {
            $arr[] = $option->value;
        }

        return $arr;

    }

    public static function getImageFilename($product_id)
    {

        return Simpla_Images::where('product_id', $product_id)->where('position', 0)->first()['filename'];

    }

    public static function getThumbAddr($product_id)
    {

        $filename = self::getImageFilename($product_id);
        $ext = substr(strrchr($filename, '.'), 1);
        $img = env('APP_URL') . 'assets/img/thumb/' . env('TEXT_THUMB_IMAGE_INS') . '' . $product_id . '.' . $ext;

        return $img;

    }

    public static function get_menu_sql($user_id, $cat_id)
    {

        $lang = BotUserSettingsController::getLang($user_id);
        $region_id = BotUser::getValue($user_id, 'city_id');
        $text_lang = '_'.$lang;
        $products = Simpla_Categories::join('s_products_categories', 's_categories.id', 's_products_categories.category_id')
            ->join('s_products', function ($join) {
                $join->on('s_products.id', 's_products_categories.product_id')
                    ->where('s_products.visible', 1);
            })
            ->leftJoin('s_tabs', 's_products_categories.product_id', 's_tabs.product_id')
            ->join('s_images', 's_images.product_id', 's_products.id')
            ->join('s_products_regions', 's_products_regions.product_id', 's_products.id')
            ->where('s_products_regions.region_id', $region_id)
            ->where(function ($query) use ($cat_id) {
                $query->where('s_categories.id', $cat_id)
                    ->orWhere('s_categories.parent_id', $cat_id);
            })
            ->where('s_images.position', 0)
            ->groupBy('s_products.id')
            ->orderBy('s_products.position', 'desc')
            ->get(['s_products.id', 's_categories.id as cat_id', 's_products.name'.$text_lang.' as name', 's_products.featured', 's_products.position', 's_tabs.body'.$text_lang.' as description', 's_images.filename']);

        return $products;

    }

    public static function get_product_sql($user_id, $product_id)
    {

        $lang = BotUserSettingsController::getLang($user_id);
        $text_lang = '_'.$lang;
        $region_id = BotUser::getValue($user_id, 'city_id');
        $product = Simpla_Products::leftJoin('s_tabs', 's_products.id', 's_tabs.product_id')
            ->join('s_images', 's_images.product_id', 's_products.id')
            ->leftJoin('s_products_categories', 's_products_categories.product_id', 's_products.id')
            ->leftJoin('s_categories', 's_categories.id', 's_products_categories.category_id')
            ->join('s_products_regions', 's_products_regions.product_id', 's_products.id')
            ->where(function ($query) use ($product_id) {
                $query->where('s_products.id', $product_id);
//                    ->orWhere('s_categories.parent_id', $cat_id);
            })
            ->where('s_products_regions.region_id', $region_id)
            ->where('s_images.position', 0)
            ->first(['s_products.id', 's_products.name'.$text_lang.' as name', 's_products.featured', 's_products.position', 's_tabs.body'.$text_lang.' as description', 's_images.filename', 's_categories.url', 's_products.no_actions', 's_products.visible']);

        return $product;

    }

    public static function get_products_arr($user_id)
    {

        $products_arr = [];

        $menus = BotMenu::all();
        foreach ($menus as $menu) {

            $cat = Simpla_Categories::where('url', $menu['menu_key'])->first();
            if ($cat) {
                $cat_id = $cat->id;
                $products = BotMenuController::get_menu_sql($user_id, $cat_id);
                foreach ($products as $product) {

                    $id = $product['id'];
                    $products_arr[$id] = $product['name'];

                }
            }


        }

        return $products_arr;

    }

    public static function searchForInlineQuery($user_id, $text)
    {

        $cat_arr = [];

        $menus = BotMenu::all();
        foreach ($menus as $menu) {

            $id = Simpla_Categories::where('url', $menu['menu_key'])->first()['id'];

            $cat_arr[] = $id;

            $childs = Simpla_Categories::where('parent_id', $id)->get();
            foreach ($childs as $child) {
                $cat_arr[] = $child['id'];
            }

        }

        $lang = BotUserSettingsController::getLang($user_id);
        $text_lang = '_'.$lang;

        $region_id = BotUser::getValue($user_id, 'city_id');

        return Simpla_Products::leftJoin('s_tabs', 's_products.id', 's_tabs.product_id')
            ->join('s_images', 's_images.product_id', 's_products.id')
            ->join('s_products_categories', function($join) use ($cat_arr) {
                $join->on('s_products_categories.product_id', 's_products.id')
                    ->whereIn('s_products_categories.category_id', $cat_arr);
            })
            ->join('s_products_regions', 's_products_regions.product_id', 's_products.id')
//                ->leftJoin('s_categories', function($join) {
//                    $join->on('s_categories.id', 's_products_categories.category_id')
//                        ->orOn('s_categories.parent_id', 's_products_categories.category_id');
//                })
            ->where('s_products.name'.$text_lang, 'like', '%'.$text.'%')
            ->where('s_products_regions.region_id', $region_id)
            ->where('s_products.visible', 1)
            ->where('s_images.position', 0)
            ->orderBy('s_products.position', 'desc')
            ->get(['s_products.id', 's_products.name'.$text_lang.' as name', 's_products.featured', 's_products.position', 's_tabs.body'.$text_lang.' as description', 's_images.filename']);

    }

    public static function menuOptionsForInlineQuery($user_id, $text)
    {

        $lang = BotUserSettingsController::getLang($user_id);
        $text_lang = '_'.$lang;
        $region_id = BotUser::getValue($user_id, 'city_id');
        $text = BotMenuOptions::where('name'.$text_lang, $text)->first()['name'];
        return Simpla_Options::join('s_products', 's_products.id', 's_options.product_id')
            ->join('s_features', 's_features.id', 's_options.feature_id')
            ->leftJoin('s_tabs', 's_products.id', 's_tabs.product_id')
            ->join('s_images', 's_images.product_id', 's_products.id')
            ->join('s_products_regions', 's_products_regions.product_id', 's_products.id')
            ->where('s_products.visible', 1)
            ->where('s_features.in_filter', 1)
            ->where('s_images.position', 0)
            ->where('s_options.value', $text)
            ->where('s_products_regions.region_id', $region_id)
            ->orderBy('s_products.position', 'desc')
            ->get(['s_products.id', 's_products.name'.$text_lang.' as name', 's_products.featured', 's_products.position', 's_tabs.body'.$text_lang.' as description', 's_images.filename']);

    }

}
