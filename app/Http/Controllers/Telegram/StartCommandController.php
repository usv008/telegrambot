<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotAdvertisingChannel;
use App\Models\BotAdvertisingChannelTexts;
use App\Models\BotCashbackHistory;
use App\Models\BotChatMessages;
use App\Models\BotRaffleCherryLinks;
use App\Models\BotRaffleCherryTakeaway;
use App\Models\BotSettingsCashback;
use App\Models\BotSettingsLang;
use App\Models\BotSettingsSticker;
use App\Models\BotUser;
use App\Models\BotUserHistory;
use App\Models\BotUsersNav;
use App\Http\Controllers\Controller;

use App\Http\Controllers\GeocodeController;
use App\Http\Controllers\SimplaRegionsController;
use App\Http\Controllers\TelegramController;
use Illuminate\Http\Request as LRequest;

use Longman\TelegramBot\Commands\UserCommands\OrderCommand;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;

use App\Models\BotSettingsTexts;
use App\Models\BotSettingsButtonsInline;

class StartCommandController extends Controller
{

    public static $telegram;

    public static function process_text($user_id, $text, $act) {

        $checkUserActive = self::checkUserActive($user_id);

        $buttons_home = BotButtonsController::getButtons($user_id,'System', ['home', 'cancel']);
        $buttons_back = BotButtonsController::getButtons($user_id,'System', ['back']);
        $products_arr = BotMenuController::get_products_arr($user_id);

        $channels_arr = [];
        $advertising_channels = BotAdvertisingChannel::orderBy('id', 'asc')->get();
        foreach ($advertising_channels as $value) {

            if ($value['url'] !== null && $value['url'] !== '') $channels_arr[] = $value['url'];

        }

        $menu_back = BotTextsController::getText($user_id, 'Menu', 'menu_back_text');

        // Обрабатываем содержимое текста
        $first_time = BotUsersNavController::getValue($user_id, 'first_time');
        $change_key = BotUsersNavController::getValue($user_id, 'change_key');

        $my_order_text = BotTextsController::getText($user_id, 'MyOrders', 'my_orders');

        if ($text == '' || in_array($text, $buttons_home) || in_array($text, $buttons_back)) {

            BotRaffleCherryTakeaway::removeUnReceivedByUserId($user_id);
            BotUsersNavController::updateValue($user_id, 'change_key', null);
            BotUsersNavController::updateValue($user_id, 'raffle_cherry_takeaway_id', null);
            if ($act == 'start') {
                BotUserHistoryController::insertToHistory($user_id, 'send', 'Hello');
                self::send_hello($user_id);
            }

        }
        elseif ($change_key !== null) {

            if ($text !== '') {

                if ($change_key == 'send_review') {
                    BotUserHistoryController::insertToHistory($user_id, 'text', 'Отзыв: '.$text);
                    $firstname = BotUsersNavController::getValue($user_id, 'firstname');
                    BotMoreController::addReview($user_id, $firstname, $text);
                    BotUsersNavController::updateValue($user_id, $change_key, $text);
                    BotUsersNavController::updateValue($user_id, 'change_key', null);
                    MoreCommandController::send_review_ok($user_id);
                }
                elseif ($change_key == 'cashback') {

                    $buttons_skip = BotButtonsController::getButtons($user_id,'System', ['skip']);
                    if (in_array($text, $buttons_skip)) {

                        BotCashbackController::clearCashback($user_id);
                        BotOrderController::select_date($user_id, 'send', null);

                    }
                    else {

                        $total = BotCartController::count_sum_total_without_cashback($user_id);
                        $min_sum_order = (float)BotSettingsController::getSettings($user_id,'min_sum_order')['settings_value'];

                        $user_cashback = BotCashbackController::getUserCashback($user_id);
                        $user_cashback_action = BotCashbackController::getUserCashbackAction($user_id);

                        if ($total >= 350) $pay_cashback = $user_cashback + $user_cashback_action >= $total / 2 ? bcdiv($total, '2', 2) : $user_cashback + $user_cashback_action;
                        else $pay_cashback = $user_cashback >= $total / 2 ? bcdiv($total, '2', 2) : $user_cashback;

                        $min_order_sum = BotSettingsCashback::get_min_order_sum();

                        if (BotUsersNavController::getDeliveryYesOrNo($user_id) == 1) {
                            if ($total - $pay_cashback < $min_order_sum) $pay_cashback = $total - $min_order_sum;
                            if ($pay_cashback < 0) $pay_cashback = 0;
                        }
                        $delivery_discount = BotUsersNavController::get_delivery_from_user_id($user_id)['discount'];

                        if (($total >= $min_sum_order || $delivery_discount == 1) && $text !== '' && (is_numeric($text) || is_float($text)) && $text >= 0 && $text <= $pay_cashback ) {

                            CashbackCommandController::show_message_ok($user_id, $text);
                            BotUsersNavController::updateValue($user_id, 'change_key', null);
                            BotOrderController::select_date($user_id, 'send', null);

                        }
                        else CashbackCommandController::show_message($user_id);

                    }

                }
                elseif ($change_key == 'name') {
                    BotUserHistoryController::insertToHistory($user_id, 'change', 'Имя: '.$text);
                    BotUsersNavController::updateValue($user_id, $change_key, $text);
                    BotUsersNavController::updateValue($user_id, 'change_key', null);
                    if ($first_time == null) BotOrderController::select_nta($user_id, null, 'send', 'nta', null);
                    else BotOrderController::change_phone($user_id);
                }
                elseif ($change_key == 'phone') {

                    BotUserHistoryController::insertToHistory($user_id, 'change', 'Телефон: '.$text);

                    while (stripos($text, '+') !== false) {
                        $text = str_replace("+", "", $text);
                    }

                    $pattern_tel = "/380\d{3}\d{2}\d{2}\d{2}/";
                    if (preg_match($pattern_tel, $text) && strlen($text) === 12) {

                        BotUsersNavController::updateValue($user_id, $change_key, $text);
                        BotUsersNavController::updateValue($user_id, 'change_key', null);
                        if ($first_time == null) BotOrderController::select_nta($user_id, null, 'send', 'nta', null);
                        else {
                            if (BotUsersNavController::getDeliveryYesOrNo($user_id) == 0) BotOrderController::change_no_call($user_id);
                            else BotOrderController::change_addr($user_id);
                        }

                    }
                    else BotOrderController::change_phone($user_id);

                }
                elseif ($change_key == 'addr') {

                    if (is_array($text)) {

                        $lat = $text['lat'];
                        $lng = $text['lng'];
                        $text_ins = GeocodeController::geocode($lat, $lng);
                        BotUsersNavController::updateValue($user_id, 'latitude', $lat);
                        BotUsersNavController::updateValue($user_id, 'longitude', $lng);

                        BotUserHistoryController::insertToHistory($user_id, 'change', 'Latitude: '.$lat.', Longitude: '.$lng.' Адрес: '.$text_ins);

                    }
                    else {
                        BotUserHistoryController::insertToHistory($user_id, 'change', 'Адрес: '.$text);
                        $text_ins = $text;
                    }

                    BotUsersNavController::updateValue($user_id, $change_key, $text_ins);
                    BotUsersNavController::updateValue($user_id, 'change_key', null);
//                    if ($first_time == null) BotOrderController::select_nta($user_id, null, 'send', 'nta', null);
//                    else BotOrderController::change_no_call($user_id);
                    BotOrderController::change_no_call($user_id);

                }
                elseif ($change_key == 'no_call') {
                    $buttons_no_call = $buttons = BotButtonsController::getButtons($user_id,'Order', ['no_call']);
                    BotUsersNavController::updateValue($user_id, 'change_key', null);
                    if (in_array($text, $buttons_no_call)) {
                        $ins = 1;
                        BotUserHistoryController::insertToHistory($user_id, 'change', 'Выбрано НЕ ЗВОНИТЬ');
                        BotUsersNavController::updateValue($user_id, $change_key, $ins);
                    }
                    else {
                        $ins = null;
                        BotUserHistoryController::insertToHistory($user_id, 'change', 'Выбрано перезвонить');
                        BotUsersNavController::updateValue($user_id, $change_key, $ins);
                    }
                    BotOrderController::change_from($user_id);
                }
                elseif ($change_key == 'change_from') {
                    BotUserHistoryController::insertToHistory($user_id, 'change', 'Сдача с: '.$text);
                    BotUsersNavController::updateValue($user_id, $change_key, 'Сдача с: '.$text);
                    BotUsersNavController::updateValue($user_id, 'change_key', null);
                    BotOrderController::change_sushi_sticks($user_id);
                }
                elseif ($change_key == 'sushi_sticks') {
                    BotUserHistoryController::insertToHistory($user_id, 'change', 'Суши-палочек: '.$text);
                    BotUsersNavController::updateValue($user_id, $change_key, 'Суши-палочек: '.$text);
                    BotUsersNavController::updateValue($user_id, 'change_key', null);
                    BotOrderController::change_comment($user_id);
                }
                elseif ($change_key == 'comment') {
                    BotUserHistoryController::insertToHistory($user_id, 'change', 'Комментарий: '.$text);
                    BotUsersNavController::updateValue($user_id, $change_key, $text);
                    BotUsersNavController::updateValue($user_id, 'change_key', null);
                    $payment_id = BotUsersNavController::getValue($user_id, 'payment_id');
                    $payment_type = BotSettingsPaymentsController::getPaymentType($payment_id);
//                    if ($payment_type == 'card') BotOrderController::change_contactless($user_id);
//                    else {
//                        BotUsersNavController::updateValue($user_id, 'first_time', null);
//                        BotOrderController::show_order_finish($user_id, 'send', null);
//                    }
                    BotUsersNavController::updateValue($user_id, 'first_time', null);
                    BotOrderController::show_order_finish($user_id, 'send', null);

                }
                elseif ($change_key == 'contactless') {
                    $buttons_contactless = $buttons = BotButtonsController::getButtons($user_id,'Order', ['contactless']);
                    BotUsersNavController::updateValue($user_id, 'change_key', null);
                    if (in_array($text, $buttons_contactless)) {
                        $ins = 1;
                        BotUserHistoryController::insertToHistory($user_id, 'change', 'Бесконтактная доставка: ДА');
                        BotUsersNavController::updateValue($user_id, $change_key, $ins);
                        BotOrderController::change_contactless_comment($user_id);
                    }
                    else {
                        $ins = null;
                        BotUserHistoryController::insertToHistory($user_id, 'change', 'Бесконтактная доставка: нет');
                        BotUsersNavController::updateValue($user_id, $change_key, $ins);
                        BotUsersNavController::updateValue($user_id, 'contactless_comment', null);
                        BotUsersNavController::updateValue($user_id, 'first_time', null);
                        $payment_id = BotUsersNavController::getValue($user_id, 'payment_id');
                        $payment_type = BotSettingsPaymentsController::getPaymentType($payment_id);
                        if ($payment_type == 'card') BotOrderController::show_order_before_success_card($user_id);
                        else BotOrderController::show_order_finish($user_id, 'send', null);
                    }
                }
                elseif ($change_key == 'contactless_comment') {
                    BotUserHistoryController::insertToHistory($user_id, 'change', 'Примечание к бесконтактной доставке: '.$text);
                    BotUsersNavController::updateValue($user_id, $change_key, $text);
                    BotUsersNavController::updateValue($user_id, 'change_key', null);
                    BotUsersNavController::updateValue($user_id, 'first_time', null);
                    $payment_id = BotUsersNavController::getValue($user_id, 'payment_id');
                    $payment_type = BotSettingsPaymentsController::getPaymentType($payment_id);
                    if ($payment_type == 'card') BotOrderController::show_order_before_success_card($user_id);
                    else BotOrderController::show_order_finish($user_id, 'send', null);
                }
                elseif ($change_key == 'feedback_comment') {
                    BotUserHistoryController::insertToHistory($user_id, 'text', 'Комментарий к оценке: '.$text);
                    $feedback_id = BotUsersNavController::getValue($user_id, 'feedback_id');
                    if ($feedback_id !== null) BotFeedBackController::updateFeedBack($user_id, $feedback_id, 'comment', $text);
                    BotUsersNavController::updateValue($user_id, 'feedback_id', null);
                    BotUsersNavController::updateValue($user_id, 'change_key', null);
                    FeedBackCommandController::step_finish($user_id, $feedback_id);
                }
                elseif ($change_key == 'send_comment_game_sea_battle_rate') {
                    BotUserHistoryController::insertToHistory($user_id, 'text', 'Комментарий к оценке игры морской бой: '.$text);
                    $send_comment = BotGameSeaBattleController::rateGameCommentSend($user_id, $text);
                }
                elseif ($change_key == 'raffle_cherry_enter_name') {
                    RaffleCherryCommandController::askWinnerPhone($user_id, $text);
                }
                elseif ($change_key == 'raffle_cherry_enter_phone') {
                    RaffleCherryCommandController::getTakeawayCherry($user_id, $text);
                }

            }

        }
        elseif (in_array($text, $products_arr)) {

            $product_id = array_search($text, $products_arr);
            MenuCommandController::get_product_from_id($product_id, $user_id);

        }
        elseif (stripos($text, $my_order_text) !== false) {

            $simpla_id = str_replace($my_order_text, "", $text);
            if (is_numeric($simpla_id) && $simpla_id !== '' && $simpla_id > 0) {

                BotOrderController::sendOrderForRepeat($user_id, $simpla_id);

            }
            else self::not_understand($user_id, $text);

        }
        elseif (stripos($text, 'share') !== false) {

            $refer_id = str_replace("share", "", $text);
            if (is_numeric($refer_id) && $refer_id !== '' && $refer_id > 0) {

                BotRaffleController::updateRaffleGuest($user_id, $refer_id, 'share');

            }
            else self::not_understand($user_id, $text);

        }
        elseif (stripos($text, 'raffle') !== false) {

            $refer_id = str_replace("raffle", "", $text);
            if (is_numeric($refer_id) && $refer_id !== '' && $refer_id > 0) {

                BotRaffleController::updateRaffleGuest($user_id, $refer_id, 'raffle');

            }
            else self::not_understand($user_id, $text);

        }
        elseif (stripos($text, 'drink_cherry') !== false) {

            $link = BotRaffleCherryLinks::getLinkFromName('drink_cherry');
            $link->increment('n');
            RaffleCherryCommandController::execute($user_id, 'send', null);

        }
        elseif (stripos($text, 'ecopizza_cherry') !== false) {

            $link = BotRaffleCherryLinks::getLinkFromName('ecopizza_cherry');
            $link->increment('n');
            RaffleCherryCommandController::execute($user_id, 'send', null);

        }
        elseif (in_array($text, $channels_arr)) {

            $channel = BotAdvertisingChannel::where('url', $text)->first();

            $count_history = BotUserHistory::where('user_id', $user_id)->count();
            $new_user = $count_history <= 2 ? 1 : 0;
            $exists_user = $count_history > 2 ? 1 : 0;

            BotAdvertisingChannelController::add_to_history($channel->id, $user_id);
            BotUserHistoryController::insertToHistory($user_id, 'send', 'Hello');
            self::send_hello($user_id);

            $check_count = BotCashbackHistory::where('user_id', $user_id)->where('advertising_channel_id', $channel->id)->count();
            if ($check_count == 0) {

                if (
                    (
                        ($channel->only_new_users == 1 && $new_user == 1)
                        || ($channel->only_exists_users == 1 && $exists_user == 1)
                    )
                    && (
                        ($channel->limit_in > 0 && $channel->limit_in_value > 0)
                        || $channel->limit_in == 0
                    )
                    && (
                        $channel->bonus > 0
                        || ($channel->product_present == 1 && $channel->product_present_variant_id !== null && (int)$channel->product_present_variant_id > 0)
                    )
                ) {

                    if ($channel->bonus > 0) {
                        if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== '' && $_SERVER['REMOTE_ADDR'] !== null) $ip = $_SERVER['REMOTE_ADDR'];
                        else $ip = '';

                        $balance_old = BotCashbackController::getUserCashback($user_id);
                        $balance_new = bcadd($balance_old, $channel->bonus, 2);

                        $date_z = date("Y-m-d H:i:s");
                        $user_cashback_history = new BotCashbackHistory;
                        $user_cashback_history->admin_login = 'BOT';
                        $user_cashback_history->user_id = $user_id;
                        $user_cashback_history->type = 'IN';
                        $user_cashback_history->summa = $channel->bonus;
                        $user_cashback_history->descr = 'Начисление бонусов за переход по акционной ссылке';
                        $user_cashback_history->balance_old = $balance_old;
                        $user_cashback_history->balance = $balance_new;
                        $user_cashback_history->advertising_channel_id = $channel->id;
                        $user_cashback_history->ip = $ip;
                        $user_cashback_history->date_z = $date_z;
                        $user_cashback_history->save();
                        BotUser::where('user_id', $user_id)->update(['cashback' => $balance_new, 'updated_at' => $date_z]);
                    }

                    if ($channel->product_present == 1 && $channel->product_present_variant_id !== null && (int)$channel->product_present_variant_id > 0) {
                        BotPresentController::addPresentToCart($user_id, $channel->product_present_variant_id);
                    }

                    if ($channel->limit_in > 0)
                        BotAdvertisingChannel::find($channel->id)->decrement('limit_in_value');

                    $text = BotAdvertisingChannelTexts::find($channel->id) !== null ? BotAdvertisingChannelTexts::find($channel->id)->get_text()->first() : null;
                    if ($text !== null) {
                        $text_lang = BotTextsController::getUserTextLang($user_id);
                        $data_text = ['chat_id' => $user_id];
                        $data_text['text'] = $text->$text_lang;
                        $data_text['parse_mode'] = 'html';
                        $send_text = Request::sendMessage($data_text);
                    }

                }

            }

        }
        elseif (stripos($text, $menu_back) !== false) {
            MenuCommandController::execute($user_id, 'send', null);
        }
        elseif ($text == '+++') {
            self::share($user_id);
        }
//        elseif ($text == 'drink_cherry') {
//            RaffleCherryCommandController::execute($user_id, 'send', null);
//        }
        else {
            self::not_understand($user_id, $text);
        }

    }

    public static function checkUserActive($user_id)
    {
        if (BotUser::where('user_id', $user_id)->count() == 1) {
            $botUser = BotUser::where('user_id', $user_id)->first();
            if ($botUser->active == 0) {
                $updateBotUser = BotUser::where('user_id', $user_id)->update(['active' => 1]);
                return $updateBotUser;
            }
            return null;
        }
        return null;
    }

    public static function removeKeyboardBottom($user_id)
    {
        $keyboard_bottom = new Keyboard([]);
        $keyboard_bottom->addRow('-');
        $keyboard_b = $keyboard_bottom
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->setSelective(false);
        $data_open = ['chat_id' => $user_id];
        $data_open['text'] = BotTextsController::getText($user_id, 'Cart', 'open_cart');
        $data_open['parse_mode'] = 'html';
        $data_open['reply_markup'] = $keyboard_b;
        $send_sec1 = Request::sendMessage($data_open);
        $send_typing = Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);
        sleep(1);
        $data_open = ['chat_id' => $user_id];
        $data_open['text'] = '...';
        $data_open['parse_mode'] = 'html';
        $data_open['reply_markup'] = Keyboard::remove(['selective' => true]);
        $send_sec2 = Request::sendMessage($data_open);
        sleep(1);
        return $send_sec2;
    }

    protected static function not_understand($user_id, $text) {

        $bot_user_update = BotUser::where('user_id', $user_id)->update(['updated_at' => date("Y-m-d H:i:s")]);
        $checkUserActive = self::checkUserActive($user_id);

        if ($text == 'game') {
            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = 'Игры';
            $data_text['reply_markup'] = BotButtonsInlineController::getGamesButtonsInline($user_id);
            $send_text = Request::sendMessage($data_text);
            return null;
        }
        elseif ($text == 'select_game') {
            BotGamesController::selectGame($user_id);
            return null;
        }
//        elseif ($text = 'sea_battle') {
//            BotGamesController::startGame($user_id, 'sea_battle', []);
//            return null;
//        }

        // Заносим текст в таблицу чата
        $new_message = new BotChatMessages;
        $new_message->user_id = $user_id;
        $new_message->text = $text;
        $new_message->save();

        // Вносим в историю набранный пользователем текст
        BotUserHistoryController::insertToHistory($user_id, 'text', $text);

        /////////////////// To Me //////////////////////////////
//        $data_admin = ['chat_id' => '522750680'];
//        $data_admin['parse_mode'] = 'html';
//        $data_admin['text'] = '<a href="https://telegrambot.ecopizza.com.ua/admin/bot/users/'.$user_id.'">'.$user_id.'</a> написал в бот: '.PHP_EOL.$text;
//        Request::sendMessage($data_admin);

        ///////////////// To Chat //////////////////////////
        $data_admin = ['chat_id' => '-318301424'];
        $data_admin['parse_mode'] = 'html';
        $data_admin['text'] = '<a href="https://telegrambot.ecopizza.com.ua/admin/bot/users/'.$user_id.'">'.$user_id.'</a> написал в бот: '.PHP_EOL.$text
            .PHP_EOL.'<a href="https://telegrambot.ecopizza.com.ua/admin/bot/chat/'.$user_id.'">💬 перейти в чат с пользователем</a>';
        $g = Request::sendMessage($data_admin);

        if (BotUser::where('user_id', $user_id)->first()['chat'] == 0) {
            $update_user_chat = BotUser::where('user_id', $user_id)->update(['chat' => 1]);

            // Отправляем ответ
            $data_text = ['chat_id' => $user_id];
//        $data_text['text'] = ' "'.$text.'"? '.BotTextsController::getText($user_id, 'Start', 'not_understand');
            $data_text['text'] = BotTextsController::getText($user_id, 'Chat', 'chat_enable');
            $data_text['reply_markup'] = Keyboard::remove(['selective' => true]);
            $send_text = Request::sendMessage($data_text);
        }


//        if ($text == '123') self::$telegram->executeCommand('start');

    }

    public static function sendPhotoToOperatorsChat($message)
    {
        $user_id = $message->getFrom()->getId();
        $data_admin = ['chat_id' => '-318301424'];
        $photo = $message->getPhoto()[2];
        $data_admin['photo'] = $photo->getFileId();
        $caption = '';
        $caption_text = null;
        if ($message->getCaption() !== null && $message->getCaption() !== '') {
            $caption = ' написал в бот: '.PHP_EOL.$message->getCaption();
            $caption_text = $message->getCaption();
        }
        $data_path = [
            'file_id' => $photo->getFileId(),
        ];
        $file_path = Request::getFile($data_path);
        $photo_path = '';
        if ($file_path->getOk()) {

            $photo_path = 'https://api.telegram.org/file/bot' . env('PHP_TELEGRAM_BOT_API_KEY') . '/' . $file_path->getResult()->file_path;

            // Заносим текст в таблицу чата
            $new_message = new BotChatMessages;
            $new_message->user_id = $user_id;
            $new_message->text = $caption_text;
            $new_message->photo = $file_path->getResult()->file_path;
            $new_message->save();

        }

        $caption = '<a href="https://telegrambot.ecopizza.com.ua/admin/bot/users/'.$user_id.'">'.$user_id.'</a>'.$caption;
        $data_admin['caption'] = $caption;
        $data_admin['parse_mode'] = 'html';
        $send_t = Request::sendPhoto($data_admin);

        // Отправляем ответ
        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = BotTextsController::getText($user_id, 'Start', 'not_understand');
        $data_text['reply_markup'] = Keyboard::remove(['selective' => true]);
        $send_text = Request::sendMessage($data_text);
        $checkUserActive = self::checkUserActive($user_id);
    }

    public static function send_hello($user_id) {

        $command = 'Start';
        $name = 'hello';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command);

        BotUsersNavController::updateValue($user_id, 'order_sent', 0);
        BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'to_delete_message_id', null);

        BotUsersNavController::updateValue($user_id, 'change_key', null);

        // Отправляем стикер приветствия
        $data_sticker = ['chat_id' => $user_id];
        $data_sticker['sticker'] = BotStickerController::getSticker($user_id, $command);
        $data_sticker['reply_markup'] = Keyboard::remove(['selective' => true]);
        $send_sticker = Request::sendSticker($data_sticker);

        // Вытягиваем из БД текст для сообщения
        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_text['parse_mode'] = 'html';

        // Изменения для нового меню
        $check_win = BotRaffleController::checkUserRaffleWin($user_id);
//        if ($check_win == 0) {
//            MenuCommandController::execute($user_id, '', null);
//        }
//        else {
//            $data_text = ['chat_id' => $user_id];
//            $data_text['text'] = BotTextsController::getText($user_id, 'Raffle', 'message_ask_add_action_pizza');
//            $data_text['parse_mode'] = 'html';
//            $data_text['reply_markup'] = BotRaffleButtonsController::get_ask_pizza_add_buttons($user_id);
//            $send_text = Request::sendMessage($data_text);
//            if ($send_text->getResult() !== null) {
//                $message_id = $send_text->getResult()->getMessageId();
//                BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'to_delete_message_id', $message_id);
//            }
//        }
        // --- конец изменений

        // Формируем кнопки Inline
        $inline_keyboard = BotStartButtonsController::get_hello_buttons($user_id, $check_win);

//        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, $command, 'lang');
//        $lang = BotUserSettingsController::getLang($user_id);
//        $emoji = BotSettingsLang::where('value', $lang)->first()['emoji'];
//        $inline_keyboard->addRow( new InlineKeyboardButton(['text' => $emoji.$text, 'callback_data' => $data]) );

        $data_text['reply_markup'] = $inline_keyboard;
        $send_text = Request::sendMessage($data_text);

        BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'order_message_id', null);

//        $check_selected_city = BotUser::getValue($user_id, 'city_id');
//        if ($check_selected_city == null) {
//            return self::change_city($user_id);
//        }

        return $send_text;

    }

    public static function change_city($user_id, $message_id)
    {

        $checkUserActive = self::checkUserActive($user_id);
        $data_text = ['chat_id' => $user_id];
        $text = BotTextsController::getText($user_id, 'City', 'change_city');;
        $regions = SimplaRegionsController::getRegions($user_id);
        $data_text['text'] = $text;
        $data_text['parse_mode'] = 'html';

        $inline_keyboard = new InlineKeyboard([]);
        foreach ($regions as $id => $region) {
            $inline_keyboard->addRow(new InlineKeyboardButton([
                'text'          => $region,
                'callback_data' => 'select_region___'.$id
            ]));
        }
        $data_text['reply_markup'] = $inline_keyboard;

        if ($message_id !== null) {
            $data_text['message_id'] = $message_id;
            $send_text = Request::editMessageText($data_text);
        }
        else {
            $send_text = Request::sendMessage($data_text);
        }


        return $send_text;
    }

    public static function begin($user_id) {

        $command = 'Begin';
        $name = 'begin';

        $checkUserActive = self::checkUserActive($user_id);

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command);

        BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'to_delete_message_id', null);

        $inline_keyboard = new InlineKeyboard([]);
        list($text, $data) = BotButtonsInlineController::getButtonInline($user_id, 'Start', 'begin');
        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $text, 'web_app' => ['url' => route('show_new_menu', ['user_id' => $user_id])]]));

        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = BotTextsController::getText($user_id, 'Start', 'begin');
        $data_text['parse_mode'] = 'html';
        $data_text['reply_markup'] = $inline_keyboard;
        $send_text = Request::sendMessage($data_text);

//        $check_win = BotRaffleController::checkUserRaffleWin($user_id);
//        if ($check_win == 0) {
//            MenuCommandController::execute($user_id, '', null);
//        }
//        else {
//            $data_text = ['chat_id' => $user_id];
//            $data_text['text'] = BotTextsController::getText($user_id, 'Raffle', 'message_ask_add_action_pizza');
//            $data_text['parse_mode'] = 'html';
//            $data_text['reply_markup'] = BotRaffleButtonsController::get_ask_pizza_add_buttons($user_id);
//            $send_text = Request::sendMessage($data_text);
//            if ($send_text->getResult() !== null) {
//                $message_id = $send_text->getResult()->getMessageId();
//                BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'to_delete_message_id', $message_id);
//            }
//        }
        BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'order_message_id', null);
//        // Вытягиваем из БД текст для сообщения
//        $data_text = ['chat_id' => $user_id];
//        $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
//        $data_text['parse_mode'] = 'html';
//
//        $send_text = Request::sendMessage($data_text);

    }

    public static function begin_ask_action_pizza($user_id) {

        $command = 'Begin';
        $name = 'begin';

        $checkUserActive = self::checkUserActive($user_id);

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command);

        BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'to_delete_message_id', null);

        $check_win = BotRaffleController::checkUserRaffleWin($user_id);
        if ($check_win == 0) {
//            MenuCommandController::execute($user_id, '', null);
            self::begin($user_id);
        }
        else {
            $data_text = ['chat_id' => $user_id];
            $data_text['text'] = BotTextsController::getText($user_id, 'Raffle', 'message_ask_add_action_pizza');
            $data_text['parse_mode'] = 'html';
            $data_text['reply_markup'] = BotRaffleButtonsController::get_ask_pizza_add_buttons($user_id);
            $send_text = Request::sendMessage($data_text);
            if ($send_text->getResult() !== null) {
                $message_id = $send_text->getResult()->getMessageId();
                BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'to_delete_message_id', $message_id);
            }
        }
        BotUsersNavController::deleteMessageAndUpdateMessageId($user_id, 'order_message_id', null);
//        // Вытягиваем из БД текст для сообщения
//        $data_text = ['chat_id' => $user_id];
//        $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
//        $data_text['parse_mode'] = 'html';
//
//        $send_text = Request::sendMessage($data_text);

    }

    public static function contact_us($user_id)
    {
        $checkUserActive = self::checkUserActive($user_id);

        $command = 'ContactUs';
        $name = 'contactus';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command);

        // Вытягиваем из БД текст для сообщения
        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_text['parse_mode'] = 'html';

        // Формируем кнопки Inline
        $inline_keyboard = BotButtonsInlineController::getButtonsInline($user_id, $command, null);

        $data_text['reply_markup'] = $inline_keyboard;
        $send_text = Request::sendMessage($data_text);

    }

    public static function call_operator($user_id)
    {

        $checkUserActive = self::checkUserActive($user_id);

        $command = 'ContactUs';
        $name = 'call_operator';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command);

        // Вытягиваем из БД текст для сообщения
        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_text['parse_mode'] = 'html';

        $send_text = Request::sendMessage($data_text);

    }

    public static function change_lang($user_id) {

        $checkUserActive = self::checkUserActive($user_id);

        $command = 'Lang';
        $name = 'lang';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command);

        // Вытягиваем из БД текст для сообщения
        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_text['parse_mode'] = 'html';

        // Формируем кнопки Inline
        $inline_keyboard = new InlineKeyboard([]);
        $langs = BotSettingsLang::where('enabled', 1)->orderBy('id', 'asc')->get();
        foreach ($langs as $lang) {
            $inline_keyboard->addRow(
                new InlineKeyboardButton(['text' => $lang['emoji'].$lang['name'], 'callback_data' => 'change_user_lang___'.$lang['value']])
            );
        }

        $data_text['reply_markup'] = $inline_keyboard;
        $send_text = Request::sendMessage($data_text);

    }

    public static function share($user_id) {

        $checkUserActive = self::checkUserActive($user_id);

        $command = 'Share';
        $name = 'share';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command);

        // Вытягиваем из БД текст для сообщения
        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_text['parse_mode'] = 'html';
        $send_text = Request::sendMessage($data_text);

        $text_ins = BotTextsController::getText($user_id, $command, 'text');
        $text_ins = str_replace("___USER_ID___", $user_id, $text_ins);
        $text_ins = str_replace("___BOT_NAME___", env('PHP_TELEGRAM_BOT_NAME'), $text_ins);

        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = $text_ins;
        $data_text['parse_mode'] = 'html';
        $data_text['disable_web_page_preview'] = true;
        $send_text = Request::sendMessage($data_text);

        $inline_keyboard = new InlineKeyboard([]);
        list($text1, $data1) = BotButtonsInlineController::getButtonInline($user_id, 'More', 'share');
        $inline_keyboard->addRow(
            new InlineKeyboardButton(['text' => $text1, 'switch_inline_query' => $data1])
        );

        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = BotTextsController::getText($user_id, $command, 'share2');
        $data_text['parse_mode'] = 'html';
        $data_text['reply_markup'] = $inline_keyboard;
        $send_text = Request::sendMessage($data_text);

        // Формируем кнопки Inline
//        $inline_keyboard = new InlineKeyboard([]);
//        $langs = BotSettingsLang::orderBy('id', 'asc')->get();
//        foreach ($langs as $lang) {
//            $inline_keyboard->addRow(
//                new InlineKeyboardButton(['text' => $lang['emoji'].$lang['name'], 'callback_data' => 'change_user_lang___'.$lang['value']])
//            );
//        }
//
//        $data_text['reply_markup'] = $inline_keyboard;

    }

}
