<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiPrestaShopController;
use App\Models\BotCart;
use App\Models\BotCashbackHistory;
use App\Models\BotCashbackHistory2;
use App\Models\BotCashbackHistoryNew;
use App\Models\BotCashbackUsers;
use App\Models\BotEcoUser;
use App\Models\BotMenu;
use App\Models\BotOrder;
use App\Models\BotOrderContent;
use App\Models\BotOrders;
use App\Models\BotOrdersNew;
use App\Models\BotRaffle;
use App\Models\BotRaffleUsers;
use App\Models\BotSendingMessagesHistory;
use App\Models\BotStocks;
use App\Models\BotUser;
use App\Http\Controllers\Telegram\BotButtonsInlineController;
use App\Http\Controllers\Telegram\BotFeedBackButtonsController;
use App\Http\Controllers\Telegram\BotStickerController;
use App\Http\Controllers\Telegram\BotTextsController;
use App\Http\Controllers\Telegram\BotUsersNavController;
use App\Http\Controllers\Telegram\FeedBackCommandController;
use App\Models\PrestaShop_Category;
use App\Models\PrestaShop_Feature_Product;
use App\Models\PrestaShop_Product;
use App\Models\PrestaShop_Product_Attribute;
use App\Models\Simpla_Dubug_Products;
use App\Models\Simpla_Dubug_Products_Regions;
use App\Models\Simpla_Options;
use App\Models\SimplaOrders;
use Illuminate\Http\Request as LRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;

class TestController extends Controller
{

    public static function getNewUsers()
    {
        $date_start = '2024-01-01';
        $date_end = '2024-01-05';
        $users = BotUser::where('created_at', '>=', $date_start.' 00:00:00')->where('created_at', '<=', $date_end.' 23:59:59')->get();
        $orders = BotOrdersNew::where('created_at', '>=', $date_start.' 00:00:00')->where('created_at', '<=', $date_end.' 23:59:59')->get();

        foreach ($users as $user) {
            echo $user->created_at.'; '.$user->user_id.'; '.$orders->where('user_id', $user->user_id)->count().'; '.$orders->where('user_id', $user->user_id)->sum('price_with_discount').'<br />';
        }
        dd($users, $orders);

    }


    public static function testReturnCashback2023()
    {
        $category_select  = 3;
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
        }
        $products_categories = $products->unique('category_name')->sortBy('category_name');

        dd($products_categories);

        $history = BotCashbackHistory::where('date_z', '>=', '2023-07-27 00:00:00')->orderBy('id')->get();
        $history_old = BotCashbackHistory::where('date_z', '<', '2023-07-27 00:00:00')->orderBy('id', 'desc')->get();
        $history_in = $history->where('type', 'IN')->count();
        $history_out = $history->where('type', 'OUT')->count();
        $history_clients = $history->unique('user_id');

        print '<table border="1">';

        $n = 0;
        foreach ($history_clients as $history_client) {
            $n++;
            print '<tr>';
            print '<td>';
            print $n.') ';
            print '<table border="1">';
            print '<tr>
                    <td>user_id</td>
                    <td>type</td>
                    <td>order</td>
                    <td>summa</td>
                    <td>balance old</td>
                    <td>balance_new</td>
                    <td>date</td>
                  </tr>';
            foreach ($history->where('user_id', $history_client->user_id) as $item) {
                print '<tr>';
                print '<td>'.$item->user_id.'</td><td>'.$item->type.'</td><td>'.$item->order_id.'</td><td>'.$item->summa.'</td><td>'.$item->balance_old.'</td><td>'.$item->balance.'</td><td>'.$item->date_z.'</td>';
                print '</tr>';
            }
            print '</table>';
//            if ($history->where('user_id', $history_client->user_id)->count() == 1) {
//                echo $history_client->user_id.'<br />';
//            }
//            else {
//                foreach ($history->where('user_id', $history_client->user_id) as $item) {
//                    echo ' * '.$item->user_id.' - '.$item->type.' - '.$item->date_z.'<br />';
//                }
////                dd($history->where('user_id', $history_item->user_id));
//            }

            print '</td>';
            print '</tr>';
        }
        print '</table>';
        dd($history, $history_in, $history_out, $history_clients);
    }


    public static function showTestGame(LRequest $request)
    {
        $input = $request->except('_token');
        $category_select = isset($input['category_select']) && $input['category_select'] !== null ? $input['category_select'] : 3;
//        $categories = BotMenu::where('enabled', 1)->orderBy('menu_sort', 'asc')->get();
////        $products_response = Http::get('https://api.ecopizza.com.ua/test_presta', []);
//        $resource = 'products';
//        $products = ApiPrestaShopController::sendResponse($resource);
////        dd($products);
//        $products = $products->$resource;
//        $products = collect($products);
//        $products = $products->where('id_category_default', 3)->where('active', 1)->sortBy('position_in_category');
////        foreach ($products as $product)
////        {
////            $content = file_get_contents("https://NXUAQSSLS82SFTVBMXCBMCFIFRPVWJZR@stage.ecopizza.com.ua/api/images/products/".$product->id."/".$product->id_default_image."/small_default?ws_key=NXUAQSSLS82SFTVBMXCBMCFIFRPVWJZR&output_format=JSON&display=full");
////            $save_file = file_put_contents(public_path().'/assets/img/thumb/'.$product->id.'.webp',$content);
////            if ($save_file) echo $product->id.' - ok<br />';
////        }
////        dd($products);
////        if ($products_response->ok() && $products_response->successful()) {
////            $products = json_decode($products_response);
////        }
        $data = [
            'category_select' => $category_select,
//            'categories' => $categories,
//            'products' => $products,
        ];
        return view('telegram.menu', $data);
    }


    public static function sendTestGame()
    {
        $user_id = 522750680; // Я
//        $user_id = 190644023; // Сергей
//        $user_id = 329595353; // Юра
//        $user_id = 221559061; // Андрей
        $inline_keyboard = new InlineKeyboard([]);
//        $inline_keyboard->addRow(
//            new InlineKeyboardButton(['text' => 'Тест Гейм', 'callback_game' => 'testGame'])
//        );
        $inline_keyboard->addRow(
            new InlineKeyboardButton(['text' => '🍴 Меню', 'web_app' => ['url' => route('show_new_menu', ['user_id' => $user_id])]])
        );
        $data = ['chat_id' => $user_id];
        $data['text'] = 'Нове меню';
//        $data['game_short_name'] = 'testGame';
        $data['reply_markup'] = $inline_keyboard;
        $send = Request::sendMessage($data);
        return $send;
    }

    public static function sendDice()
    {
        $user = \App\Models\User::find(1);
        dd($user->hasRole('admin'), $user->hasPermission('admin_panel'));
        $user_id = 522750680;
//        $user_id = 190644023;
        $data = ['chat_id' => $user_id];
        $data['emoji'] = '🎳';
        $send = Request::sendDice($data);
        if ($send->getOk()) {
            $result = $send->result;
            $dice = $result->dice;
            $data = ['chat_id' => $user_id];
            $data['text'] = 'Выпало: '.$dice['value'].' '.$dice['emoji'];
            $send_message = Request::sendMessage($data);
        }
    }

    public static function removeActionPizzas()
    {
        $cart_users = BotCart::where('action_pizza', 1)->get();
        $i = 0;
        foreach ($cart_users as $cart_user) {
            $i++;
            $user_id = $cart_user->id_user;
            $update_win = BotRaffleUsers::where('user_id', $user_id)->update(['win' => 1]);
            if ($update_win) {
                $update_cart = BotCart::where('id_user', $user_id)->where('action_pizza', 1)->delete();
                if ($update_cart) echo $i.') '.$user_id.' - пиццу удалил<br />';
                else echo $i.') '.$user_id.' - ошибка<br />';
            }
        }
    }

    public static function testActiveUsers()
    {
        $result = BotSendingMessagesHistory::find(1086003)->result;
        $result = json_decode($result, true);
        dd($result);
        return '123';
    }

    public static function testFeedback()
    {
        $user_id = 522750680;
        $send = FeedBackCommandController::execute($user_id);
        if ($send == true) Log::info($user_id.' - ok');
        else Log::info($user_id.' - error');

//        $messages = BotSendingMessagesHistory::where('date_z', 'like', '2022-08-09%')->get();
//        foreach ($messages as $message) {
//            $result = json_decode($message->result);
//            if ($result->ok == false) {
//                if (BotUser::where('user_id', $message->user_id)->count() == 1) {
//                    $updateBotUser = BotUser::where('user_id', $message->user_id)->update(['active' => 0]);
//                }
//            }
//        }
//        return 'ok';

    }

    public static function checkUsersCart()
    {
        $minutes = 2;
        $products = BotCart::leftJoin('bot_users_nav', 'bot_users_nav.user_id', 'bot_cart.id_user')
            ->where('action_pizza', 0)
            ->where('product_present', 0)
            ->get();
        $users = $products->unique('id_user');
//        dd($users);
        foreach ($users as $user) {

            $user_id = 522750680;

            $inline_keyboard = new InlineKeyboard([]);

            $time_last_edit = $products->where('id_user', $user->id_user)->sortByDesc('date_edit')->first()['date_edit'];
            $time_z = strtotime($time_last_edit."+".$minutes."minutes");
            if (time() > $time_z && $user->id_user == $user_id) {

                if (!isset($user->send_reminder) || $user->send_reminder == null) {
                    $text = BotTextsController::getText($user_id, 'Reminder', 'show1');

                    list($text_button, $data_button) = BotButtonsInlineController::getButtonInline($user_id, 'Cart', 'cart');
                    $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text_button, 'callback_data' => $data_button]) );
                    list($text_button, $data_button) = BotButtonsInlineController::getButtonInline($user_id, 'Menu', 'menu');
                    $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $text_button, 'callback_data' => $data_button]) );

                    BotUsersNavController::updateValue($user_id, 'send_reminder', 1);
                    BotUsersNavController::updateValue($user_id, 'send_reminder_datetime', date("Y-m-d H:i:s"));
                    BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'order_message_id', null);
                    BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'cart_message_id', null);
                    $data = ['chat_id' => $user_id];
                    $data['text'] = $text;
                    $data['reply_markup'] = $inline_keyboard;
                    $result = Request::sendMessage($data);
                    dd($result);
                }
                elseif (isset($user->send_reminder) && $user->send_reminder == 1) {
                    $time_reminder_z = strtotime($user->send_reminder_datetime."+".$minutes."minutes");
                    if (time() > $time_reminder_z) {
                        $text = BotTextsController::getText($user_id, 'Reminder', 'show2');
                        $clear_cart = BotCart::where('id_user', $user_id)->where('action_pizza', 0)->where('product_present', 0)->delete();
                        BotUsersNavController::updateValue($user_id, 'send_reminder', null);
                        BotUsersNavController::updateValue($user_id, 'send_reminder_datetime', null);
                        BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'order_message_id', null);
                        BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'cart_message_id', null);
                        $data = ['chat_id' => $user_id];
                        $data['text'] = $text;
                        $data['reply_markup'] = $inline_keyboard;
                        $result = Request::sendMessage($data);
                    }
                }
            }

        }
        dd($products, $users);
        return true;
    }

    public static function simpla_add_product_regions()
    {
//        $products = Simpla_Dubug_Products_Regions::orderBy('product_id', 'asc')->get();
//        $products = $products->unique('product_id');
        $products = Simpla_Dubug_Products::where('visible', 1)->orderBy('id', 'asc')->get();
//        dd($products);
//        Simpla_Dubug_Products_Regions::where('region_id', 10)->orWhere('region_id', 11)->delete();
        $n = 0;
        foreach ($products as $product) {
            $n++;
            $region_id = 10;
            if (Simpla_Dubug_Products_Regions::where('product_id', $product->id)->where('region_id', $region_id)->count() == 0) {
                $position = 4;
                $item = new Simpla_Dubug_Products_Regions;
                $item->product_id = $product->id;
                $item->region_id = $region_id;
                $item->position = $position;
                $item->save();
            }
            $region_id = 11;
            if (Simpla_Dubug_Products_Regions::where('product_id', $product->id)->where('region_id', $region_id)->count() == 0) {
                $position = 5;
                $item = new Simpla_Dubug_Products_Regions;
                $item->product_id = $product->id;
                $item->region_id = $region_id;
                $item->position = $position;
                $item->save();
            }
            echo $n.') '.$product->id.'; <br />';
        }
    }

    public static function tttest_cashback()
    {
        $orders = SimplaOrdersController::getOrdersForCashBack();
        dd($orders);
    }


    public static function test_stock()
    {

        $stock = BotStocks::join('bot_stocks_algorithm', 'bot_stocks_algorithm.id', 'bot_stocks.algorithm_id')
            ->where('bot_stocks.id', 1)
            ->first([
                'bot_stocks.name',
                'bot_stocks.date_start',
                'bot_stocks.date_end',
                'bot_stocks_algorithm.name as algorithm_name',
                'bot_stocks_algorithm.descr as algorithm_descr',
                'bot_stocks_algorithm.function_name as algorithm_fumction_name'
            ]);

        $algorithm = $stock['algorithm_fumction_name'];

        print 'Акция: ' . $stock['name'] . '<br />'
            . 'Дата начала: ' . $stock['date_start'] . '<br />'
            . 'Дата окончания: ' . $stock['date_end'] . '<br />'
            . 'Алгоритм: ' . $stock['algorithm_name'] . ' (' . $stock['algorithm_descr'] . ')' . '<br />';

        print 'Result: ';
        return BotStocksAlgorithmController::$algorithm();

    }

    public static function get_phone_numbers() {

        $bot_orders = BotOrder::distinct('phone')->groupBy('phone')->orderBy('phone')->get();
        $bot_arr = [];
        foreach ($bot_orders as $bot_order) {
            if ($bot_order['phone'] !== '') {
                $phone = $bot_order['phone'];
                $phone = str_replace(" ", "", $phone);
                $phone = str_replace("(", "", $phone);
                $phone = str_replace(")", "", $phone);
                $phone = str_replace("-", "", $phone);
                $phone = str_replace("±", "", $phone);
                $phone = str_replace("+", "", $phone);
                $phone = str_replace("79215557646", "", $phone);
                if ($phone !== '') {

                    $phone = '+'.$phone;
                    $bot_arr[] = $phone;
//                    echo $phone.'<br />';

                }
            }
        }

//        dd($bot_orders);

        $simpla_orders = SimplaOrders::selectRaw('s_orders.*, COUNT(s_orders.id) as nums')
            ->where('date', '>=', '2020-04-01 00:00:00')
            ->where('date', '<=', '2020-04-30 23:59:59')
            ->where('status', 2)
            ->distinct('phone')
            ->groupBy('phone')
            ->orderBy('phone')
//            ->take(10)
            ->get();

//        dd($simpla_orders);
        $simpla_arr = [];
        print('Simpla: <br />');
        foreach ($simpla_orders as $simpla_order) {
            if ($simpla_order['phone'] !== '') {
                $phone = $simpla_order['phone'];
//                $n = SimplaOrders::where('phone', $phone)->where('status', 2)->count();
                $phone = str_replace(" ", "", $phone);
                $phone = str_replace("(", "", $phone);
                $phone = str_replace(")", "", $phone);
                $phone = str_replace("-", "", $phone);
                $phone = stripos($phone, '+38') !== false ? $phone : '+38'.$phone;
                if (!in_array($phone, $bot_arr)) $simpla_arr[] = ['phone' => $phone, 'nums' => $simpla_order['nums']];
//                echo $phone.' - '.$simpla_order['nums'].'<br />';
            }
        }
//        dd($simpla_orders);

        $count = count($simpla_arr);
        echo $count.'<br />';
        echo '<table>';
        foreach ($simpla_arr as $value) {
            echo '<tr><td>'.$value['phone'].'</td><td>'.$value['nums'].'</td></tr>';
        }
        echo '</table>';

    }

    public static function delete_duplicate_present_products() {

        $users = BotCart::where('product_present', 1)->groupBy('id_user')->get();

        $arr = [];
        foreach ($users as $user) {
            $count = BotCart::where('id_user', $user->id_user)->where('product_present', 1)->count();
            if ($count > 1) {
                $arr[] = $user->id;
//                BotCart::where('id', $user->id)->delete();
            }
        }
        echo 'users: '.count($users);
        dd($arr);

    }

    public static function test_show_orders_old_products() {

        $orders = BotOrderContent::leftJoin('s_products', 's_products.id', 'bot_order_content.product_id')
//            ->take(10)
//            ->where('s_products.visible', 1)
            ->groupBy('product_id')
            ->get(['bot_order_content.id_tovar as id_tovar', 'bot_order_content.product_name as product_name', 's_products.id as id', 's_products.name as name', 's_products.visible']);

        echo '<table>';
        foreach ($orders as $order) {

            echo '<tr>';
            echo '<td>'.$order->id_tovar.'</td>';
            echo '<td>'.$order->id.'</td>';
            echo '<td>'.$order->product_name.'</td>';
            echo '<td>'.$order->name.'</td>';
            echo '<td>'.$order->visible.'</td>';
            echo '</tr>';

        }
        echo '</table>';
        dd($orders);

    }

    public static function test_shedule() {

        $data_t = ['chat_id' => '522750680'];
        $data_t['text'] = 'Привет! Сейчас '.date("Y-m-d H:i:s");
        Request::sendMessage($data_t);

    }

    public static function show_cashback()
    {

        $users = BotCashbackUsers::join('bot_user', 'bot_user.user_id', 'bot_cashback_users.user_id')->get(['bot_cashback_users.user_id', 'bot_user.cashback', 'bot_cashback_users.sum']);
        $i = 0;
        $summa = 0;
        $ins = '';
        echo '<table>';
        foreach ($users as $user) {
            if ($user['cashback'] !== $user['sum']) {
                $i++;
                $summa += $user['sum'] - $user['cashback'];
                $bc1 = $user['cashback'] > $user['sum'] ? '<b>' : '';
                $bc2 = $user['cashback'] > $user['sum'] ? '</b>' : '';
                $bs1 = $user['cashback'] < $user['sum'] ? '<b>' : '';
                $bs2 = $user['cashback'] < $user['sum'] ? '</b>' : '';

////                $user_id = '522750680';
//                $user_id = $user['user_id'];
//                BotUser::where('user_id', $user_id)->update(['cashback' => $user['sum']]);
//
//                // Отправляем стикер приветствия
//                $data_sticker = ['chat_id' => $user_id];
//                $data_sticker['sticker'] = BotStickerController::getSticker($user_id, 'Start');
//                $data_sticker['reply_markup'] = Keyboard::remove(['selective' => true]);
//                $send_sticker = Request::sendSticker($data_sticker);

//                $razn = $user['sum'] - $user['cashback'];
//
//                $inline_keyboard = new InlineKeyboard([]);
//                list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Order', 'goto_start');
//                $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'callback_data' => $data]));
//
//                $text = 'Привет!
//Я конечно сильно извиняюсь🙄, но только что случайно заметил, что у тебя не правильно рассчитан кешбэк. Поэтому спешу начислить тебе недостающие '.$razn.' грн.
//Теперь у тебя на счету '.$user['sum'].' грн.
//Обещаю больше не ошибаться!😊
//Желаю тебе хорошего дня и отличного настроения!';
//
//                $data_text = ['chat_id' => $user_id];
//                $data_text['text'] = $text;
//                $data_text['parse_mode'] = 'html';
//                $data_text['reply_markup'] = $inline_keyboard;
//                $send_text = Request::sendMessage($data_text);
//
//                $result = json_decode($send_text);
//                $send = $result->ok == 1 ? 'отправлено!' : 'не отправлено!';

                $tr = '
                <tr>
                    <td style="padding: 5px; margin: 5px; width:200px;">'.$user['user_id'].'</td>
                    <td style="padding: 5px; margin: 5px; width:100px;">'.$bc1.$user['cashback'].$bc2.'</td>
                    <td style="padding: 5px; margin: 5px; width:100px;">'.$bs1.$user['sum'].$bs2.'</td>
                </tr>
                ';
                $ins .= $tr;
                echo $tr;
            }
            else echo '<tr><td style="padding: 5px; margin: 5px; width:200px;">'.$user['user_id'].'</td><td style="padding: 5px; margin: 5px; width:100px;">'.$user['cashback'].'</td><td style="padding: 5px; margin: 5px; width:100px;">'.$user['sum'].'</td></tr>';
        }
        echo '</table>';

        echo '<br /><b>'.$i.'<br />'.$summa.'</b><br />';
        echo '<table>';
        echo $ins;
        echo '</table>';

    }

    public static function test_cashback()
    {

        $day = '2019-06-27';
//        $orders = BotOrder::where('order_yes', 0)->where('order_date_reg', '>', $day.' 00:00:00')->orderBy('id', 'asc')->take(10)->get();
//        $orders = BotOrder::join('s_orders', 's_orders.id', 'bot_order.simpla_id')
////            ->where('bot_order.order_yes', 0)
//            ->where('bot_order.order_date_reg', '>', $day.' 00:00:00')
//            ->where('s_orders.status', 2)
//            ->orderBy('bot_order.id', 'asc')
////            ->take(100)
//            ->get(['bot_order.id', 'bot_order.simpla_id', 'bot_order.user_id', 'bot_order.order_price']);

        $ip = isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== '' && $_SERVER['REMOTE_ADDR'] !== null ? $_SERVER['REMOTE_ADDR'] : '';

        $historys = BotCashbackHistory::orderBy('id', 'asc')->get();
//        $historys = BotCashbackHistory::where('descr', 'not like', 'Оплата заказа №%')
//            ->where('descr', 'not like', 'Начисление за  заказ №%')
//            ->orderBy('id', 'asc')
//            ->get();
//        dd($historys);

        foreach ($historys as $history) {

//            echo $history['descr']."<br>";

            $descr = $history['descr'];
            if ($history['type'] == 'OUT' && stripos($descr, 'Оплата заказа №') !== false) {

                $simpla_id = str_replace("Оплата заказа № ", "", $descr);
                if (SimplaOrders::where('id', $simpla_id)->first()['status'] == 2) {

                    $order_id = BotOrder::where('simpla_id', $simpla_id)->first()['id'];

                    $balance_old = self::getUserBalance($history['user_id']);
                    $sum = $history['type'] == 'OUT' ? -1 * $history['summa'] : $history['summa'];
                    $balance = $balance_old + $sum;

                    $user = new BotCashbackHistoryNew;
                    $user->admin_login = 'BOT';
                    $user->user_id = $history['user_id'];
                    $user->order_id = $order_id;
                    $user->type = 'OUT';
                    $user->summa = $history['summa'];
                    $user->descr = 'Оплата заказа № '.$simpla_id;
                    $user->balance_old = $balance_old;
                    $user->balance = $balance;
                    $user->ip = $history['ip'];
                    $user->date_z = $history['date_z'];
                    $user->save();

                    self::cashbackUsers($history['user_id'], $history['type'], $history['summa']);

                }

            }
            elseif ($history['type'] == 'IN' && stripos($descr, 'Начисление за  заказ №') !== false) {

                $simpla_id = str_replace("Начисление за  заказ № ", "", $descr);

                if (SimplaOrders::where('id', $simpla_id)->first()['status'] == 2) {

                    $order_id = BotOrder::where('simpla_id', $simpla_id)->first()['id'];

                    $balance_old = self::getUserBalance($history['user_id']);
                    $sum = $history['type'] == 'OUT' ? -1 * $history['summa'] : $history['summa'];
                    $balance = $balance_old + $sum;

                    $user = new BotCashbackHistoryNew;
                    $user->admin_login = 'BOT';
                    $user->user_id = $history['user_id'];
                    $user->order_id = $order_id;
                    $user->type = 'IN';
                    $user->summa = $history['summa'];
                    $user->descr = 'Начисление за заказ № '.$simpla_id;
                    $user->balance_old = $balance_old;
                    $user->balance = $balance;
                    $user->ip = $history['ip'];
                    $user->date_z = $history['date_z'];
                    $user->save();

                    self::cashbackUsers($history['user_id'], $history['type'], $history['summa']);

                }

            }
            elseif ($history['type'] == 'IN' && stripos($descr, 'Розыгрыш от Св.Николая') !== false) {

                $balance_old = self::getUserBalance($history['user_id']);
                $sum = $history['type'] == 'OUT' ? -1 * $history['summa'] : $history['summa'];
                $balance = $balance_old + $sum;

                $user = new BotCashbackHistoryNew;
                $user->admin_login = 'BOT';
                $user->user_id = $history['user_id'];
                $user->type = 'IN';
                $user->summa = $history['summa'];
                $user->descr = $descr;
                $user->balance_old = $balance_old;
                $user->balance = $balance;
                $user->ip = $history['ip'];
                $user->date_z = $history['date_z'];
                $user->save();

                self::cashbackUsers($history['user_id'], $history['type'], $history['summa']);

            }
            elseif ($history['type'] == 'IN' && stripos($descr, 'Возврат Кб, из-за отказа клиентом по причине долгой доставки') !== false) {

                $balance_old = self::getUserBalance($history['user_id']);
                $sum = $history['type'] == 'OUT' ? -1 * $history['summa'] : $history['summa'];
                $balance = $balance_old + $sum;

                $user = new BotCashbackHistoryNew;
                $user->admin_login = 'BOT';
                $user->user_id = $history['user_id'];
                $user->type = 'IN';
                $user->summa = $history['summa'];
                $user->descr = $descr;
                $user->balance_old = $balance_old;
                $user->balance = $balance;
                $user->ip = $history['ip'];
                $user->date_z = $history['date_z'];
                $user->save();

                self::cashbackUsers($history['user_id'], $history['type'], $history['summa']);

            }
            elseif ($history['type'] == 'IN' && stripos($descr, 'Возврат по последнему заказу: 734-456=278') !== false) {

                $balance_old = self::getUserBalance($history['user_id']);
                $sum = $history['type'] == 'OUT' ? -1 * $history['summa'] : $history['summa'];
                $balance = $balance_old + $sum;

                $user = new BotCashbackHistoryNew;
                $user->admin_login = 'BOT';
                $user->user_id = $history['user_id'];
                $user->type = 'IN';
                $user->summa = $history['summa'];
                $user->descr = $descr;
                $user->balance_old = $balance_old;
                $user->balance = $balance;
                $user->ip = $history['ip'];
                $user->date_z = $history['date_z'];
                $user->save();

                self::cashbackUsers($history['user_id'], $history['type'], $history['summa']);

            }

        }


//        foreach ($orders as $order) {
//
//            $cashback = bcmul(bcdiv($order['order_price'], 100, 2), 7, 2);
//
//            $user = new BotCashbackHistoryNew;
//            $user->admin_login = 'BOT';
//            $user->user_id = $order['user_id'];
//            $user->order_id = $order['id'];
//            $user->type = 'IN';
//            $user->summa = $cashback;
//            $user->descr = 'Начисление за  заказ № '.$order['simpla_id'];
//            $user->balance_old = 0;
//            $user->balance = 0;
//            $user->ip = $ip;
//            $user->date_z = date("Y-m-d H:i:s");
//            $user->save();
//
//        }
//        dd($orders);

    }

    public static function cashbackUsers($user_id, $type, $cashback)
    {

        $sum = $type == 'OUT' ? -1 * $cashback : $cashback;

        if (BotCashbackUsers::where('user_id', $user_id)->count() == 0) {

            $user = new BotCashbackUsers;
            $user->user_id = $user_id;
            $user->sum = $sum;
            $user->date_reg = date("Y-m-d H:i:s");
            $user->date_edit = date("Y-m-d H:i:s");
            $user->save();

        }
        else {

            $user = BotCashbackUsers::where('user_id', $user_id)->first();
            $cashback_old = $user['sum'];
            $sum = $cashback_old + $sum;
            BotCashbackUsers::where('user_id', $user_id)->update(['sum' => $sum, 'date_edit' => date("Y-m-d H:i:s")]);

        }

    }

    public static function getUserBalance($user_id)
    {

        $user = BotCashbackUsers::where('user_id', $user_id)->first();
        return $user !== null && $user['sum'] !== null ? $user['sum'] : 0;

    }

    public static function simpla() {

        echo app_path();

        include('../../www/api/Simpla.php');

        $ins_phone = '380955675764';
        $simpla = new \Simpla();
        if ($user = $simpla->users->get_user('+'.$ins_phone)) {
            echo '1.user_id: '.$user->id.'<br />';
            echo '2.user_name: '.$user->name.'<br />';
        }
//        else {
//            $add_arr = ['name' => $ins_name, 'phone' => '+'.$ins_phone, 'enabled' => '1'];
//            $simpla->users->add_user($add_arr);
//        }

    }

    public static function raffle() {

        $raffle_users = BotRaffle::where('win', 1)->distinct('user_id')->get();
        foreach ($raffle_users as $raffle_user) {
            $win = BotRaffle::where('win', 1)->where('user_id', $raffle_user->user_id)->count();
            echo 'user_id: '.$raffle_user->user_id.' - '.$win.'<br />';
        }
        dd($raffle_users);

    }


    public static function menu()
    {

//        $options = Simpla_Options::join('s_products', 's_products.id', 's_options.product_id')
//            ->join('s_features', 's_features.id', 's_options.feature_id')
//            ->where('s_products.visible', 1)
//            ->where('s_features.in_filter', 1)
//            ->groupBy('s_options.value')
//            ->distinct('s_options.value')
//            ->get(['s_options.value']);
//
//        $arr = [];
//        foreach ($options as $option) {
//            $arr[] = $option->value;
//        }

        $text = 'Без мяса';
        $products = Simpla_Options::join('s_products', 's_products.id', 's_options.product_id')
            ->join('s_features', 's_features.id', 's_options.feature_id')
            ->leftJoin('s_tabs', 's_products.id', 's_tabs.product_id')
            ->join('s_images', 's_images.product_id', 's_products.id')
            ->where('s_products.visible', 1)
            ->where('s_features.in_filter', 1)
            ->where('s_images.position', 0)
            ->where('s_options.value', $text)
            ->get(['s_products.id', 's_products.name', 's_products.featured', 's_products.position', 's_tabs.body as description', 's_images.filename']);


        dd($products);

    }

    public static function test() {

        $orders = BotOrder::select('user_id')->where('order_date_reg', '>=', '2019-07-01 00:00:00')->where('order_date_reg', '<=', '2019-11-30 23:59:59')->distinct()->get();

        $n = 0;
        foreach ($orders as $order) {

            $orders_sum = BotOrder::where('user_id', $order->user_id)->where('order_date_reg', '>=', '2019-07-01 00:00:00')->where('order_date_reg', '<=', '2019-11-30 23:59:59')->get();

            $i = 0;
            $sum = 0;
            $phones = [];
            foreach ($orders_sum as $order_sum) {

                $i++;
                $sum += $order_sum['order_price'];
                if (!in_array($order_sum['order_phone'], $phones)) $phones[] = $order_sum['order_phone'];

            }
            $sum_s = round($sum / $i, 2);
            if ($sum_s >= 300) {

                $count = BotOrder::where('user_id', $order->user_id)->where('order_date_reg', '>=', '2019-12-01 00:00:00')->where('order_date_reg', '<=', '2020-01-29 23:59:59')->count();
                if ($count == 0) {

                    $n++;
                    echo 'user_id: '.$order['user_id'].'; Имя: '.$order_sum['order_name'].'; Кол-во заказов: '.$i.'; Общая сумма заказов: '.$sum.'грн; Средний чек: '.$sum_s.'грн;  Телефоны: '.implode(", ", $phones).';<br />';

                }

            }

        }

        dd($n);

    }

    public static function execute(LRequest $request) {

        if (view()->exists('admin.orders')) {

//            if ($request->ajax()) {
//                $data = BotRestaurants::join('bot_restaurants_settings', 'bot_restaurants_settings.resto_id', '=', 'bot_restaurants.id')
//                    ->leftJoin('bot_restaurants_network_agr', 'bot_restaurants_network_agr.resto_id', '=', 'bot_restaurants.id')
//                    ->leftJoin('bot_restaurants_network', 'bot_restaurants_network.resto_id', '=', 'bot_restaurants.id')
//                    ->where('bot_restaurants_settings.settings_name', 'city')
//                    ->where('bot_restaurants_network.resto_id', null)
////                ->where('bot_id', '!=', null)
////                ->orderBy('name', 'asc')
//                    ->get(
//                        [
//                            'bot_restaurants.id as id',
//                            'bot_restaurants_network_agr.resto_id as network_agr_id',
//                            'bot_restaurants_network.resto_id as network_id',
//                            'bot_restaurants.fav as fav',
//                            'bot_restaurants.name as name',
//                            'bot_restaurants.bot_id as bot_id',
//                            'bot_restaurants.act as act',
//                            'bot_restaurants.reserve as reserve',
//                            'bot_restaurants.keywords as keywords',
//                            'bot_restaurants_settings.settings_val as city'
//                        ]
//                    );
////                $data = BotRestaurants::latest()->get();
////                dd($data);
//                return Datatables::of($data)
//                    ->addIndexColumn()
//                    ->addColumn('fav', function($row){
//                        $img = $row->fav == 1 ? '<img src="'.asset('assets/img/star.png').'" style="width:28px; height:28px;" class="fav" id="fav_'.$row->id.'" />' : '<img src="'.asset('assets/img/star_bw.png').'" style="width:28px; height:28px;" class="fav" id="fav_'.$row->id.'" />';
//                        return $img;
//                    })
//                    ->addColumn('name', function($row){
//                        $link = '<a alt="'.$row->name.'" href="'.route('restoEdit', ['resto' => $row->id]).'">'.$row->name.'</a>';
//                        return $link;
//                    })
//                    ->addColumn('network', function($row){
//                        $img = '<img src="'.asset('assets/img/network_no0.png').'" style="width:28px; height:28px; cursor:pointer;" class="network" id="'.$row->id.'" />';
//                        if ($row->network_agr_id !== null) {
//                            $img = '<a alt="'.$row->name.'" href="'.route('restoNetwork', ['resto_agr_id' => $row->network_agr_id]).'"><img src="'.asset('assets/img/network_agr.png').'" style="width:28px; height:28px;" /></a>';
//                        }
//                        elseif ($row->network_id !== null) {
//                            $img = '<img src="'.asset('assets/img/network.png').'" style="width:28px; height:28px;" />';
//                        }
//                        return $img;
//                    })
//                    ->addColumn('bot_id', function($row){
//                        $img = '';
//                        if ($row->bot_id !== null) {
//                            $act = BotRoute::where('id', $row->bot_id)->first()['act'];
//                            $img = $act == 1 ? '<img src="'.asset('assets/img/telegram_logo_1.png').'" style="width:28px; height:28px;" />' : '<img src="'.asset('assets/img/telegram_logo_0.png').'" style="width:28px; height:28px;" />';
//                        }
//                        return $img;
//                    })
//                    ->addColumn('act', function($row){
//                        $checked = $row->act == 1 ? ' checked' : '';
//                        $checkbox = '<label class="form-switch">
//                            <input type="checkbox" '.$checked.' class="switch_resto" id="active'.$row->id.'">
//                            <i></i>
//                            </label>
//                            ';
//                        return $checkbox;
//                    })
//                    ->addColumn('reserve', function($row){
//                        $checked = $row->reserve == 1 ? ' checked' : '';
//                        $checkbox = '<label class="form-switch2">
//                            <input type="checkbox" '.$checked.' class="switch_resto_reserve" id="reserve'.$row->id.'">
//                            <i></i>
//                            </label>
//                            ';
//                        return $checkbox;
//                    })
//                    ->addColumn('delete', function($row){
//                        $btn = '<button id="delete_resto_'.$row->id.'" type="button" class="resto_delete btn btn-danger">Удалить</button>';
//                        return $btn;
//                    })
//                    ->rawColumns(['fav', 'name', 'network', 'bot_id', 'act', 'reserve', 'delete'])
//                    ->make(true);
//            }


            $data = [
                'title' => 'Заказы',
//                'hours_table' => $hours_table,
//                'res5_4' => $res5_4,
//                'res5_6' => $res5_6,
//                'res5_7' => $res5_7,
//                'res5_3' => $res5_3,
//                'res10_4' => $res10_4,
//                'res10_6' => $res10_6,
//                'res10_7' => $res10_7,
//                'res10_3' => $res10_3,
//                'input' => $input,
            ];

            return view('admin.orders', $data);

        }

    }

    public function ordersList()
    {

        $users = BotEcoUser::get();
        return datatables()->of($users)
            ->make(true);

    }

}
