<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Api\ApiPrestaShopController;
use App\Http\Controllers\CashBackController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\LiqPayController;
use App\Http\Controllers\PrestaShopWebserviceController;
use App\Http\Controllers\PrestaShopWebserviceExceptionController;
use App\Http\Controllers\SimplaOrdersController;
use App\Http\Controllers\TelegramBotCherry\TelegramBotCherryController;
use App\Http\Controllers\WayForPayController;
use App\Models\BotCart;
use App\Models\BotCartNew;
use App\Models\BotCashbackHistory;
use App\Models\BotMenu;
use App\Models\BotOrder;
use App\Models\BotOrderContent;
use App\Models\BotOrderOld;
use App\Models\BotOrders;
use App\Models\BotOrdersNew;
use App\Models\BotRaffleCherryTakeaway;
use App\Models\BotRaffleUsers;
use App\Models\BotSettings;
use App\Models\BotSettingsCashback;
use App\Models\BotUser;
use App\Models\PrestaShop_Accessory_Group_Product;
use App\Models\PrestaShop_Addresses;
use App\Models\PrestaShop_Cart_Cart_Rule;
use App\Models\PrestaShop_Cart_Rule;
use App\Models\PrestaShop_Cart_Rule_Lang;
use App\Models\PrestaShop_Cart_Rule_Product_Rule_Group;
use App\Models\PrestaShop_Cashback_Categories;
use App\Models\PrestaShop_Category;
use App\Models\PrestaShop_Category_Product;
use App\Models\PrestaShop_Category_Products;
use App\Models\PrestaShop_Feature_Product;
use App\Models\PrestaShop_Lang;
use App\Models\PrestaShop_Module_Currency;
use App\Models\PrestaShop_Order_History;
use App\Models\PrestaShop_Orders;
use App\Models\PrestaShop_Product;
use App\Models\PrestaShop_Product_Attribute;
use App\Models\PrestaShop_Product_Attribute_Combination;
use App\Models\PrestaShop_Product_Attribute_Shop;
use App\Models\PrestaShop_Specific_Price;
use App\Models\PrestaShop_WD_MegaMenu;
use App\Models\SimplaOrders;
use App\Models\SimplaStreetList;
use App\Services\WorkingHoursService;
use http\Env\Request;
use Illuminate\Http\Request as LRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use mysql_xdevapi\Warning;
use PDO;

class BotMenuNewController extends Controller
{

    public static int $language = 2;

    public static function showMenu(LRequest $request)
    {
        $input = $request->except('_token');
        $category_select = isset($input['category_select']) && $input['category_select'] !== null ? $input['category_select'] : 3;

        $whStatus = WorkingHoursService::isCurrentlyOpen();
        $whMessage = WorkingHoursService::getClosedMessage();

        $data = [
            'category_select' => $category_select,
            'wh_is_open' => $whStatus['is_open'],
            'wh_message' => $whMessage,
            'wh_next_open' => $whStatus['next_open'],
        ];
        return view('telegram.menu', $data);
    }

    public static function countProductsInCartByUserId(LRequest $request)
    {
        $input = $request->except('_token');
        if (!isset($input['user_id']) || $input['user_id'] == null || $input['user_id'] < 0)
            return null;
        $products = BotCartNew::getProductsByUserId($input['user_id']);
        return ['products_count' => $products->count(), 'products_sum' => $products->sum('price_all')];
    }

    public static function getMenus(LRequest $request)
    {
        $input = $request->except('_token');
        $category_select = isset($input['category_select']) && $input['category_select'] !== null ? $input['category_select'] : 3;
        $menus = BotMenu::where('enabled', 1)->orderBy('menu_sort', 'asc')->get();

        $megamenus = PrestaShop_WD_MegaMenu::join('ps_wdmegamenu_lang', 'ps_wdmegamenu_lang.id_wdmegamenu', 'ps_wdmegamenu.id_wdmegamenu')
            ->where('ps_wdmegamenu.active', 1)
            ->where('ps_wdmegamenu_lang.id_lang', 2)
            ->get();
        $menu_active = [];
        foreach ($megamenus as $megamenu) {
            $category_from_title = str_replace("CAT", "", $megamenu->title);
            if (is_int((int)$category_from_title) && (int)$category_from_title > 0) {
                $menu_active[] = $category_from_title;
            }
        }
        $menus = $menus->whereIn('category_id', $menu_active);
        $data = [
            'category_select' => $category_select,
            'menus' => $menus,
        ];
        return view('telegram.categories', $data);
    }

    public static function manipulationWithProducts($category_select, $products, $item_associations, $product_features, $product_combinations, $product_options)
    {
        foreach ($item_associations as $item2) {
            $products_arr[] = $item2->id;
        }
        $item_products = ApiPrestaShopController::sendResponse(['resource' => 'products', 'url' => 'products', 'category_id' => $category_select, 'filter_array' => $products_arr]);
        $item_products = $item_products->where('active', 1)->sortBy('position_in_category')->toArray();
        foreach ($item_products as $item_product) {
            $item_product->product_feature = '';
            if (isset($item_product->associations)) {
                $associations = $item_product->associations;
                if (isset($associations->product_features)) {
                    $product_feature_id = collect($associations->product_features)->first()->id_feature_value;
                    $item_product->product_feature = $product_features->where('id', $product_feature_id)->first()->value;
                }
                if (isset($associations->combinations)) {
                    $item_product = self::manipulationWithCombinations($item_product, $associations, $product_combinations, $product_options);
                }
            }
            $products->push($item_product);
        }
        return $products;
    }

    private static function manipulationWithCombinations($item_product, $associations, $product_combinations, $product_options)
    {
        $combinations = [];
        foreach ($associations->combinations as $item_combination) {
            $combination = $product_combinations->where('id', $item_combination->id)->first();
            if (isset($combination->associations)) {
                if (isset($combination->associations->product_option_values)) {
                    $combination_options = [];
                    foreach ($combination->associations->product_option_values as $item_option) {
                        $combination_options[$item_option->id] = $product_options->where('id', $item_option->id)->first();
                    }
                    $combination->options = collect($combination_options)->sortBy('position');
                }
            }
            $combinations[] = $combination;
        }
        $item_product->product_combinations = collect($combinations)->sortBy('position');
        return $item_product;
    }



    public static function getProducts(LRequest $request)
    {
        $input = $request->except('_token');
        $abra_kadabra = BotSettingsController::getSettings(null, 'abra_kadabra_for_thumbs_all')['settings_value'];
        $category_select = isset($input['category_select']) && $input['category_select'] !== null ? $input['category_select'] : 3;

        $categories = PrestaShop_Category::getCategoriesAll();
        $categories_child = $categories->where('id_parent', $category_select)->sortBy('position');
        $categories_array = [];
        foreach ($categories_child as $category) {
            array_push($categories_array, $category->id_category);
        }

        $products = PrestaShop_Product::getProductsByCategoryId($category_select, $categories_array);
        $product_features = PrestaShop_Feature_Product::getFeatures();
        $product_attributes = PrestaShop_Product_Attribute::getAttributesAll();
        foreach ($products as $product) {
            $product->product_features = $product_features->where('id_product', $product->id_product)->where('id_feature', 1)->first();
            $product->product_attributes = $product_attributes->where('id_product', $product->id_product)->sortBy('price');
            $product_specific_price = PrestaShop_Specific_Price::getPriceByProductId($product->id_product);
            foreach ($product->product_attributes as $product_attribute) {
                $product_attribute->price_new = $product_attribute->price;
                if ($product_specific_price->count() > 0) {
                    if ($product_specific_price->where('id_product_attribute', $product_attribute->id)->count() > 0) {
                        if ($product_specific_price->first()->reduction_type == 'amount') {
                            $product_attribute->price_new = bcsub($product_attribute->price, $product_specific_price->first()->reduction, 2);
                        }
                        elseif ($product_specific_price->first()->reduction_type == 'percentage') {
                            $discount = bcmul($product_attribute->price, $product_specific_price->first()->reduction, 10);
                            $product_attribute->price_new = bcsub($product_attribute->price, $discount, 2);
                        }
                    }
                }
            }
        }
//        $products_categories = $products->unique('category_name')->sortBy('category_name');
        $products_categories = $products->unique('category_name');
        $products_new = collect();
        foreach ($products_categories as $product_category) {
            $products_temp = $products->where('id_category', $product_category->id_category);
            foreach ($products_temp as $product) {
                $product_accessories = PrestaShop_Accessory_Group_Product::getAccessoriesByProductId($product->id_product);
                foreach ($product_accessories as $item) {
                    $item->product = $products->where('id_product', $item->id_accessory)->first();
                    $item->product_attributes = $product_attributes->where('id_product', $item->id_accessory)->first();
                }
                $categories_unique = $product_accessories->unique('id_accessory_group')->sortBy('category_name');
                $product->ingredients = $categories_unique;
                $quantity = 0;
                foreach ($product->product_attributes as $attribute) {
                    $quantity += $attribute->quantity;
                }
                if ($quantity > 0) $products_new->push($product);
            }
        }
        $products = $products_new;

        $data = [
            'category_select' => $category_select,
            'products' => $products,
            'abra_kadabra' => $abra_kadabra,
        ];
        return view('telegram.products', $data);
    }

    public static function getProduct(LRequest $request)
    {
        $input = $request->except('_token');
        if (!isset($input['id']) || $input['id'] == null && !(int)$input['id'])
            return null;

        $abra_kadabra = BotSettingsController::getSettings(null, 'abra_kadabra_for_thumbs_all')['settings_value'];

        $products = PrestaShop_Product::getProductsAll();
        $product = $products->where('id_product', $input['id'])->first();
        $product_features = PrestaShop_Feature_Product::getFeatureByProductId($input['id']);
        $product_attributes = PrestaShop_Product_Attribute::getAttributesAll();
        $product->product_attributes = $product_attributes->where('id_product', $input['id']);
        $product->product_features = $product_features->where('id_feature', 1)->first();
        $product_accessories = PrestaShop_Accessory_Group_Product::getAccessoriesByProductId($input['id']);
        $product_specific_price = PrestaShop_Specific_Price::getPriceByProductId($input['id']);
        foreach ($product->product_attributes as $product_attribute) {
            $product_attribute->price_new = $product_attribute->price;
            if ($product_specific_price->count() > 0) {
                if ($product_specific_price->where('id_product_attribute', $product_attribute->id)->count() > 0) {
                    if ($product_specific_price->first()->reduction_type == 'amount') {
                        $product_attribute->price_new = bcsub($product_attribute->price, $product_specific_price->first()->reduction, 2);
                    }
                    elseif ($product_specific_price->first()->reduction_type == 'percentage') {
                        $discount = bcmul($product_attribute->price, $product_specific_price->first()->reduction, 10);
                        $product_attribute->price_new = bcsub($product_attribute->price, $discount, 2);
                    }
                }
            }
        }

        foreach ($product_accessories as $item) {
            $item->product = $products->where('id_product', $item->id_accessory)->first();
            $item->product_attributes = $product_attributes->where('id_product', $item->id_accessory)->first();
        }
        $categories_unique = $product_accessories->unique('id_accessory_group')->sortBy('category_name');
        $categories = collect();
        foreach ($categories_unique as $category) {
            $category->products = $product_accessories->where('id_accessory_group', $category->id_accessory_group);
            $categories->push($category);
        }

        $data = [
            'id' => $input['id'],
            'product' => $product,
            'product_specific_price' => $product_specific_price,
            'categories' => $categories,
            'abra_kadabra' => $abra_kadabra,
        ];

        return view('telegram.product', $data);
    }

    public static function addProductToCart(LRequest $request)
    {
        // Working hours check
        $whStatus = WorkingHoursService::isCurrentlyOpen();
        if (!$whStatus['is_open'] && WorkingHoursService::getSetting('allow_future_orders', '1') != '1') {
            return response()->json([
                'working_hours_closed' => true,
                'message' => WorkingHoursService::getClosedMessage(),
            ]);
        }

        $input = $request->except('_token');
        if (!isset($input['user_id']) || $input['user_id'] == null || $input['user_id'] < 0)
            return null;
        if (!isset($input['product_id']) || $input['product_id'] == null || $input['product_id'] < 0)
            return null;
        if (!isset($input['price']) || $input['price'] == null || $input['price'] < 0)
            return null;

        $id = isset($input['id']) && $input['id'] !== null && $input['id'] > 0 ? $input['id'] : null;

        $combination_id = null;
        if (isset($input['combination_id']) && $input['combination_id'] !== null && $input['combination_id'] > 0)
            $combination_id = $input['combination_id'];

        $abra_kadabra = BotSettingsController::getSettings(null, 'abra_kadabra_for_thumbs_all')['settings_value'];

        $ingredients = isset($input['ingredients']) && $input['ingredients'] !== null ? json_decode($input['ingredients'], true) : null;
        if (is_array($ingredients))
            $ingredients = $ingredients['ingredients'];
        Log::debug('ingredients: ', ['ingredients' => $ingredients]);
//        $ingredients = $ingredients['ingredients'];

        $product = ApiPrestaShopController::sendResponse(['resource' => 'products', 'url' => 'products/'.$input['product_id']])->first();

        if ($id) {
            $product_in_cart = BotCartNew::getProductByUserIdAndId($input['user_id'], $id);
            $product_present_quantity_max = 1;
            $settings_present_quantity_max = BotSettings::getSettingsByName('product_present_quantity_max');
            if ($settings_present_quantity_max)
                $product_present_quantity_max = $settings_present_quantity_max->settings_value;
            if (($product_in_cart->product_present == 1 && $product_in_cart->quantity < $product_present_quantity_max) || $product_in_cart->product_present == 0) {
                $price_with_ingredients = bcadd($product_in_cart->price_all, $product_in_cart->price, 2);
                $ingredients_in_cart = BotCartNew::getChildrenProducts($product_in_cart->id);
                if ($ingredients_in_cart->count() > 0) {
                    $quantity = $product_in_cart->quantity;
                    $quantity_new = $quantity + 1;
                    foreach ($ingredients_in_cart as $ingredient) {
                        $ingredient_quantity = $ingredient->quantity / $quantity;
                        $ingredient_quantity_new = $ingredient_quantity * $quantity_new;
                        $ingredient_price_all = bcmul($ingredient->price, $ingredient_quantity_new, 2);
                        $update = BotCartNew::where('id', $ingredient->id)->update(['quantity' => $ingredient_quantity_new, 'price_all' => $ingredient_price_all]);
                        $price_with_ingredients = bcadd($price_with_ingredients, $ingredient_price_all, 2);
                    }
                }
                $product_in_cart->increment('quantity');
                $product_in_cart->increment('price_all', $input['price']);
                $update_product = BotCartNew::where('id', $product_in_cart->id)->update(['price_with_ingredients' => $price_with_ingredients]);
            }
        }
        else {
            $products_in_cart = BotCartNew::getProductByUserIdProductIdCombinationId($input['user_id'], $input['product_id'], $combination_id);
            if ($products_in_cart->count() > 0) {
                $stop_add = false;
                foreach ($products_in_cart as $product_in_cart) {
                    if ($stop_add === false) {
                        $price_with_ingredients = bcadd($product_in_cart->price_all, $input['price'], 2);
                        $ingredients_in_cart = BotCartNew::getChildrenProducts($product_in_cart->id);
                        $ingredient_not_exist = false;
                        if ($ingredients_in_cart->count() > 0) {
                            if ($ingredients) {
                                foreach ($ingredients as $ingredient) {
                                    if ($ingredients_in_cart->where('parent_id', $product_in_cart->id)->where('parent_product_id', $product_in_cart->product_id)->where('product_id', $ingredient['id'])->where('combination_id', $ingredient['combination_id'])->count() == 0)
                                        $ingredient_not_exist = true;
                                }
                            } else $ingredient_not_exist = true;

                            if ($ingredient_not_exist === false) {
                                $quantity = $product_in_cart->quantity;
                                $quantity_new = $quantity + 1;
                                foreach ($ingredients_in_cart as $ingredient) {
                                    $ingredient_quantity = $ingredient->quantity / $quantity;
                                    $ingredient_quantity_new = $ingredient_quantity * $quantity_new;
                                    $ingredient_price_all = bcmul($ingredient->price, $ingredient_quantity_new, 2);
                                    $update = BotCartNew::where('id', $ingredient->id)->update(['quantity' => $ingredient_quantity_new, 'price_all' => $ingredient_price_all]);
                                    Log::debug('Add ingredient', ['quantity' => $quantity, 'ingredient_quantity' => $ingredient_quantity, 'quantity_new' => $quantity_new, 'id' => $ingredient->id, 'i_q_n' => $ingredient_quantity_new]);
                                    $price_with_ingredients = bcadd($price_with_ingredients, $ingredient_price_all, 2);
                                }
                                $product_in_cart->increment('quantity');
                                $product_in_cart->increment('price_all', $input['price']);
                                $update_product = BotCartNew::where('id', $product_in_cart->id)->update(['price_with_ingredients' => $price_with_ingredients]);
                                $stop_add = true;
                            }
                            if ($ingredient_not_exist === true && $products_in_cart->last() == $product_in_cart) {
                                $add_product = self::addNewProductToCart($input['user_id'], $product, $combination_id, $input['price'], $ingredients);
                                Log::debug('add new product line 245');
                                $stop_add = true;
                            }
                        }
                        else {
                            if ($products_in_cart->last() == $product_in_cart) {
                                if ($ingredients) {
                                    $add_product = self::addNewProductToCart($input['user_id'], $product, $combination_id, $input['price'], $ingredients);
                                } else {
                                    $product_in_cart->increment('quantity');
                                    $product_in_cart->increment('price_all', $input['price']);
                                    $update_product = BotCartNew::where('id', $product_in_cart->id)->update(['price_with_ingredients' => $price_with_ingredients]);
                                }
                                $stop_add = true;
                            }
                            elseif (!$ingredients) {
                                $product_in_cart->increment('quantity');
                                $product_in_cart->increment('price_all', $input['price']);
                                $update_product = BotCartNew::where('id', $product_in_cart->id)->update(['price_with_ingredients' => $price_with_ingredients]);
                                $stop_add = true;
                            }
                        }
                    }
                }
            } else {
                $add_product = self::addNewProductToCart($input['user_id'], $product, $combination_id, $input['price'], $ingredients);
            }
        }

        $products = self::getProductsForCart($input['user_id']);
        $data = [
            'products' => $products,
            'abra_kadabra' => $abra_kadabra,
        ];
        return ['view' => (string)view('telegram.cart', $data), 'products_sum' => $products->sum('price_all')];
    }

    public static function addNewProductToCart($user_id, $product, $combination_id, $price, $ingredients)
    {
        $cart = new BotCartNew;
        $cart->user_id = $user_id;
        $cart->category_id = $product->id_category_default;
        $cart->product_id = $product->id;
        $cart->combination_id = $combination_id;
        $cart->product_name = $product->name;
        $cart->quantity = 1;
        $cart->price = $price;
        $cart->price_all = $price;
        $cart->price_with_ingredients = $price;
        $cart->save();

        if ($ingredients && count($ingredients) > 0)
        {
            $price_with_ingredients = $price;
            foreach ($ingredients as $ingredient) {
                $price_all_ingredient = bcmul($ingredient['price'], $ingredient['quantity'], 2);
                $cart_ingredient = new BotCartNew;
                $cart_ingredient->parent_id = $cart->id;
                $cart_ingredient->user_id = $user_id;
                $cart_ingredient->category_id = $ingredient['category_id'];
                $cart_ingredient->parent_product_id = $product->id;
                $cart_ingredient->product_id = $ingredient['id'];
                $cart_ingredient->combination_id = $ingredient['combination_id'];
                $cart_ingredient->product_name = $ingredient['name'];
                $cart_ingredient->quantity = $ingredient['quantity'];
                $cart_ingredient->price = $ingredient['price'];
                $cart_ingredient->price_all = $price_all_ingredient;
                $cart_ingredient->save();
                $price_with_ingredients = bcadd($price_with_ingredients, $price_all_ingredient, 2);
            }
            $update_product = BotCartNew::where('id', $cart->id)->update(['price_with_ingredients' => $price_with_ingredients]);
        }
        return $cart;
    }

    public static function removeProductInCart(LRequest $request)
    {
        $input = $request->except('_token');
        if (!isset($input['user_id']) || $input['user_id'] == null || $input['user_id'] < 0)
            return null;
        if (!isset($input['id']) || $input['id'] == null || $input['id'] < 0)
            return null;

        $abra_kadabra = BotSettingsController::getSettings(null, 'abra_kadabra_for_thumbs_all')['settings_value'];

        $product_in_cart = BotCartNew::getProductByUserIdAndId($input['user_id'], $input['id']);
        if ($product_in_cart) {
            $quantity = $product_in_cart->quantity;
            $quantity_new = $quantity - 1;
            if ($product_in_cart->quantity > 1) {
                $price_with_ingredients = bcsub($product_in_cart->price_all, $product_in_cart->price, 2);
                $ingredients = BotCartNew::getChildrenProducts($product_in_cart->id);
                if ($ingredients->count() > 0) {
                    foreach ($ingredients as $ingredient) {
                        $ingredient_quantity = $ingredient->quantity / $quantity;
                        $ingredient_quantity_new = $ingredient_quantity * $quantity_new;
                        $ingredient_price_all = bcmul($ingredient->price, $ingredient_quantity_new, 2);
                        $update = BotCartNew::where('id', $ingredient->id)->update(['quantity' => $ingredient_quantity_new, 'price_all' => $ingredient_price_all]);
                        $price_with_ingredients = bcadd($price_with_ingredients, $ingredient_price_all, 2);
                        Log::debug('Remove ingredient', ['quantity' => $quantity, 'ingredient_quantity' => $ingredient_quantity, 'quantity_new' => $quantity_new, 'id' => $ingredient->id, 'i_q_n' => $ingredient_quantity_new]);
                    }
                }
                $product_in_cart->decrement('quantity');
                $product_in_cart->decrement('price_all', $product_in_cart->price);
                $update_product = BotCartNew::where('id', $product_in_cart->id)->update(['price_with_ingredients' => $price_with_ingredients]);
            }
            else {
                $delete_product = BotCartNew::deleteProductFromCart($input['user_id'], $input['id']);
            }
        }
        $products = self::getProductsForCart($input['user_id']);
        $data = [
            'products' => $products,
            'abra_kadabra' => $abra_kadabra,
        ];
        return ['view' => (string)view('telegram.cart', $data), 'products_sum' => $products->sum('price_all')];
    }

    public static function deleteProductInCart(LRequest $request)
    {
        $input = $request->except('_token');
        if (!isset($input['user_id']) || $input['user_id'] == null || $input['user_id'] < 0)
            return null;
        if (!isset($input['id']) || $input['id'] == null || $input['id'] < 0)
            return null;

        $abra_kadabra = BotSettingsController::getSettings(null, 'abra_kadabra_for_thumbs_all')['settings_value'];

        $delete_product = BotCartNew::deleteProductFromCart($input['user_id'], $input['id']);
        $products = self::getProductsForCart($input['user_id']);
        $data = [
            'products' => $products,
            'abra_kadabra' => $abra_kadabra,
        ];
        return ['view' => (string)view('telegram.cart', $data), 'products_sum' => $products->sum('price_all')];
    }

    public static function showCart(LRequest $request)
    {
        $input = $request->except('_token');
        if (!isset($input['user_id']) || $input['user_id'] == null || $input['user_id'] < 0)
            return null;

        $abra_kadabra = BotSettingsController::getSettings(null, 'abra_kadabra_for_thumbs_all')['settings_value'];
        $user_id = $input['user_id'];
        $products = self::getProductsForCart($user_id);

        $data = [
            'products' => $products,
            'abra_kadabra' => $abra_kadabra,
        ];
        return ['view' => (string)view('telegram.cart', $data), 'products_sum' => $products->sum('price_with_ingredients')];
    }

    public static function getProductsForCart($user_id)
    {
        $check_cherry_win = BotRaffleCherryController::checkAndAddProductToCartByUserId($user_id);
        $products = BotCartNew::getProductsByUserId($user_id);
        $product_combinations = ApiPrestaShopController::sendResponse(['resource' => 'combinations', 'url' => 'combinations']);
        $product_options = ApiPrestaShopController::sendResponse(['resource' => 'product_option_values', 'url' => 'product_option_values']);

        $product_option_id = null;
        foreach ($products as $product) {
            $combination = $product_combinations->where('id', $product->combination_id)->first();
            if (isset($combination->associations)) {
                $association = $combination->associations;
                if (isset($association->product_option_values))
                {
                    $product_option_id = $association->product_option_values[0]->id;
                    $product->combination_name = $product_options->where('id', $product_option_id)->first()->name;
                }
                else $product->combination_name = null;
            }
            else $product->combination_name = null;
        }
        return $products;
    }

    public static function showOrder(LRequest $request)
    {
        $input = $request->except('_token');
        $u = 0;
        if (!isset($input['user_id']) || $input['user_id'] == null || $input['user_id'] < 0) {
            $u = 1;
            $user_id = 522750680;
        }
        else {
            $user_id = $input['user_id'];
        }

        $bot_order_last = BotOrdersNew::where('user_id', $user_id)->orderBy('id', 'desc')->first();
        $bot_orders = BotOrdersNew::where('user_id', $user_id)->where('delivery_id', 6)->orderBy('id', 'desc')->take(500)->get();
        $external_ids = [];
        foreach ($bot_orders as $bot_order) {
            array_push($external_ids, $bot_order->external_id);
        }
        $orders = PrestaShop_Orders::whereIn('id_order', $external_ids)->get();
        $addresses_ids = [];
        foreach ($orders as $order) {
            array_push($addresses_ids, $order->id_address_delivery);
        }
        $addresses = PrestaShop_Addresses::whereIn('id_address', $addresses_ids)->orderBy('id_address', 'desc')->get();
        $address = $addresses->first();

        $products = self::getProductsForCart($user_id);
        $price_all = $products->sum('price_all');
        $delivery = self::count_delivery($user_id, $price_all);
        $price_all = bcadd($price_all, $delivery, 2);
        if (round($price_all) == $price_all) $price_all += 0;
        $cashback = BotCashbackController::getUserCashbackAll($user_id);

        $cashback_categories = PrestaShop_Cashback_Categories::all();
        $cashback_categories_array = [];
        foreach ($cashback_categories as $category) {
            array_push($cashback_categories_array, $category->category_id);
        }

        $category_products = PrestaShop_Category_Product::all();
        $price_for_cashback = 0;
        foreach ($products as $product) {
            if ($category_products->where('id_product', $product->product_id)->count() > 0) {
                $in_cashback = false;
                foreach ($category_products->where('id_product', $product->product_id) as $item) {
                    if (in_array($item->id_category, $cashback_categories_array)) $in_cashback = true;
                }
                if ($in_cashback) {
                    $price_for_cashback = bcadd($price_for_cashback, $product->price_all, 2);
                }
            }
        }
        $cashback_max_pay = self::countCashbackMaxPay($user_id, $price_all, $price_for_cashback);
        if (round($cashback_max_pay) == $cashback_max_pay) $cashback_max_pay += 0;

        $payment_modules = PrestaShop_Module_Currency::getModulesByCurrency();

        $data = [
            'order_last' => $bot_order_last,
            'addresses' => $addresses,
            'address' => $address,
            'products' => $products,
            'payment_modules' => $payment_modules,
            'price_all' => $price_all,
            'cashback' => $cashback,
            'delivery' => $delivery,
            'cashback_max_pay' => $cashback_max_pay,
            'price_for_cashback' => $price_for_cashback,
        ];

        return $u == 1 ? view('telegram.order', $data) : ['view' => (string)view('telegram.order', $data), 'products_sum' => $products->sum('price_all')];
    }

    public static function getStreets(LRequest $request)
    {
        $input = $request->except('_token');
//        Log::warning($input);
        $text = isset($input['q']) && $input['q'] !== '' ? $input['q'] : null;
        $streets = SimplaStreetList::searchStreet($text);
        $result = [];
        foreach ($streets as $street) {
            $result[] = ['id' => $street->name, 'text' => $street->name];
        }
        $result = ['results' => $result];
        $result = json_encode($result);
        return $result;
    }

    public static function getMain()
    {
        $category_select = 3;
        $menus = BotMenu::where('enabled', 1)->orderBy('menu_sort', 'asc')->get();

        $whStatus = WorkingHoursService::isCurrentlyOpen();

        $data = [
            'category_select' => $category_select,
            'menus' => $menus,
            'wh_is_open' => $whStatus['is_open'],
            'wh_next_open' => $whStatus['next_open'],
        ];
        return view('telegram.main', $data);
    }

    public static function addressInDeliveryArea(LRequest $request)
    {
        $input = $request->except('_token');
        if (!isset($input['address']) || $input['address'] == null || $input['address'] == '')
            return null;
        Log::debug('addressInDeliveryArea', ['address' => $input['address']]);

        $url = "https://api.ecopizza.com.ua/address-in-delivery-area";
        $response = Http::get($url, [
            'token' => env('API_ECOPIZZA_TOKEN'),
            'address' => $input['address']
        ]);

        Log::debug('addressInDeliveryArea response', ['response' => (string)$response]);

        if ($response->ok() && $response->successful()) {
            $result = json_decode($response);
            return $result;
        }
        else return ['message' => 'Error...'];
    }

    public static function getTimeForOrder(LRequest $request)
    {
        // Working hours check — allow if future orders are permitted
        $whStatus = WorkingHoursService::isCurrentlyOpen();
        if (!$whStatus['is_open'] && WorkingHoursService::getSetting('allow_future_orders', '1') != '1') {
            $closedMsg = WorkingHoursService::getClosedMessage();
            return json_encode(['success' => false, 'working_hours_closed' => true, 'message' => $closedMsg]);
        }

        $input = $request->except('_token');
        if (!isset($input['date']) || $input['date'] == null || $input['date'] == '')
            return null;
        if (!isset($input['address']) || $input['address'] == null || $input['address'] == '')
            if (!isset($input['takeaway']) || $input['takeaway'] == null || $input['takeaway'] == 0)
                return null;

        $url = "https://api.ecopizza.com.ua/get-time";
        $response = Http::get($url, [
            'token' => env('API_ECOPIZZA_TOKEN'),
            'date' => $input['date'],
            'address' => $input['address'],
            'takeaway' => $input['takeaway'],
            'time_now' => time(),
        ]);

        Log::debug('getTimeForOrder response', ['response' => (string)$response]);

        if ($response->ok() && $response->successful()) {
            $result = json_decode($response);
            return $result;
        }
        else return ['message' => 'Error...'];
    }

    public static function getOrderSum(LRequest $request)
    {
        $input = $request->except('_token');
        if (!isset($input['user_id']) || $input['user_id'] == null || $input['user_id'] == '')
            return null;

        $discounts = isset($input['discounts']) && $input['discounts'] != null && is_array(json_decode($input['discounts'], true)) ? json_decode($input['discounts'], true) : null;
        $cashback = isset($input['cashback']) && $input['cashback'] != null && $input['cashback'] != '' && $input['cashback'] > 0 ? $input['cashback'] : 0;

        $user_id = $input['user_id'];

        try {
            $webService = new PrestaShopWebserviceController(config('services.prestashop.url'), config('services.prestashop.key'));

            $products = BotCartNew::getProductsByUserId($user_id);
            $cart_price_all = $products->sum('price_all');

            $cart_rules = $webService->get(array('url' => config('services.prestashop.url').'/api/cart_rules?display=full&language='.self::$language), true);
            $cart_rules = json_decode($cart_rules);
            $cart_rules = collect($cart_rules->cart_rules);

            $cashback_categories = PrestaShop_Cashback_Categories::all();
            $cashback_categories_array = [];
            foreach ($cashback_categories as $category) {
                array_push($cashback_categories_array, $category->category_id);
            }

            $category_products = PrestaShop_Category_Product::all();

            $discount_all = 0;
            $price_with_discount = $cart_price_all;

            $price_for_cashback = 0;
            foreach ($products as $product) {
                if ($category_products->where('id_product', $product->product_id)->count() > 0) {
                    $in_cashback = false;
                    foreach ($category_products->where('id_product', $product->product_id) as $item) {
                        if (in_array($item->id_category, $cashback_categories_array)) $in_cashback = true;
                    }
                    if ($in_cashback) {
                        $price_for_cashback = bcadd($price_for_cashback, $product->price_all, 2);
                    }
                }
            }

            Log::debug('discounts:', ['discounts' => $discounts]);
            if ($discounts) {
                foreach ($products as $product) {
                    foreach ($discounts as $discount_id) {
//                        $cart_rule = $cart_rules->where('id', $discount_id)->first();
//                        $categories_to_discount = PrestaShop_Cart_Rule_Product_Rule_Group::findRuleByIdCartRule($discount_id);
//                        if ($categories_to_discount->where('id_item', $product->category_id)->count() > 0) {
//                            $percent = bcadd($cart_rule->reduction_percent, 0, 2);
//                            $discount = bcmul(bcdiv($product->price_all, 100, 10), $percent, 10);
//                            $product->price_all = bcsub($product->price_all, $discount, 2);
//                            $price_with_discount = bcsub($price_with_discount, $discount, 10);
//                            $price_for_cashback = bcsub($price_for_cashback, $discount, 10);
//                            $discount_all = bcadd($discount_all, $discount, 10);
//                        }
                        $cart_rule = $cart_rules->where('id', $discount_id)->first();
                        $categories_to_discount = PrestaShop_Cart_Rule_Product_Rule_Group::findRuleByIdCartRule($discount_id);
                        if ($categories_to_discount->where('id_item', $product->product_id)->count() > 0) {
                            $percent = bcadd($cart_rule->reduction_percent, 0, 2);
                            $discount = bcmul(bcdiv($product->price_all, 100, 10), $percent, 10);
                            $product->price_all = bcsub($product->price_all, $discount, 2);
                            $price_with_discount = bcsub($price_with_discount, $discount, 10);
                            $price_for_cashback = bcsub($price_for_cashback, $discount, 10);
                            $discount_all = bcadd($discount_all, $discount, 10);
                        }
                    }
                }
                $discount_all = bcadd($discount_all, 0, 2);
                $price_with_discount = bcadd($price_with_discount, 0, 2);
                $price_for_cashback = bcadd($price_for_cashback, 0, 2);
            }

            $delivery = self::count_delivery($user_id, $price_with_discount, $discounts);

            if (round($discount_all) == $discount_all) $discount_all += 0;

            $price_with_discount = bcadd($price_with_discount, $delivery, 2);

            $price_for_cashback = bcadd($price_for_cashback, $delivery, 2);
            if (round($price_for_cashback) == $price_for_cashback) $price_for_cashback += 0;

            $cashback_max_pay = self::countCashbackMaxPay($user_id, $price_with_discount, $price_for_cashback, $discounts);
            if (round($cashback_max_pay) == $cashback_max_pay) $cashback_max_pay += 0;

            if ($cashback <= $cashback_max_pay) {
                $price_with_discount = bcsub($price_with_discount, $cashback, 2);
            }

            if (round($price_with_discount) == $price_with_discount) $price_with_discount += 0;

            return [
                'price_all' => $cart_price_all,
                'discount_all' => $discount_all,
                'price_with_discount' => $price_with_discount,
                'delivery' => $delivery,
                'cashback_max_pay' => $cashback_max_pay,
                'price_for_cashback' => $price_for_cashback,
                'success' => true,
                'message' => 'ok'
            ];

        } catch (PrestaShopWebserviceExceptionController $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

    }

    public static function count_delivery($user_id, $sum, $discounts = [])
    {

        $max_sum_order = (float)BotSettingsController::getSettings($user_id,'max_sum_order')['settings_value'];
        $sum_delivery = (float)BotSettingsController::getSettings($user_id,'sum_delivery')['settings_value'];

        if (in_array(4, $discounts))
            return 0;

        if ($sum < $max_sum_order) {
            if ($max_sum_order - $sum < $sum_delivery) {
                $sum_delivery = bcsub($max_sum_order, $sum, 2);
                if (round($sum_delivery) == $sum_delivery) $sum_delivery += 0;
                return $sum_delivery;
            }
            else return $sum_delivery;
        }
        else return 0;

    }

    public static function countCashbackMaxPay($user_id, $price_all, $price_for_cashback, $discounts = [])
    {
        $cart_rules_takeaway_id = 4;
        $cashback = BotCashbackController::getUserCashback($user_id);
        $cashback_action = BotCashbackController::getUserCashbackAction($user_id);
        $max_sum_order = (float)BotSettingsController::getSettings($user_id,'max_sum_order')['settings_value'];
        $cashback_all = bcadd($cashback, $cashback_action, 2);

        $cashback_pay = 0;
        $percent_max_pay = 50;
        $cashback_maybe_pay = bcmul(bcdiv($price_for_cashback, 100, 10), $percent_max_pay, 2);
        $cashback_maybe_pay_max = bcmul(bcdiv($price_all, 100, 10), $percent_max_pay, 2);
        $price_for_no_cashback = bcsub($price_all, $price_for_cashback, 2);

        if ($cashback_all > 0) {
            if (!in_array($cart_rules_takeaway_id, $discounts)) {
//                if ($sum_order > $max_sum_order) {
//                    if (bcmul(bcdiv($sum_order, 100, 10), $percent_max_pay, 2) >= $max_sum_order) {
//                        $cashback_pay = bcmul(bcdiv($sum_order, 100, 10), $percent_max_pay, 2);
//                    }
//                    else {
////                        $difference = bcsub($sum_order, $max_sum_order, 2);
//                        $difference = bcsub($sum_order, $max_sum_order, 2);
//                        $cashback_pay = bcmul(bcdiv($difference, 100, 10), $percent_max_pay, 2);
//                    }
//                    return $cashback_pay;
//                }
                if ($price_for_no_cashback > 0) {
                    if ($price_all - $cashback_maybe_pay_max >= $max_sum_order) {
                        $cashback_pay = $cashback_maybe_pay;
                    }
                    else {
                        $difference = bcsub($max_sum_order, $price_for_no_cashback, 2);
                        $cashback_pay = bcsub($price_for_cashback, $difference, 2);
                        $cashback_pay = $cashback_pay > $cashback_maybe_pay ? $cashback_maybe_pay : $cashback_pay;
                    }
                }
                else {
                    if ($price_for_cashback - $cashback_maybe_pay >= $max_sum_order) {
                        $cashback_pay = $cashback_maybe_pay;
                    }
                    else {
//                        $difference = bcsub($price_for_cashback, $max_sum_order, 2);
                        $cashback_pay = bcsub($price_for_cashback, $max_sum_order, 2);
                    }
                }
            }
            else {
                $cashback_pay = bcmul(bcdiv($price_for_cashback, 100, 10), $percent_max_pay, 2);
            }
        }
        $cashback_pay = $cashback_pay >= $cashback_all ? $cashback_all : $cashback_pay;
        $cashback_pay = $cashback_pay > 0 ? $cashback_pay : 0;
        return $cashback_pay;
    }

    public static function addOrder(LRequest $request)
    {
        // Working hours check — allow if future orders are permitted
        $whStatus = WorkingHoursService::isCurrentlyOpen();
        if (!$whStatus['is_open'] && WorkingHoursService::getSetting('allow_future_orders', '1') != '1') {
            $closedMsg = WorkingHoursService::getClosedMessage();
            return json_encode(['success' => false, 'working_hours_closed' => true, 'message' => $closedMsg]);
        }

        $input = $request->except('_token');
        $errors = [];
        $data = [];

        if (empty($input['name'])) {
            $errors['name'] = 'name is required.';
        }

        if (empty($input['phone'])) {
            $errors['phone'] = 'phone is required.';
        }

        if (empty($input['street'])) {
            $errors['street'] = 'street is required.';
        }

        if (empty($input['house'])) {
            $errors['house'] = 'house is required.';
        }

        if (!empty($errors)) {
            $data['success'] = false;
            $data['errors'] = $errors;
        } else {
            $data['success'] = true;
            $data['message'] = 'Success!';
        }

        return json_encode($data);
    }

    public static function checkCustomer($webService, $phone, $firstname, $lastname) {

        $customer = ApiPrestaShopController::sendResponse(['resource' => 'customers', 'url' => 'customers', 'filter' => ['phone_login' => $phone]]);
        if ($customer->count() > 0) {
            $customer = $customer->first();
        }
        else {
            $xml = $webService->get(array('url' => config('services.prestashop.url') .'/api/customers?schema=blank'));

            $xml->customer->id_default_group = 3;
            $xml->customer->id_lang          = 2;
            $xml->customer->deleted          = 0;
            $xml->customer->active           = 1;
            $xml->customer->firstname        = $firstname;
            $xml->customer->lastname         = $lastname;
            $xml->customer->email            = 'bot'.$phone.'@ecopizza.com.ua';
            $xml->customer->phone_login      = $phone;

            $xml->customer->date_add     = date("Y-m-d H:i:s");
            $xml->customer->date_upd     = date("Y-m-d H:i:s");

            $xml->customer->associations->groups->group[0]->id = 3;

            $opt = array( 'resource' => 'customers' );
            $opt['postXml'] = $xml->asXML();
            $xml = $webService->add( $opt );
            $customer = $xml->customer;
            $id_customer = $xml->customer->id;
        }
//        dd($customer);
        return $customer;

    }

    public static function checkAddress($webService, $customer, $address) {

        $user_addresses = ApiPrestaShopController::sendResponse(['resource' => 'addresses', 'url' => 'addresses', 'filter' => ['id_customer' => $customer->id]]);
//        Log::warning("User addresses:");
//        Log::warning($user_addresses);
        if ($user_addresses->count() > 0) {
            $user_address = $user_addresses->where('alias', $address->alias)->first();
            if ($user_address) {
                $id_address = $user_address->id;
            }
            else {
                $id_address = self::createAddress($webService, $customer, $address);
            }
        }
        else {
            $id_address = self::createAddress($webService, $customer, $address);
        }
        return $id_address;

    }

    public static function createAddress($webService, $customer, $address)
    {
        $xml = $webService->get(array('url' => config('services.prestashop.url') .'/api/addresses?schema=blank'));

        // Adding dinamic and mandatory fields
        // Required
        $xml->address->id_customer  = $customer->id;
        $xml->address->id_country   = 214;
        $xml->address->alias        = $address->alias;
        $xml->address->firstname    = $customer->firstname;
        $xml->address->lastname     = $customer->lastname;
        $xml->address->city         = 'Дніпро';
        $xml->address->address1     = $address->street;
        $xml->address->build        = $address->build; // дом
        $xml->address->frontdoor    = $address->frontdoor; // подъезд
        $xml->address->corps        = $address->corps; // корпус
        $xml->address->floor        = $address->floor; // этаж
        $xml->address->flat         = $address->flat; // квартира
        // Others
        $xml->address->phone_mobile = $customer->phone_login;
        $xml->address->postcode     = 0;
        $xml->address->date_add     = date("Y-m-d H:i:s");
        $xml->address->date_upd     = date("Y-m-d H:i:s");

        // Adding the new Customer's Addresss
        $opt = array( 'resource' => 'addresses' );
        $opt['postXml'] = $xml->asXML();
        $xml = $webService->add( $opt );
        $id_address = (int)$xml->address->id;
        return $id_address;
    }

    public static function createCart($webService, $user_id, $id_customer, $id_address, $discounts = null, $comment = null, $delivery_type = 6, $cashback_pay = 0) {

//        $xml = $webService->get(array('url' => 'https://stage.ecopizza.com.ua/'.'api/carts/?schema=blank'));
//        $xml->cart->id_currency         = 1;
//        $xml->cart->id_lang             = 2;
//
//        // Others
//        $xml->cart->id_address_delivery = $id_address;
//        $xml->cart->id_address_invoice  = $id_address;
//        $xml->cart->id_customer         = $id_customer;
//        $xml->cart->carrier             = 6;
//        $xml->cart->date_add            = date("Y-m-d H:i:s");
//        $xml->cart->date_upd            = date("Y-m-d H:i:s");
//
//        // Adding the new customer's cart
//        $opt = array( 'resource' => 'carts' );
//        $opt['postXml'] = $xml->asXML();
//        $xml = $webService->add( $opt );
//        $id_cart = (int)$xml->cart->id;


        $xml = $webService->get(array('url' => config('services.prestashop.url').'/api/carts/?schema=blank'));
//        $xml->cart->id                  = $id_cart;
        $xml->cart->id_currency         = 1;
        $xml->cart->id_lang             = 2;

        // Others
        $xml->cart->id_address_delivery = $id_address;
        $xml->cart->id_address_invoice  = $id_address;
        $xml->cart->id_customer         = $id_customer;
        $xml->cart->carrier             = $delivery_type;
        $xml->cart->id_carrier          = $delivery_type;
//        $xml->cart->delivery_option     = json_encode([$id_address => $delivery_type]);
        $xml->cart->delivery_option     = '{"'.$id_address.'": "'.$delivery_type.'"}';
        $xml->cart->date_add            = date("Y-m-d H:i:s");
        $xml->cart->date_upd            = date("Y-m-d H:i:s");

        $products = BotCartNew::getProductsByUserId($user_id);
        $price_all = 0;

//        foreach ($products->where('parent_id', null) as $product) {
//            $id_customization = self::createCustomization($webService, $id_cart, $id_address, $product->product_id, $product->combination_id, $product->quantity);
//        }

        $i = 0;
        foreach ($products->where('parent_id', null) as $product) {
            $id_customization = rand(100000000,999999999);
            $xml->cart->associations->cart_rows->cart_row[$i]->id_product            = $product->product_id;
            $xml->cart->associations->cart_rows->cart_row[$i]->id_product_attribute  = $product->combination_id;
            $xml->cart->associations->cart_rows->cart_row[$i]->id_address_delivery   = $id_address;
            $xml->cart->associations->cart_rows->cart_row[$i]->quantity              = $product->quantity;
            $xml->cart->associations->cart_rows->cart_row[$i]->id_customization      = $id_customization;
            foreach ($products->where('parent_id', $product->id) as $ingredient) {
                $i++;
                $xml->cart->associations->cart_rows->cart_row[$i]->id_product            = $ingredient->product_id;
                $xml->cart->associations->cart_rows->cart_row[$i]->id_product_attribute  = $ingredient->combination_id;
                $xml->cart->associations->cart_rows->cart_row[$i]->id_address_delivery   = $id_address;
                $xml->cart->associations->cart_rows->cart_row[$i]->id_customization      = $id_customization;
                $xml->cart->associations->cart_rows->cart_row[$i]->quantity              = $ingredient->quantity;
                $price_all += $ingredient->price_all;
            }
            $i++;
            $price_all += $product->price_all;
        }

        $opt = array('resource' => 'carts');
        $opt['postXml'] = $xml->asXML();
        $xml_cart = $webService->add($opt);

        $id_cart = (int)$xml_cart->cart->id;
        Log::debug('Cart created', ['Cart_id' => $id_cart, 'Discounts' => $discounts, 'Cashback' => $cashback_pay]);
        sleep(1);
        if ($discounts) {
            foreach ($discounts as $discount_id) {
                $cart_cart_rule = new PrestaShop_Cart_Cart_Rule;
                $cart_cart_rule->id_cart = $id_cart;
                $cart_cart_rule->id_cart_rule = $discount_id;
                $cart_cart_rule->save();
            }
        }

        if ($cashback_pay > 0) {
            $new_cart_rule = new PrestaShop_Cart_Rule;
            $new_cart_rule->date_from = date("Y-m-d 00:00:00");
            $new_cart_rule->date_to = date("Y-m-d 23:59:59");
            $new_cart_rule->date_add = date("Y-m-d H:i:s");
            $new_cart_rule->date_upd = date("Y-m-d H:i:s");
            $new_cart_rule->description = 'CashBack Pay, cart: '.$id_cart;
            $new_cart_rule->reduction_amount = $cashback_pay;
            $new_cart_rule->reduction_tax = 1;
            $new_cart_rule->reduction_currency = 1;
            $new_cart_rule->quantity = 1;
            $new_cart_rule->quantity_per_user = 1;
            $new_cart_rule->priority = 100;
            $new_cart_rule->partial_use = 1;
            $new_cart_rule->save();

            $langs = PrestaShop_Lang::where('active', 1)->get();
            foreach ($langs as $lang) {
                $new_cart_rule_lang = new PrestaShop_Cart_Rule_Lang;
                $new_cart_rule_lang->id_cart_rule = $new_cart_rule->id;
                $new_cart_rule_lang->id_lang = $lang->id_lang;
                $new_cart_rule_lang->name = 'CashBack Pay, cart: '.$id_cart;
                $new_cart_rule_lang->save();
            }

            sleep(1);
            $cart_cart_rule = new PrestaShop_Cart_Cart_Rule;
            $cart_cart_rule->id_cart = $id_cart;
            $cart_cart_rule->id_cart_rule = $new_cart_rule->id;
            $cart_cart_rule->save();
        }

        if (($comment && $comment != '') || $cashback_pay > 0) {
            $xml = $webService->get(array('url' => config('services.prestashop.url').'/api/messages/?schema=blank'));
            $xml->message->id_cart = $id_cart;
            $xml->message->message = $cashback_pay > 0 ? $comment.'; '.PHP_EOL.'Сплачено кешбеком: '.$cashback_pay.' грн' : $comment;
            $opt = array(
                'resource' => 'messages',
                'postXml' => $xml->asXML(),
            );
            $xml = $webService->add($opt);
        }

        return ['id' => $id_cart, 'price_all' => $price_all];
    }

    public static function getModuleAndPayment($payment_type)
    {
        $module = 'ffgotivka';
        $payment = 'Готівка';

        if ($payment_type == 'card') {
            $module = 'wayforpay';
            $payment = 'Платежі WayForPay';
        }
        elseif ($payment_type == 'cash') {
            $module = 'ffgotivka';
            $payment = 'Готівка';
        }
        elseif ($payment_type == 'terminal') {
            $module = 'ffterminalnameste';
            $payment = 'Оплата через термінал на місці';
        }
        return ['module' => $module, 'payment' => $payment];
    }

    public static function createOrder($data) {

        $webService = $data['webService'];
        $cart = $data['cart'];

        $xml = $webService->get(array('url' => config('services.prestashop.url').'/api/orders/?schema=blank'));

        $moduleAndPayment = self::getModuleAndPayment($data['payment_type']);

        $cart_rules = $webService->get(array('url' => config('services.prestashop.url').'/api/cart_rules?display=full&language='.self::$language), true);
        $cart_rules = json_decode($cart_rules);
        $cart_rules = collect($cart_rules->cart_rules);

        $products = BotCartNew::getProductsByUserId($data['user_id']);

        $discount_all = 0;
        $price_with_discount = $cart['price_all'];

//        if ($data['discounts']) {
//            $price_all = 0;
//            foreach ($products as $product) {
//                $price_all = bcadd($price_all, $product->price_all, 2);
//            }
//            $price_with_discount = $price_all;
//
//            foreach ($data['discounts'] as $discount_id) {
//                $cart_rule = $cart_rules->where('id', $discount_id)->first();
//                $percent = bcadd($cart_rule->reduction_percent, 0, 2);
//
//                $discount = bcmul(bcdiv($price_with_discount, 100, 10), $percent, 2);
//                $price_with_discount = bcsub($price_with_discount, $discount, 2);
//                $discount_all = bcadd($discount_all, $discount, 2);
//            }
//        }

        if ($data['discounts']) {
            foreach ($products as $product) {
                foreach ($data['discounts'] as $discount_id) {
                    $cart_rule = $cart_rules->where('id', $discount_id)->first();
                    $categories_to_discount = PrestaShop_Cart_Rule_Product_Rule_Group::findRuleByIdCartRule($discount_id);
//                    print '<br />'.$product->product_name.': '.'<br />';
//                    if ($categories_to_discount->where('id_item', $product->category_id)->count() > 0) {
                    if ($categories_to_discount->where('id_item', $product->product_id)->count() > 0) {
                        $percent = bcadd($cart_rule->reduction_percent, 0, 2);
                        $discount = bcmul(bcdiv($product->price_all, 100, 10), $percent, 10);
                        $product->price_all = bcsub($product->price_all, $discount, 2);
                        $price_with_discount = bcsub($price_with_discount, $discount, 10);
                        $discount_all = bcadd($discount_all, $discount, 10);
//                        print('percent: '.$percent.'; price all: '.$product->price_all.'; discount: '.$discount.'; price with discount: '.$price_with_discount.'; discount all: '.$discount_all);
                    }
                }
            }
            $discount_all = bcadd($discount_all, 0, 2);
            $price_with_discount = bcadd($price_with_discount, 0, 2);
        }
        $delivery_sum = self::count_delivery($data['user_id'], $price_with_discount, $data['discounts']);

        $discount_all = bcadd($discount_all, $data['cashback_pay'], 2);
        $price_with_discount = bcsub($price_with_discount, $data['cashback_pay'], 2);

        Log::debug('CASHBACK', ['cashback_pay' => $data['cashback_pay'], 'discount_all' => $discount_all, 'price_with_discount' => $price_with_discount]);

        $xml->children()->children()->id_address_delivery = $data['id_address'];
        $xml->children()->children()->id_address_invoice = $data['id_address'];
        $xml->children()->children()->id_cart = $cart['id'];
        $xml->children()->children()->id_currency = 1;
        $xml->children()->children()->id_lang = 2;
        $xml->children()->children()->id_customer = $data['id_customer'];
        $xml->children()->children()->id_carrier = $data['delivery_type'];
        $xml->children()->children()->current_state = 24;
        $xml->children()->children()->module = $moduleAndPayment['module'];
        $xml->children()->children()->date_add = date("Y-m-d H:i:s");
        $xml->children()->children()->date_upd = date("Y-m-d H:i:s");
        $xml->children()->children()->date = date("Y-m-d", strtotime($data['date']));
        $xml->children()->children()->time = $data['time'];
        $xml->children()->children()->payment = $moduleAndPayment['payment'];

        $xml->children()->children()->total_discounts = $discount_all;
        $xml->children()->children()->total_discounts_tax_incl = $discount_all;
        $xml->children()->children()->total_discounts_tax_excl = $discount_all;

        $xml->children()->children()->total_paid = $price_with_discount;
        $xml->children()->children()->total_paid_tax_incl = $price_with_discount;
        $xml->children()->children()->total_paid_tax_excl = $price_with_discount;
        $xml->children()->children()->total_paid_real = 0;
        $xml->children()->children()->total_products = $cart['price_all'];
        $xml->children()->children()->total_products_wt = $cart['price_all'];
        $xml->children()->children()->total_shipping = $delivery_sum;
        $xml->children()->children()->total_shipping_tax_incl = $delivery_sum;
        $xml->children()->children()->total_shipping_tax_excl = $delivery_sum;
        $xml->children()->children()->carrier_tax_rate = 0.000;
        $xml->children()->children()->total_wrapping = 0.000000;
        $xml->children()->children()->total_wrapping_tax_incl = 0.000000;
        $xml->children()->children()->total_wrapping_tax_excl =0.000000;
        $xml->children()->children()->conversion_rate = 1.000000 ;
        $xml->children()->children()->dontcallme = $data['without_call'];
        $xml->children()->children()->odd_money = $data['change_from'];

        $i = 0;
        foreach ($products as $product) {
            $xml->children()->children()->associations->order_rows->order_row[$i]->product_id            = $product->product_id;
            $xml->children()->children()->associations->order_rows->order_row[$i]->product_attribute_id  = $product->combination_id;
            $xml->children()->children()->associations->order_rows->order_row[$i]->product_quantity      = $product->quantity;
            $xml->children()->children()->associations->order_rows->order_row[$i]->product_name          = $product->product_name;
            $xml->children()->children()->associations->order_rows->order_row[$i]->product_price         = $product->price;
            $i++;
        }

//        $products = BotCartNew::getProductsByUserId($user_id);
//        $price_all = 0;
//
////        foreach ($products->where('parent_id', null) as $product) {
////            $id_customization = self::createCustomization($webService, $id_cart, $id_address, $product->product_id, $product->combination_id, $product->quantity);
////        }
//
//        $i = 0;
//        foreach ($products->where('parent_id', null) as $product) {
//            $id_customization = self::createCustomization($webService, $cart['id'], $id_address, $product->product_id, $product->combination_id, $product->quantity);
//            $xml->children()->children()->associations->order_rows->order_row[$i]->product_id            = $product->product_id;
//            $xml->children()->children()->associations->order_rows->order_row[$i]->product_attribute_id  = $product->combination_id;
//            $xml->children()->children()->associations->order_rows->order_row[$i]->product_quantity      = $product->quantity;
//            $xml->children()->children()->associations->order_rows->order_row[$i]->product_name          = $product->product_name;
//            $xml->children()->children()->associations->order_rows->order_row[$i]->product_price         = $product->price;
//            $xml->children()->children()->associations->order_rows->order_row[$i]->id_customization      = $id_customization;
//            foreach ($products->where('parent_id', $product->id) as $ingredient) {
//                $i++;
//                $xml->children()->children()->associations->order_rows->order_row[$i]->product_id            = $ingredient->product_id;
//                $xml->children()->children()->associations->order_rows->order_row[$i]->product_attribute_id  = $ingredient->combination_id;
//                $xml->children()->children()->associations->order_rows->order_row[$i]->product_quantity      = $ingredient->quantity;
//                $xml->children()->children()->associations->order_rows->order_row[$i]->product_name          = $ingredient->product_name;
//                $xml->children()->children()->associations->order_rows->order_row[$i]->product_price         = $ingredient->price;
//                $xml->children()->children()->associations->order_rows->order_row[$i]->id_customization      = $id_customization;
//            }
//            $i++;
//        }

        $opt = array(
            'resource' => 'orders',
            'postXml' => $xml->asXML(),
        );

//        Log::info($opt);

        $delivery_type = $data['delivery_type'];
        $delivery_names = [
            6 => 'Кур’єром',
            8 => 'Самовивіз',
        ];

//        Log::warning('XML OPT:');
//        Log::warning($opt);
//        Log::warning('XML ANSWER:');
        $xml = $webService->add($opt);
//        Log::warning($xml);
        $id_order = (int)$xml->order->id;

//        Log::warning($id_order);

        $bot_order = new BotOrdersNew;
        $bot_order->external_id = $id_order;
        $bot_order->user_id = $data['user_id'];
        $bot_order->name = $data['name'];
        $bot_order->delivery_name = $delivery_names[$delivery_type];
        $bot_order->delivery_id = $data['delivery_type'];
        $bot_order->address = $data['address'];
        $bot_order->phone = $data['phone'];
        $bot_order->price_all = $cart['price_all'];
        $bot_order->discount = $discount_all;
        $bot_order->delivery_sum = $delivery_sum;
        $bot_order->price_with_discount = bcadd($price_with_discount, $delivery_sum, 2);
        $bot_order->price_for_cashback = $data['price_for_cashback'];
        $bot_order->cashback_pay = $data['cashback_pay'];
        $bot_order->delivery_date = $data['date'];
        $bot_order->delivery_time = $data['time'];
        $bot_order->pay = $moduleAndPayment['payment'];
        $bot_order->payment_id = 0;
        $bot_order->comment = $data['comment'];
        $bot_order->pay_yes  = 0;
        $bot_order->cashback_cron  = 0;
        $bot_order->save();

        $products = BotCartNew::getProductsByUserId($data['user_id']);
        foreach ($products->where('parent_id', null) as $product) {
            $bot_order_content = new BotOrderContent;
            $bot_order_content->order_id = $bot_order->id;
            $bot_order_content->user_id = $data['user_id'];
            $bot_order_content->category = $product->category_id;
            $bot_order_content->product_id = $product->product_id;
            $bot_order_content->variant_id = $product->combination_id;
            $bot_order_content->product_name = $product->product_name;
            $bot_order_content->variant_name = $product->combination_name;
            $bot_order_content->quantity = $product->quantity;
            $bot_order_content->price = $product->price;
            $bot_order_content->price_all = $product->price_all;
            $bot_order_content->vendor_code = '';
            $bot_order_content->save();
            foreach ($products->where('parent_id', $product->id) as $ingredient) {
                $bot_order_content_ingredient = new BotOrderContent;
                $bot_order_content_ingredient->order_id = $bot_order->id;
                $bot_order_content_ingredient->user_id = $data['user_id'];
                $bot_order_content_ingredient->category = $ingredient->category_id;
                $bot_order_content_ingredient->parent_product_id = $bot_order_content->id;
                $bot_order_content_ingredient->product_id = $ingredient->product_id;
                $bot_order_content_ingredient->variant_id = $ingredient->combination_id;
                $bot_order_content_ingredient->product_name = $ingredient->product_name;
                $bot_order_content_ingredient->variant_name = $ingredient->combination_name;
                $bot_order_content_ingredient->quantity = $ingredient->quantity;
                $bot_order_content_ingredient->price = $ingredient->price;
                $bot_order_content_ingredient->price_all = $ingredient->price_all;
                $bot_order_content_ingredient->vendor_code = '';
                $bot_order_content_ingredient->save();
            }
        }

        if ($data['cashback_pay'] > 0) {
            $cashback = BotCashbackController::payCashbackNew($data['user_id'], $data['cashback_pay'], $bot_order->id, $id_order);
        }

        return $id_order;
    }

    public static function testPresta()
    {

        $input['id'] = 99;
        $abra_kadabra = BotSettingsController::getSettings(null, 'abra_kadabra_for_thumbs_all')['settings_value'];
        $category_select = isset($input['category_select']) && $input['category_select'] !== null ? $input['category_select'] : 7;

        $categories = PrestaShop_Category::getCategoriesAll();
        $categories_child = $categories->where('id_parent', $category_select);
        $categories_array = [];
        foreach ($categories_child as $category) {
            array_push($categories_array, $category->id_category);
        }

        $products = PrestaShop_Product::getProductsByCategoryId($category_select, $categories_array);
        $product_features = PrestaShop_Feature_Product::getFeatures();
        $product_attributes = PrestaShop_Product_Attribute::getAttributesAll();
        foreach ($products as $product) {
            $product->product_features = $product_features->where('id_product', $product->id_product)->where('id_feature', 1)->first();
            $product->product_attributes = $product_attributes->where('id_product', $product->id_product)->sortBy('price');
        }
        $products_categories = $products->unique('category_name')->sortBy('category_name');
        dd($products, $products_categories);
        $products_new = collect();
        foreach ($products_categories as $product_category) {
            $products_temp = $products->where('id_category', $product_category->id_category);
            foreach ($products_temp as $product) {
                $products_new->push($product);
            }
        }
        $products = $products_new;
        dd($categories, $products);

        $products = PrestaShop_Product::getProductsAll();
        $product = $products->where('id_product', $input['id'])->first();
        $product_features = PrestaShop_Feature_Product::getFeatureByProductId($input['id']);
        $product_attributes = PrestaShop_Product_Attribute::getAttributesAll();
        $product->product_attributes = $product_attributes->where('id_product', $input['id']);
        $product->product_features = $product_features->where('id_feature', 1)->first();
        $product_accessories = PrestaShop_Accessory_Group_Product::getAccessoriesByProductId($input['id']);
        foreach ($product_accessories as $item) {
            $item->product = $products->where('id_product', $item->id_accessory)->first();
            $item->product_attributes = $product_attributes->where('id_product', $item->id_accessory)->first();
        }
        $categories_unique = $product_accessories->unique('id_accessory_group')->sortBy('category_name');
        $categories = collect();
        foreach ($categories_unique as $category) {
            $category->products = $product_accessories->where('id_accessory_group', $category->id_accessory_group);
            $categories->push($category);
            dd($product, $categories);
        }
        dd($product, $categories);

        $data = [
            'id' => $input['id'],
            'product' => $product,
//            'product_accessories' => $product_accessories,
            'categories' => $categories,
            'abra_kadabra' => $abra_kadabra,
        ];
        dd($data);



//        /*
//         * LiqPay
//         */
//        $send_invoice = LiqPayController::sendInvoice(522750680, 1045);
//        dd($send_invoice);
//
//        $action_product = PrestaShop_Product::getProductById(372);
//        dd($action_product);
//
//
//        /*
//         * Для рассылки
//         */
//        $orders = BotOrdersNew::where('created_at', '>=', '2023-02-01')->get();
//        $users = $orders->unique('user_id');
//        $users_arr = [];
//        foreach ($users as $user) {
//            $users_arr[$user->user_id] = $orders->where('user_id', $user->user_id)->count();
//        }
//        arsort($users_arr);
//
//        echo 'Первые 100: <br />';
//        $i = 0;
//        foreach ($users_arr as $user_id => $quantity) {
//            $i++;
//            if ($i <= 100)
//                echo $user_id.'<br />';
//        }
//
//        echo '<br />Полностью все: <br />';
//        $i = 0;
//        foreach ($users_arr as $user_id => $quantity) {
//            $i++;
//            echo $i.') '.$user_id. ' заказов: '.$quantity.'<br />';
//        }
//        dd($orders, $users, $users_arr);

//        $feedback_users = BotFeedBackController::sendFeedBack();
//        dd($feedback_users);

//        $new_cart_rule = new PrestaShop_Cart_Rule;
//        $new_cart_rule->date_from = date("Y-m-d 00:00:00");
//        $new_cart_rule->date_to = date("Y-m-d 23:59:59");
//        $new_cart_rule->date_add = date("Y-m-d H:i:s");
//        $new_cart_rule->date_upd = date("Y-m-d H:i:s");
//        $new_cart_rule->description = 'CashBack Test';
//        $new_cart_rule->reduction_amount = 0.01;
//        $new_cart_rule->save();
//
//        $langs = PrestaShop_Lang::where('active', 1)->get();
//        foreach ($langs as $lang) {
//            $new_cart_rule_lang = new PrestaShop_Cart_Cart_Rule_Lang;
//            $new_cart_rule_lang->id_cart_rule = $new_cart_rule->id;
//            $new_cart_rule_lang->id_lang = $lang->id_lang;
//            $new_cart_rule_lang->name = 'CashBack Test';
//            $new_cart_rule_lang->save();
//        }
//        dd($new_cart_rule);

//        $new_cart_rule_lang = new PrestaShop_Cart_Cart_Rule_Lang;

//        $orders = BotOrderOld::orderBy('id', 'asc')->get();
//        foreach ($orders as $order) {
//            $order_new = new BotOrdersNew;
//            $order_new->id = $order->id;
//            $order_new->external_id = $order->simpla_id;
//            $order_new->user_id = $order->user_id;
//            $order_new->name = $order->order_name;
//            $order_new->address = $order->order_addr;
//            $order_new->phone = $order->order_phone;
//            $order_new->price_all = $order->order_price;
//            $order_new->price_with_discount = $order->order_price;
//            $order_new->delivery_name = $order->order_delivery;
//            $order_new->delivery_date = $order->order_delivery_date;
//            $order_new->delivery_time = $order->order_delivery_time;
//            $order_new->pay = $order->order_oplata;
//            $order_new->payment_id = $order->order_payment_id;
//            $order_new->comment = $order->order_comment;
//            $order_new->pay_yes = $order->order_oplata_yes;
//            $order_new->cashback_cron = $order->order_yes;
//            $order_new->return_cashback  = $order->order_return_cashback;
//            $order_new->sent_archi = $order->sent_archi;
//            $order_new->created_at = $order->order_date_reg;
//            $order_new->updated_at = $order->order_date_edit;
//            $order_new->save();
//        }
//        $order_content = BotOrders::orderBy('id', 'asc')->get();
//        foreach ($order_content as $product_content) {
//            $new_order_content = new BotOrderContent;
//            $new_order_content->id = $product_content->id;
//            $new_order_content->order_id = $product_content->id_order;
//            $new_order_content->user_id = $product_content->id_user;
//            $new_order_content->category = $product_content->category;
//            $new_order_content->parent_product_id = $product_content->parent_product_id;
//            $new_order_content->product_id = $product_content->id_tovar;
//            $new_order_content->variant_id = $product_content->id_size;
//            $new_order_content->product_name = $product_content->product_name;
//            $new_order_content->variant_name = $product_content->variant_name;
//            $new_order_content->quantity = $product_content->quantity;
//            $new_order_content->price = $product_content->price;
//            $new_order_content->price_all = $product_content->price_all;
//            $new_order_content->vendor_code = $product_content->vendor_code;
//            $new_order_content->action_pizza = $product_content->action_pizza;
//            $new_order_content->product_present = $product_content->product_present;
//            $new_order_content->created_at = $product_content->date_reg;
//            $new_order_content->updated_at = $product_content->date_reg;
//            $new_order_content->save();
//        }
//        dd($orders);
//
//
//
//        $carts = BotCart::orderBy('id', 'asc')->get();
//        foreach ($carts as $cart) {
//            BotRaffleUsers::where('user_id', $cart->id_user)->update(['win' => 1]);
//            BotCart::where('id_user', $cart->id_user)->delete();
//        }
//        dd($cart);


        $product_attributes = PrestaShop_Product_Attribute::all();
        $products = PrestaShop_Product::join('ps_product_lang', 'ps_product_lang.id_product', 'ps_product.id_product')
            ->where('ps_product.id_category_default', 41)
            ->where('ps_product_lang.id_lang', 2)
            ->get();
        foreach ($products as $product) {
            $product_attribute = $product_attributes->where('id_product', $product->id_product)->where('default_on', 1)->first();
            if (!$product_attribute)
                $product_attribute = $product_attributes->where('id_product', $product->id_product)->first();
            $product->attribute = $product_attribute;
            $product->price = $product_attribute ? $product_attribute->price : 0;
        }
        dd($products);

        $order = PrestaShop_Orders::where('id_order', 903)->first();
        $kb = PrestaShop_Cart_Cart_Rule::join('ps_cart_rule', 'ps_cart_rule.id_cart_rule', 'ps_cart_cart_rule.id_cart_rule')
            ->where('ps_cart_cart_rule.id_cart', $order->id_cart)
            ->where('ps_cart_rule.description', 'like', 'CashBack Pay, cart:%')
            ->select('ps_cart_rule.reduction_amount')
            ->first();
        dd($order, $kb);


        $products = PrestaShop_Product::join('ps_product_lang', 'ps_product_lang.id_product', 'ps_product.id_product')
            ->join('ps_image', 'ps_image.id_product', 'ps_product.id_product')
            ->where('ps_product.id_category_default', 38)
            ->where('ps_product_lang.id_lang', 2)
            ->where('ps_image.cover', 1)
            ->get();
        foreach ($products as $product)
        {
            if (isset($product->id_image) && $product->id_image > 0) {
                $addr = config('services.prestashop.url')."/api/images/products/".$product->id_product."/".$product->id_image."/cart_default?ws_key=".config('services.prestashop.key')."&output_format=JSON&display=full";
//                dd($addr);
                $content = file_get_contents($addr);
                if ($content) $save_file = file_put_contents(public_path().'/assets/img/thumb/cart_default_'.$product->id_product.'.webp', $content);
            }
        }
        dd($products);



        try {
//            $cashback_add = CashBackController::addCashbackNew();
//            dd($cashback_add);
//            $cashback_return = CashBackController::returnCashbackNew();
//            dd($cashback_return);
            $webService = new PrestaShopWebserviceController('https://stage.ecopizza.com.ua', config('services.prestashop.key'));

//            $xml = $webService->get(array('url' => 'https://stage.ecopizza.com.ua/'.'api/cart_rules/?schema=blank'));
//            dd($xml);
//            $xml->children()->children()->name->language = 'CashBack Test';
//            $xml->children()->children()->date_from = date("Y-m-d");
//            $xml->children()->children()->date_to = date("Y-m-d");
//            $xml->children()->children()->reduction_amount = 0.01;
//            $opt = array('resource' => 'cart_rules');
//            $opt['postXml'] = $xml->asXML();
//            dd($opt);
//            $xml_cart_rule = $webService->add($opt);
//
//            dd($xml_cart_rule);
//
//            $new_cart_rule = $webService->add(array('url' => 'https://stage.ecopizza.com.ua/api/cart_rules'));

//            $bot_orders = BotOrdersNew::getOrdersForCashBack();
//            $orders_array = [];
//            $current_state = 5;
//            foreach ($bot_orders as $bot_order) {
//                array_push($orders_array, $bot_order->external_id);
//            }
//            $presta_orders = PrestaShop_Orders::whereIn('id_order', $orders_array)->where('current_state', $current_state)->get();
//            foreach ($presta_orders as $order) {
//                $bot_order = $bot_orders->where('external_id', $order->id_order)->first();
//                $cashback_percent = BotSettingsCashback::get_cashback_percent();
//                $cashback_percent = $cashback_percent !== null && $cashback_percent > 0 ? $cashback_percent : 7;
//                $cashback_add = bcmul(bcdiv($bot_order->price_for_cashback, 100, 10), $cashback_percent, 2);
//                if ($cashback_add > 0) {
//                    $balance_old = BotCashbackController::getUserCashback($bot_order->user_id);
//                    $balance_new = bcadd($balance_old, $cashback_add, 2);
//                    if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== '' && $_SERVER['REMOTE_ADDR'] !== null) $ip = $_SERVER['REMOTE_ADDR'];
//                    else $ip = '';
//
//                    $user_cashback_history = new BotCashbackHistory;
//                    $user_cashback_history->admin_login = 'BOT';
//                    $user_cashback_history->user_id = $bot_order->user_id;
//                    $user_cashback_history->order_id = $bot_order->id;
//                    $user_cashback_history->type = 'IN';
//                    $user_cashback_history->summa = $cashback_add;
//                    $user_cashback_history->descr = 'Начисление за заказ № '.$bot_order->external_id;
//                    $user_cashback_history->balance_old = $balance_old;
//                    $user_cashback_history->balance = $balance_new;
//                    $user_cashback_history->ip = $ip;
//                    $user_cashback_history->date_z = date("Y-m-d H:i:s");
//                    $user_cashback_history->save();
//
//                    BotUser::where('user_id', $bot_order->user_id)->update(['cashback' => $balance_new, 'updated_at' => date("Y-m-d H:i:s")]);
//                    BotOrdersNew::where('id', $bot_order->id)->update(['cashback_cron' => 1]);
//                    echo 'order_id: '.$bot_order->id.'; user_id: '.$bot_order->user_id.'; cashback_add: '.$cashback_add.'; price_for_cashback: '.$bot_order->price_for_cashback.'<br />';
//                }
//            }
//            dd($bot_orders, $orders_array, $presta_orders);


            $cart_rules = PrestaShop_Cart_Rule::where('description', 'like', 'CashBack Pay, cart:%')->get();
            foreach ($cart_rules as $cart_rule) {
                $delete_rule_lang = PrestaShop_Cart_Rule_Lang::where('id_cart_rule', $cart_rule->id_cart_rule)->delete();
                $delete_rule = PrestaShop_Cart_Rule::where('id_cart_rule', $cart_rule->id_cart_rule)->delete();
            }
            dd($cart_rules);


            $cashback_categories = PrestaShop_Cashback_Categories::all();
            $array = [];
            foreach ($cashback_categories as $category) {
                array_push($array, $category->category_id);
            }
            dd($cashback_categories, $array);

            $user_id = 522750680;
            $products = BotCartNew::getProductsByUserId($user_id);
            $cart_price_all = $products->sum('price_all');

            $cart_rules = $webService->get(array('url' => 'https://stage.ecopizza.com.ua/api/cart_rules?display=full&language='.self::$language), true);
            $cart_rules = json_decode($cart_rules);
            $cart_rules = collect($cart_rules->cart_rules);

            $discount_all = 0;
            $price_with_discount = $cart_price_all;

//            $price_all = bcadd($price_all, $product->price_all, 2);

            $discounts = [1, 4];

            if ($discounts) {
                foreach ($products as $product) {
                    foreach ($discounts as $discount_id) {
                        $cart_rule = $cart_rules->where('id', $discount_id)->first();
                        $categories_to_discount = PrestaShop_Cart_Rule_Product_Rule_Group::findRuleByIdCartRule($discount_id);
                        print '<br />'.$product->product_name.': '.'<br />';
                        if ($categories_to_discount->where('id_item', $product->category_id)->count() > 0) {
                            $percent = bcadd($cart_rule->reduction_percent, 0, 2);
                            $discount = bcmul(bcdiv($product->price_all, 100, 10), $percent, 10);
                            $product->price_all = bcsub($product->price_all, $discount, 2);
                            $price_with_discount = bcsub($price_with_discount, $discount, 10);
                            $discount_all = bcadd($discount_all, $discount, 10);
                            print('percent: '.$percent.'; price all: '.$product->price_all.'; discount: '.$discount.'; price with discount: '.$price_with_discount.'; discount all: '.$discount_all);
                        }
                    }
                }
                $discount_all = bcadd($discount_all, 0, 2);
                $price_with_discount = bcadd($price_with_discount, 0, 2);
            }

            print '<br />'.'Price all: '.$cart_price_all.'<br />';
            print 'Discount all: '.$discount_all.'<br />';
            print 'Price_with_discount: '.$price_with_discount.'<br />';

            dd($discount_all);


        } catch (PrestaShopWebserviceExceptionController $e) {
            dd($e);
            $data = [
                'category_select' => 1,
                'products' => null,
            ];
            return view('telegram.products', $data);
        }


//        $str = Str::random(12);
//        dd($str);

//        $product = ApiPrestaShopController::sendResponse(['resource' => 'products', 'url' => 'products/14'])->first();
//        $product_options = ApiPrestaShopController::sendResponse(['resource' => 'product_option_values', 'url' => 'product_option_values']);
//        $product_combinations = ApiPrestaShopController::sendResponse(['resource' => 'combinations', 'url' => 'combinations']);
//
//        $categories_all = ApiPrestaShopController::sendResponse(['resource' => 'categories', 'url' => 'categories']);
//        $products = ApiPrestaShopController::sendResponse(['resource' => 'products', 'url' => 'products']);
//        $product_ingredients = ApiPrestaShopController::sendResponse(['resource' => 'products', 'url' => 'product_accessory_id/14']);
//
//        $ingredients = collect();
//        foreach ($product_ingredients as $product_ingredient) {
//            $ingredients->push($products->where('id', $product_ingredient->id)->first());
//        }
//        $categories = collect();
//        foreach ($ingredients->unique('id_category_default') as $ingredient) {
//            $categories->push($categories_all->where('id', $ingredient->id_category_default)->first());
//        }
//        $categories = $categories->sortBy('name');
//        foreach ($categories as $category) {
//            $category->products = $ingredients->where('id_category_default', $category->id)->sortBy('name');
//            foreach ($category->products as $category_product) {
//                $price = bcmul($product_combinations->where('id', $category_product->associations->combinations[0]->id)->first()->price, 1, 2);
//                $category_product->price = $price;
//            }
//        }
//
//        $product->product_feature = '';
//        if (isset($product->associations)) {
//            $associations = $product->associations;
//            if (isset($associations->product_features)) {
//                $product_feature_id = collect($associations->product_features)->first()->id_feature_value;
//                $product_features = ApiPrestaShopController::sendResponse(['resource' => 'product_feature_values', 'url' => 'product_feature_values/'.$product_feature_id])->first();
//                $product->product_feature = $product_features->value;
//            }
//            if (isset($associations->combinations)) {
//                $product = self::manipulationWithCombinations($product, $associations, $product_combinations, $product_options);
//            }
//        }
//        dd($product);



//        $categories_all = ApiPrestaShopController::sendResponse(['resource' => 'categories', 'url' => 'categories']);
//        $products = ApiPrestaShopController::sendResponse(['resource' => 'products', 'url' => 'products']);
//        $product_ingredients = ApiPrestaShopController::sendResponse(['resource' => 'products', 'url' => 'product_accessory_id/14']);
//
//        $ingredients = collect();
//        foreach ($product_ingredients as $product_ingredient) {
//            $ingredients->push($products->where('id', $product_ingredient->id)->first());
//        }
//
//        $categories = collect();
//        foreach ($ingredients->unique('id_category_default') as $ingredient) {
//            $categories->push($categories_all->where('id', $ingredient->id_category_default)->first());
//        }
//        $categories = $categories->sortBy('name');
//
//        foreach ($categories as $category) {
//            $category->products = $ingredients->where('id_category_default', $category->id)->sortBy('name');
//        }
//        dd($categories);


        try {
            $webService = new PrestaShopWebserviceController('https://stage.ecopizza.com.ua', config('services.prestashop.key'), true);
//            $result = $webService->get(array('resource' => 'customers', 'display' => 'full'));

//            $result = simplexml_load_string($result);
//            $result = json_encode($result);
//            $result = json_decode($result);

//            $xml = $webService->get(array('url' => 'https://stage.ecopizza.com.ua/'.'api/carts/?schema=blank'));
//            $xml->cart->id                  = 1879;
//            $xml->cart->id_customer         = 113; // ID покупателя
//            $xml->cart->id_address_delivery = 1156; // ID адреса доставки
//            $xml->cart->id_currency         = 1;
//            $xml->cart->id_lang             = 2;
//
//            $id_customization = self::createCustomization($webService, 1879, 1156, 43, 51, 2);
//            print($id_customization);
//
//            $xml->cart->associations->cart_rows->cart_row->id_product            = 43;
//            $xml->cart->associations->cart_rows->cart_row->id_product_attribute  = 51;
//            $xml->cart->associations->cart_rows->cart_row->id_address_delivery   = 1156;
//            $xml->cart->associations->cart_rows->cart_row->id_customization      = $id_customization;
//            $xml->cart->associations->cart_rows->cart_row->quantity              = 2;
//
//            $opt = array( 'resource' => 'carts', 'id' => 1879 );
//            $opt['putXml'] = $xml->asXML();
////            dd($opt);
//            $xml_cart = $webService->edit( $opt );
//
//            dd($xml_cart);


            $user_id = 522750680;
            $firstname = 'Тест12';
            $lastname = 'Тестович';
            $phone = 380955675764;
            $address = collect();
            $address->alias = 'вул. 1 травня, Будинок 2, Корпус 22, Під\'їзд 3, Поверх 5, Квартира 6';
            $address->street = 'вул. 1 травня';
            $address->build = 2;
            $address->corps = 22;
            $address->frontdoor = 3;
            $address->floor = 4;
            $address->flat = 5;

            $customer = self::checkCustomer($webService, $phone, $firstname, $lastname);
            if (isset($customer->id) && $customer->id > 0) {
                $id_address = self::checkAddress($webService, $customer, $address);
                if ($id_address > 0) {

                    $cart = self::createCart($webService, $user_id, $customer->id, $id_address, 1);
                    $data_order = [
                        'webService' => $webService,
                        'user_id' => $user_id,
                        'id_customer' => $customer->id,
                        'id_address' => $id_address,
                        'date' => date("d-m-Y"),
                        'time' => "21:00",
                        'cart' => $cart,
                        'delivery_type' => 6,
                        'payment_type' => 'cash',
                        'discount_id' => 0,
                        'without_call' => 1,
                    ];
//                    $order = self::createOrder($webService, $user_id, $customer->id, $id_address, $cart);
                    $order = self::createOrder($data_order);
                    if ($order) {
                        $order_id = $order->order->id;

                        $xml = $webService->get(array('url' => 'https://stage.ecopizza.com.ua/'.'api/customer_threads/?schema=blank'));
                        $xml->customer_thread->id_lang = 2;
                        $xml->customer_thread->id_order = $order_id;
                        $xml->customer_thread->id_customer = $customer->id;
                        $xml->customer_thread->id_contact = 0;
                        $xml->customer_thread->token = Str::random(12);;
                        $opt = array(
                            'resource' => 'customer_threads',
                            'postXml' => $xml->asXML(),
                        );
                        $xml = $webService->add($opt);
                        $id_customer_thread = $xml->customer_thread->id;

                        $xml = $webService->get(array('url' => 'https://stage.ecopizza.com.ua/'.'api/customer_messages/?schema=blank'));
                        $xml->customer_message->id_customer_thread = $id_customer_thread;
                        $xml->customer_message->message = 'Тестовый комментарий';
                        $opt = array(
                            'resource' => 'customer_messages',
                            'postXml' => $xml->asXML(),
                        );
                        $xml = $webService->add($opt);

                        return $xml;
                    }
                }
            }

        } catch (PrestaShopWebserviceExceptionController $e) {
            dd($e);
            return 'error';
        }
    }

    public static function testCreateOrder(LRequest $request)
    {
        $input = $request->except('_token');
        Log::debug('testCreateOrder input', ['input' => $input]);

        try {
            $webService = new PrestaShopWebserviceController(config('services.prestashop.url'), config('services.prestashop.key'), false);
            $user_id = isset($input['user_id']) && $input['user_id'] !== null && $input['user_id'] > 0? $input['user_id'] : 522750680;
            $firstname = $input['name'];
            $lastname = ' ';
            $phone = $input['phone'];
            $phone = str_replace("+", "", $phone);
            $phone = str_replace("(", "", $phone);
            $phone = str_replace(")", "", $phone);
            $phone = str_replace("-", "", $phone);

            $address = collect();
            $address->corps = $input['corps'];
            $address->frontdoor = $input['front_door'];
            $address->floor = $input['floor'];
            $address->flat = $input['flat'];

            if ($input['takeaway'] == 1) {
                $address->alias = $input['city'].', вул. Старокозацька, Будинок 66А';
                $address->street = 'вул. Старокозацька';
                $address->build = '66А';
                $input['delivery_type'] = 8;
            }
            else {
                $address->alias = $input['city'].', '.$input['street'].', Будинок '.$input['build'];
                if ($input['corps'] !== null && $input['corps'] !== '') $address->alias .= ', Корпус '.$input['corps'];
                if ($input['front_door'] !== null && $input['front_door'] !== '') $address->alias .= ', Під\'їзд '.$input['front_door'];
                if ($input['floor'] !== null && $input['floor'] !== '') $address->alias .= ', Поверх '.$input['floor'];
                if ($input['flat'] !== null && $input['flat'] !== '') $address->alias .= ', Квартира '.$input['flat'];
                $address->street = $input['street'];
                $address->build = $input['build'];
                $input['delivery_type'] = 6;
            }
            Log::debug('Address for order', ['address' => $address]);

            $discounts = isset($input['discounts']) && $input['discounts'] != null && is_array(json_decode($input['discounts'], true)) ? json_decode($input['discounts'], true) : null;
            $cashback_pay = isset($input['cashback_pay']) && $input['cashback_pay'] != null && $input['cashback_pay'] != '' && $input['cashback_pay'] > 0 ? $input['cashback_pay'] : 0;
            $cashback_pay = bcadd($cashback_pay, 0, 2);

            Log::info('cashback = '.$cashback_pay);

            $change_from = isset($input['change_from']) && $input['change_from'] !== null ? $input['change_from'] : 0;
            $without_call = isset($input['without_call']) && $input['without_call'] !== null ? $input['without_call'] : 0;

            $customer = self::checkCustomer($webService, $phone, $firstname, $lastname);
            if (isset($customer->id) && $customer->id > 0) {
                $id_address = self::checkAddress($webService, $customer, $address);
                if ($id_address > 0) {
                    $cart = self::createCart($webService, $user_id, $customer->id, $id_address, $discounts, $input['comment'], $input['delivery_type'], $cashback_pay);
                    $data_order = [
                        'webService' => $webService,
                        'user_id' => $user_id,
                        'name' => $firstname,
                        'id_customer' => $customer->id,
                        'id_address' => $id_address,
                        'takeaway' => $input['takeaway'],
                        'date' => $input['date'],
                        'time' => $input['time'],
                        'cart' => $cart,
                        'delivery_type' => $input['delivery_type'],
                        'payment_type' => $input['payment_type'],
                        'discounts' => $discounts,
                        'price_for_cashback' => $input['price_for_cashback'],
                        'cashback_pay' => $cashback_pay,
                        'change_from' => $change_from,
                        'without_call' => $without_call,
                        'address' => $address->alias,
                        'phone' => $phone,
                        'comment' => $input['comment'],
                    ];
                    log::info($data_order);
                    $order_id = self::createOrder($data_order);
                    log::info('PRESTA ORDER ID: '.$order_id);
                    if ($order_id && $order_id > 0) {

                        $current_state = 24;
                        if ($input['payment_type'] == 'card') $current_state = 15;
                        PrestaShop_Orders::where('id_order', $order_id)->update(['current_state' => $current_state]);
                        PrestaShop_Order_History::where('id_order', $order_id)->update(['id_order_state' => $current_state]);

                        $moduleAndPayment = self::getModuleAndPayment($input['payment_type']);
                        $text = 'Ваш заказ №'.$order_id.' прийнято!'.PHP_EOL;

                        $order = BotOrdersNew::where('external_id', $order_id)->first();
                        $order_content = BotOrderContent::where('order_id', $order->id)->get();
                        foreach ($order_content->where('parent_product_id', null) as $product) {
                            $text .= PHP_EOL.'<b>'.$product->product_name.'</b>';
                            if ($order_content->where('parent_product_id', $product->id)->count() > 0) {
                                $text .= PHP_EOL;
                                foreach ($order_content->where('parent_product_id', $product->id) as $ingredient) {
                                    $product->price = bcadd($product->price, bcdiv($ingredient->price_all, $product->quantity, 2), 2);
//                                    $text .= '<code>  '.$ingredient->product_name.' '.$ingredient->price.' x '.$ingredient->quantity.' = '.$ingredient->price_all.' грн</code>'.PHP_EOL;
                                    $text .= '<code>+'.$ingredient->product_name.'</code> ';
                                }
                            }
                            $product->price_all = bcmul($product->price, $product->quantity, 2);
                            $text .= PHP_EOL.'<code>'.$product->price.' x '.$product->quantity.' = '.$product->price_all.' грн</code>'.PHP_EOL;
                        }
                        $text .= PHP_EOL.'<b>Доставка: </b>'.$order->delivery_name.PHP_EOL;
                        $text .= '<b>Адреса: </b>'.$order->address.PHP_EOL;
                        $text .= '<b>Телефон: </b>'.$order->phone.PHP_EOL;
                        $text .= '<b>Дата: </b>'.date("d.m.Y", strtotime($order->delivery_date)).PHP_EOL;
                        $text .= '<b>Час: </b>'.$order->delivery_time.PHP_EOL;
                        $text .= '<b>Сума замовлення: </b>'.$order->price_all.' грн'.PHP_EOL;
                        $text .= '<b>Сума знижки: </b>'.$order->discount.' грн'.PHP_EOL;
                        $text .= $order->cashback_pay > 0 ? '<b>Сплачено кешбеком: </b>'.$order->cashback_pay.' грн'.PHP_EOL : '';
                        $text .= '<b>Разом до сплати: </b>'.$order->price_with_discount.' грн'.PHP_EOL;

//                        $products = BotCartNew::where('user_id', $user_id)->where('parent_product_id', null)->orderBy('id', 'asc')->get();
//                        foreach ($products as $product) {
//                            $text .= '<b>'.$product->product_name.'</b>'.PHP_EOL.'<code>'.$product->price.' x '.$product->quantity.' = '.$product->price_all.' грн</code>'.PHP_EOL;
//                        }

                        $delete_products_in_cart = BotCartNew::where('user_id', $user_id)->delete();
                        if (BotRaffleCherryTakeaway::countUnReceivedAndWinByUserId($user_id) > 0) {
                            $user_cherry_takeaway = BotRaffleCherryTakeaway::getCherryTakeawayByUserId($user_id);
                            $clear_win = BotRaffleCherryController::clearWinByUserId($user_id);
                            if ($user_cherry_takeaway && $user_cherry_takeaway->id) {
                                $cherry_bot = new TelegramBotCherryController;
                                $message_ids = json_decode($user_cherry_takeaway->message_ids);
                                $text_win = '✅ '.$user_cherry_takeaway->name.PHP_EOL.$user_cherry_takeaway->phone.PHP_EOL."Отримав чекушку П'яної вишні";

                                foreach ($message_ids as $admin_id => $admin_messsage_id) {
                                    $cherry_bot->editMessageText($admin_id, $admin_messsage_id, $text_win, []);
                                }
                            }
                        }
                        $data = ['chat_id' => $user_id];
                        $data['text'] = $text;
                        $data['parse_mode'] = 'html';
                        $send_text = \Longman\TelegramBot\Request::sendMessage($data);
                        if ($moduleAndPayment['module'] == 'wayforpay') {
                            $send_widget = WayForPayController::sendWidget($user_id, $order_id);
//                            $send_widget = LiqPayController::sendInvoice($user_id, $order_id);
                            Log::debug('Payment Widget sent', ['result' => $send_widget]);
                            return $send_widget;
                        }
                    }
                }
            }

        } catch (PrestaShopWebserviceExceptionController $e) {
            Log::error('PrestaShop order error: '.$e->getMessage());
            Log::error($e->getTraceAsString());
            return 'error';
        } catch (\Exception $e) {
            Log::error('Order creation error: '.$e->getMessage());
            Log::error($e->getTraceAsString());
            return 'error';
        }

//        dd($input);
    }

}
