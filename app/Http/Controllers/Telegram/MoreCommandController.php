<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotMenu;
use App\Models\BotOrder;
use App\Models\BotRaffle;
use App\Models\BotSettingsSticker;
use App\Models\BotUsersNav;
use App\Http\Controllers\Controller;

use App\Models\Simpla_Categories;
use App\Models\Simpla_Images;
use App\Models\Simpla_Products;
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
use SebastianBergmann\CodeCoverage\Report\PHP;

class MoreCommandController extends Controller
{

    public static function execute($user_id)
    {

        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        $command = 'More';
        $name = 'more';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command);

        // Вытягиваем из БД текст для сообщения
        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_text['parse_mode'] = 'html';

        // Формируем кнопки Inline
        $inline_keyboard = BotMoreButtonsController::get_more_buttons($user_id);

        $data_text['reply_markup'] = $inline_keyboard;
        $send_text = Request::sendMessage($data_text);

    }

    public static function send_actions($user_id)
    {

        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        $command = 'More';
        $name = 'actions';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command.' ('.$name.')');

        // Вытягиваем из БД текст для сообщения
        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_text['parse_mode'] = 'html';
        $send_text = Request::sendMessage($data_text);

        $data = ['chat_id' => $user_id];

        $actions = BotMoreController::getActions($user_id);
        $total = count($actions);
        $counter = 0;
        foreach ($actions as $action) {

            $counter++;
            $data['caption'] = '<b>'.$action['name'].'</b>'.PHP_EOL.PHP_EOL;

            $text = strip_tags($action['annotation']);
            $text = preg_replace('/\,(?!\,)/', ', ', $text);
            $text = str_replace("&nbsp;"," ",$text);
            $text = str_replace("&amp;","&",$text);

            $data['caption'] .= $text;
            $data['photo']   = 'http://ecopizza.com.ua/files/shares/'.$action['image'];
            $data['parse_mode'] = 'html';

            if($counter == $total){
                $data['reply_markup'] = BotMoreButtonsController::get_actions_buttons($user_id);
            }

            Request::sendPhoto($data);

        }

    }

    public static function get_reviews($user_id)
    {

        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        $command = 'More';
        $name = 'reviews';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command.' ('.$name.')');

        // Вытягиваем из БД текст для сообщения
        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_text['parse_mode'] = 'html';
        $send_text = Request::sendMessage($data_text);

        $data = ['chat_id' => $user_id];

        $text = '';
        $reviews = BotMoreController::getReviews($user_id);
        foreach ($reviews as $review) {

            $text .= '<b>'.$review['user_name'].'</b> ('.date("d.m.Y", strtotime($review['date_reg'])).')'.PHP_EOL;
            $text .= $review['review'].PHP_EOL.PHP_EOL.PHP_EOL;

        }
        $data['text'] = $text;
        $data['parse_mode'] = 'html';
        $data['reply_markup'] = BotMoreButtonsController::get_reviews_buttons($user_id);
        Request::sendMessage($data);

    }

    public static function send_review($user_id)
    {

        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        $command = 'More';
        $name = 'send_review';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command.' ('.$name.')');

        // Вытягиваем из БД текст для сообщения
        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_text['parse_mode'] = 'html';

        $keyboard_bottom = new Keyboard([]);

        $buttons = BotButtonsController::getButtons($user_id, 'System', ['cancel']);
        foreach ($buttons as $button) {
            $keyboard_bottom->addRow($button);
        }

        $keyboard_b = $keyboard_bottom
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->setSelective(false);
        $data_text['reply_markup'] = $keyboard_b;

        $send_text = Request::sendMessage($data_text);
        BotUsersNavController::updateValue($user_id, 'change_key', 'send_review');

    }

    public static function send_review_ok($user_id)
    {

        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        $command = 'More';
        $name = 'send_review_ok';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command.' ('.$name.')');

        $remove_keyboard = StartCommandController::removeKeyboardBottom($user_id);

        // Вытягиваем из БД текст для сообщения
        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = BotTextsController::getText($user_id, $command, $name);
        $data_text['parse_mode'] = 'html';
        $data_text['reply_markup'] = BotMoreButtonsController::get_send_review_ok_buttons($user_id);

        $send_text = Request::sendMessage($data_text);
        BotUsersNavController::updateValue($user_id, 'change_key', 'send_review');

    }

    public static function send_cashback($user_id)
    {

        Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        $command = 'More';
        $name = 'send_cashback';

        // Записываемся в историю
        BotUserHistoryController::insertToHistory($user_id, 'open', $command.' ('.$name.')');

        $user_cashback = BotCashbackController::getUserCashback($user_id);
        $user_cashback_action = BotCashbackController::getUserCashbackAction($user_id);
        $currency = BotTextsController::getText($user_id, 'System', 'currency');
        $balance = BotTextsController::getText($user_id, 'More', 'cashback_balance');
        $plus = BotTextsController::getText($user_id, 'More', 'cashback_plus');
        $minus = BotTextsController::getText($user_id, 'More', 'cashback_minus');

        $data = ['chat_id' => $user_id];

        $text = BotTextsController::getText($user_id, $command, 'cashback_history').PHP_EOL.PHP_EOL;
        $vals = BotCashbackController::getAllUserCashback($user_id);
        foreach ($vals as $val) {

            if ($val['summa'] > 0) {
                $type = '';
                if ($val['type'] == 'IN') $type = $plus;
                elseif ($val['type'] == 'OUT') $type = $minus;

                $text .= '<b>'.date("d.m.Y H:i:s", strtotime($val['date_z'])).'</b>'.PHP_EOL;
                $text .= $type.$val['summa'].' '.$currency.PHP_EOL.$balance.$val['balance'].' '.$currency.PHP_EOL.PHP_EOL;
            }

        }
        $data['text'] = $text;
        $data['parse_mode'] = 'html';
        Request::sendMessage($data);

        $text = BotTextsController::getText($user_id, $command, $name);
        $text = str_replace("___CASHBACK___", $user_cashback, $text);
        $text = str_replace("___CASHBACK_ACTION___", $user_cashback_action, $text);
        $text = str_replace("___CURRENCY___", $currency, $text);

        $data_text = ['chat_id' => $user_id];
        $data_text['text'] = $text;
        $data_text['parse_mode'] = 'html';
        $data_text['reply_markup'] = BotMoreButtonsController::get_cashback_buttons($user_id);
        $send_text = Request::sendMessage($data_text);

    }

}
