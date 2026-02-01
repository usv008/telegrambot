<?php

use App\Http\Controllers\ArchiController;
use App\Http\Controllers\BotAdvertisingStatController;
use App\Http\Controllers\BotChatController;
use App\Http\Controllers\BotFeedBackController;
use App\Http\Controllers\BotMailingController;
use App\Http\Controllers\BotOrdersController;
use App\Http\Controllers\BotRaffleController;
use App\Http\Controllers\BotReviewsController;
use App\Http\Controllers\BotSeaBattleController;
use App\Http\Controllers\BotSettingsCbAndActionsController;
use App\Http\Controllers\BotSettingsController;
use App\Http\Controllers\BotSettingsPaymentsController;
use App\Http\Controllers\BotStatController;
use App\Http\Controllers\BotUserHistoryController;
use App\Http\Controllers\BotUsersController;
use App\Http\Controllers\CashBackController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageThumbController;
use App\Http\Controllers\LiqPayController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\PrestaShopCallbackController;
use App\Http\Controllers\ShowModalDialogController;
use App\Http\Controllers\Telegram\BotCartController;
use App\Http\Controllers\Telegram\BotMenuNewController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\WayForPayController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => 'web'], function() {

//    Route::get('/users', [UserController::class, 'index']);
//    Route::get('/users', 'App\Http\Controllers\UserController@index');

    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/setwebhook', [TelegramController::class, 'setWebhook']);
    Route::get('/unsetwebhook', [TelegramController::class, 'unsetwebhook']);
    Route::post('/'.env('PHP_TELEGRAM_BOT_API_KEY'), [TelegramController::class, 'webhook']);
    Route::match(['get', 'post'],'/'.env('DRINKCHERRY_TELEGRAM_BOT_API_KEY'), [App\Http\Controllers\TelegramBotCherry\TelegramBotCherryController::class, 'handle']);

    Route::match(['get', 'post'], '/'.env('LIQPAY_CALLBACK'), [LiqPayController::class, 'liqpay_callback'])->name('liqpay_callback');
    Route::match(['get', 'post'], '/'.env('WAYFORPAY_CALLBACK'), [WayForPayController::class, 'wayforpay_callback'])->name('wayforpay_callback');
    Route::match(['get', 'post'], '/'.env('WAYFORPAY_UBUNTU_CALLBACK'), [WayForPayController::class, 'wayforpay_ubuntu_callback'])->name('wayforpay_ubuntu_callback');
    Route::match(['post'], '/'.env('PRESTASHOP_CALLBACK'), [PrestaShopCallbackController::class, 'execute'])->name('prestashop_callback');

    Route::get('/getNewUsers', [TestController::class, 'getNewUsers'])->name('getNewUsers');
    Route::get('/testReturnCashback2023', [TestController::class, 'testReturnCashback2023'])->name('testReturnCashback2023');
    Route::get('/get_phone_numbers', [TestController::class, 'get_phone_numbers']);
    Route::get('/test_stock', [TestController::class, 'test_stock']);

    Route::get('/check_take_of_cashback', [TestController::class, 'returnCashback']);

    Route::get('/tttest_cashback', [TestController::class, 'tttest_cashback']);
    Route::get('/simpla_add_product_regions', [TestController::class, 'simpla_add_product_regions']);

    Route::get('/test-feedback', [TestController::class, 'testFeedback']);
    Route::get('/test-active-users', [TestController::class, 'testActiveUsers']);
    Route::get('/test-remove-action-pizzas', [TestController::class, 'removeActionPizzas']);
    Route::get('/test-send-dice', [TestController::class, 'sendDice'])->name('test-send-dice');
    Route::get('/test_sendtest_game', [TestController::class, 'sendTestGame'])->name('test_sendtest_game');
//    Route::get('/testGame', [TestController::class, 'showTestGame'])->name('testGame');
//    Route::get('/get_categories', [TestController::class, 'getMenus'])->name('get_categories');
//    Route::get('/get_products', [TestController::class, 'getProducts'])->name('get_products');
//    Route::get('/get_product', [TestController::class, 'getProduct'])->name('get_product');

    Route::get('/show_new_menu', [BotMenuNewController::class, 'showMenu'])->name('show_new_menu');
    Route::get('/count_products_in_cart', [BotMenuNewController::class, 'countProductsInCartByUserId'])->name('count_products_in_cart');
    Route::get('/get_categories', [BotMenuNewController::class, 'getMenus'])->name('get_categories');
    Route::get('/get_products', [BotMenuNewController::class, 'getProducts'])->name('get_products');
    Route::get('/get_product', [BotMenuNewController::class, 'getProduct'])->name('get_product');
    Route::post('/add_product_to_cart', [BotMenuNewController::class, 'addProductToCart'])->name('add_product_to_cart');
    Route::post('/remove_product_in_cart', [BotMenuNewController::class, 'removeProductInCart'])->name('remove_product_in_cart');
    Route::post('/delete_product_in_cart', [BotMenuNewController::class, 'deleteProductInCart'])->name('delete_product_in_cart');
    Route::get('/show_cart', [BotMenuNewController::class, 'showCart'])->name('show_cart');
    Route::get('/show_order', [BotMenuNewController::class, 'showOrder'])->name('show_order');
    Route::get('/get_streets', [BotMenuNewController::class, 'getStreets'])->name('get_streets');
    Route::get('/get_main', [BotMenuNewController::class, 'getMain'])->name('get_main');
    Route::get('/address_in_delivery_area', [BotMenuNewController::class, 'addressInDeliveryArea'])->name('address_in_delivery_area');
    Route::get('/get_time_for_order', [BotMenuNewController::class, 'getTimeForOrder'])->name('get_time_for_order');
    Route::get('/get_order_sum', [BotMenuNewController::class, 'getOrderSum'])->name('get_order_sum');
    Route::post('/add_order', [BotMenuNewController::class, 'addOrder'])->name('add_order');

    Route::get('/testPresta', [BotMenuNewController::class, 'testPresta'])->name('testPresta');
    Route::match(['get', 'post'], '/testCreateOrder', [BotMenuNewController::class, 'testCreateOrder'])->name('testCreateOrder');


//    Route::get('/test', 'ArchiController@test', 'test');
//    Route::get('/testpay', 'PayController@test', 'testpay');
////    Route::get('/test_serviceUrl', 'PayController@test_serviceUrl', 'test_serviceUrl');
//    Route::match(['get', 'post'], '/test_serviceUrl', ['uses' => 'PayController@test_serviceUrl', 'as' => 'test_serviceUrl']);
////    Route::get('/test2', 'ArchiController@test2', 'test2');

//    Route::get('/test_send', 'PostsController@test_send', 'test_send');
//    Route::get('/test_feedback', 'PostsController@test_feedback', 'test_feedback');

//    Route::get('/map', ['uses' => 'MapController@execute', 'as' => 'map']);
//    Route::get('/map_test', ['uses' => 'MapController@map_test', 'as' => 'map_test']);

////    Route::get('/menu', ['uses' => 'TestController@menu', 'as' => 'menu']);
////    Route::get('/simpla', ['uses' => 'TestController@simpla', 'as' => 'simpla']);
//    Route::get('/thumb', ['uses' => 'ImageThumbController@execute', 'as' => 'thumb']);

//    Route::get('/show_cashback', ['uses' => 'TestController@show_cashback', 'as' => 'show_cashback']);
//    Route::get('/test_cashback', ['uses' => 'TestController@test_cashback', 'as' => 'test_cashback']);
//    Route::get('/test_show_orders_old_products', ['uses' => 'TestController@test_show_orders_old_products', 'as' => 'test_show_orders_old_products']);
//    Route::get('/delete_duplicate_present_products', ['uses' => 'TestController@delete_duplicate_present_products', 'as' => 'delete_duplicate_present_products']);

    Route::get('/test_cashback',  [CashBackController::class, 'addCashback']);

    Route::get('/thumb_all',  [ImageThumbController::class, 'all'])->name('thumb_all');

    Auth::routes();

    Route::get('/home',  [HomeController::class, 'index'])->name('home');

//    Route::get('/home', 'HomeController@index')->name('home');
    // Route::get('/admin', 'HomeController@index');

});

/// admin
Route::middleware(['auth', 'role:,admin_panel'])->prefix('admin')->group(function() {

    Route::get('/', function () {
        if (view()->exists('admin.index')) {
            $data = ['title' => 'Админка'];
            return view('admin.index', $data);
        }
        return null;
    })->name('admin');

    Route::get('/map', [MapController::class, 'execute']);

    Route::match(['get', 'post', 'delete'], '/showmodaldialog', [ShowModalDialogController::class, 'execute'])->name('showmodaldialog');

    Route::group(['prefix' => 'catalog'], function() {

        Route::get('/', [CatalogController::class, 'show_categories'])->name('catalog');
        Route::get('/categories', [CatalogController::class, 'show_categories'])->name('categories');
        Route::get('/products', [CatalogController::class, 'show_products'])->name('products');
        Route::get('/sizes', [CatalogController::class, 'show_sizes'])->name('sizes');

        Route::post('/categories_add', [CategoriesController::class, 'execute'])->name('categories_add');

    });

    Route::group(['prefix' => 'bot'], function() {

        Route::group(['middleware' => 'role:,users'], function() {
            Route::get('/', [BotUsersController::class, 'show_users'])->name('bot');
            Route::get('/users', [BotUsersController::class, 'show_users'])->name('users');
            Route::get('/users_list', [BotUsersController::class, 'users_list'])->name('users_list');
            Route::get('/users/{user}', [BotUsersController::class, 'show_user'])->name('user');
        });

        Route::group(['middleware' => 'role:,orders'], function() {
            Route::get('/orders', [BotOrdersController::class, 'show_orders'])->name('orders');
            Route::get('/orders_list', [BotOrdersController::class, 'orders_list'])->name('orders_list');
            Route::post('/order', [BotOrdersController::class, 'show_order'])->name('order');
            Route::post('/order_delete', [BotOrdersController::class, 'order_delete'])->name('order_delete');
            Route::post('/order_delete_yes', [BotOrdersController::class, 'order_delete_yes'])->name('order_delete_yes');
            Route::get('/user_orders_list', [BotOrdersController::class, 'user_orders_list'])->name('user_orders_list');
        });

        Route::group(['middleware' => 'role:,feedback'], function() {
            Route::get('/feedback', [BotFeedBackController::class, 'show_feedback'])->name('feedback');
            Route::get('/feedback_list', [BotFeedBackController::class, 'feedback_list'])->name('feedback_list');
        });

        Route::group(['middleware' => 'role:,reviews'], function() {
            Route::get('/reviews', [BotReviewsController::class, 'show_reviews'])->name('reviews');
            Route::get('/reviews_list', [BotReviewsController::class, 'reviews_list'])->name('reviews_list');
            Route::post('/change_status', [BotReviewsController::class, 'change_status'])->name('change_status');
            Route::post('/review_delete', [BotReviewsController::class, 'review_delete'])->name('review_delete');
            Route::post('/review_delete_yes', [BotReviewsController::class, 'review_delete_yes'])->name('review_delete_yes');
        });

        Route::group(['middleware' => 'role:,stat'], function() {
            Route::get('/stat', [BotStatController::class, 'show_stat'])->name('stat');
            Route::get('/stat_new_users', [BotStatController::class, 'show_new_users_stat'])->name('stat_new_users');
        });

        Route::group(['middleware' => 'role:,advertising'], function() {
            Route::get('/stat_advertising', [BotAdvertisingStatController::class, 'show_stat'])->name('stat_advertising');
            Route::post('/show_advertising_form_add', [BotAdvertisingStatController::class, 'show_form_add'])->name('show_advertising_form_add');
            Route::post('/show_advertising_form_edit', [BotAdvertisingStatController::class, 'show_form_edit'])->name('show_advertising_form_edit');
            Route::post('/show_advertising_form_delete', [BotAdvertisingStatController::class, 'show_form_delete'])->name('show_advertising_form_delete');
            Route::post('/stat_advertising_add', [BotAdvertisingStatController::class, 'stat_advertising_add'])->name('stat_advertising_add');
            Route::post('/stat_advertising_edit', [BotAdvertisingStatController::class, 'stat_advertising_edit'])->name('stat_advertising_edit');
            Route::post('/stat_advertising_delete', [BotAdvertisingStatController::class, 'stat_advertising_delete'])->name('stat_advertising_delete');
        });

        Route::group(['middleware' => 'role:,users_history'], function() {
            Route::get('/users_history', [BotUserHistoryController::class, 'show_history'])->name('users_history');
            Route::get('/users_history_list', [BotUserHistoryController::class, 'users_history_list'])->name('users_history_list');
        });

        Route::group(['middleware' => 'role:,raffle'], function() {
            Route::get('/raffle', [BotRaffleController::class, 'show_raffle'])->name('raffle');
            Route::get('/raffle_list', [BotRaffleController::class, 'raffle_list'])->name('raffle_list');
            Route::post('/raffle_attempts', [BotRaffleController::class, 'raffle_attempts'])->name('raffle_attempts');
            Route::get('/raffle_pizzas', [BotRaffleController::class, 'rafflePizzas'])->name('raffle_pizzas');

            Route::get('/sea-battle', [BotSeaBattleController::class, 'show'])->name('sea-battle');
            Route::get('/sea-battle-list', [BotSeaBattleController::class, 'list'])->name('sea-battle-list');
            Route::post('/sea-battle-game', [BotSeaBattleController::class, 'game'])->name('sea-battle-game');
            Route::get('/sea-battle-rates', [BotSeaBattleController::class, 'rates'])->name('sea-battle-rates');
            Route::get('/sea-battle-rates-list', [BotSeaBattleController::class, 'rates_list'])->name('sea-battle-rates-list');
        });

        Route::group(['middleware' => 'role:,mailing'], function() {
            Route::get('/mailing', [BotMailingController::class, 'execute'])->name('mailing');
            Route::get('/posts_list', [BotMailingController::class, 'posts_list'])->name('posts_list');
            Route::post('/add_mailing', [BotMailingController::class, 'add_mailing'])->name('add_mailing');
            Route::post('/post_delete', [BotMailingController::class, 'show_modal_order_delete'])->name('post_delete');
            Route::post('/post_delete_yes', [BotMailingController::class, 'post_delete_yes'])->name('post_delete_yes');
        });

        Route::group(['middleware' => 'role:,chat'], function() {
            Route::get('/chat', [BotChatController::class, 'execute'])->name('chat');
            Route::get('/load-chats', [BotChatController::class, 'loadChats'])->name('load-chats');
            Route::get('/chat/{user_id}', [BotChatController::class, 'chatUser'])->name('chat-user');
            Route::post('/send-message-to-chat', [BotChatController::class, 'sendMessageToChat'])->name('send-message-to-chat');
            Route::get('/load-chat-users', [BotChatController::class, 'loadChatUsers'])->name('load-chat-users');
            Route::get('/load-chat-messages', [BotChatController::class, 'loadChatMessages'])->name('load-chat-messages');
            Route::post('/chat-read-messages', [BotChatController::class, 'readMessages'])->name('chat-read-messages');
            Route::get('/chat-create-new', [BotChatController::class, 'createNewChat'])->name('chat-create-new');
        });

        Route::group(['middleware' => 'role:,settings'], function() {
            Route::get('/settings', [BotSettingsController::class, 'execute'])->name('settings');
            Route::post('/settings_save', [BotSettingsController::class, 'settings_save'])->name('settings_save');
            Route::post('/settings_cashback_save', [BotSettingsController::class, 'cashback_save'])->name('settings_cashback_save');
            Route::post('/settings_user_role_save', [BotSettingsController::class, 'user_role_save'])->name('settings_user_role_save');
            Route::post('/settings_user_delete', [BotSettingsController::class, 'user_delete'])->name('settings_user_delete');
            Route::post('/settings_user_delete_yes', [BotSettingsController::class, 'user_delete_yes'])->name('settings_user_delete_yes');
            Route::get('/settings_cb_and_actions', [BotSettingsCbAndActionsController::class, 'execute'])->name('settings_cb_and_actions');
            Route::post('/settings_cb_and_actions_save', [BotSettingsCbAndActionsController::class, 'settings_cb_and_actions_save'])->name('settings_cb_and_actions_save');
            Route::get('/settings_payments', [BotSettingsPaymentsController::class, 'execute'])->name('settings_payments');
            Route::post('/settings_payments_save', [BotSettingsPaymentsController::class, 'settings_payments_save'])->name('settings_payments_save');
        });

        Route::post('/send_message_to_user', [PostsController::class, 'sendMessageToUser'])->name('send_message_to_user');

        Route::get('/test_clear', [BotCartController::class, 'clear_all_cart_without_action_pizza_and_product_present'])->name('test_clear');

//        Route::get('/test_cashback_referral', ['uses' => 'CashBackController@addCashbackInvite', 'as' => 'test_cashback_referral']);

    });

    Route::group(['prefix' => 'acrhi'], function() {

        Route::get('/', [ArchiController::class, 'execute'])->name('acrhi');
//        Route::match(['get', 'post'], '/{acrhi}', ['uses' => 'ArchiController@execute', 'as' => 'archiPoint']);
//        Route::match(['get', 'post', 'delete'], '/edit/{point}', ['uses' => 'RestoEditController@execute', 'as' => 'restoEdit']);

//        Route::match(['get', 'post'], '/add', ['uses' => 'RestoAddController@execute', 'as' => 'restoAdd']);
//        Route::match(['get', 'post', 'delete'], '/edit/{resto}', ['uses' => 'RestoEditController@execute', 'as' => 'restoEdit']);

//        Route::match(['get', 'post', 'delete'], '/showmodaldialog', ['uses' => 'ShowModalDialogController@execute', 'as' => 'showmodaldialog']);

//        Route::match(['get', 'post'], '/network', ['uses' => 'RestoNetworkController@execute', 'as' => 'restoNetwork']);

    });

});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
