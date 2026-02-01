<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use App\Models\BotSettingsCashback;
use App\Http\Controllers\Telegram\BotButtonsController;
use App\Http\Controllers\Telegram\BotCartController;
use App\Http\Controllers\Telegram\BotCashbackController;
use App\Http\Controllers\Telegram\BotOrderController;
use App\Http\Controllers\Telegram\BotSettingsController;
use App\Http\Controllers\Telegram\BotTextsController;
use App\Http\Controllers\Telegram\BotUsersNavController;
use App\Http\Controllers\Telegram\CashbackCommandController;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;


use Longman\TelegramBot\Exception;

use Longman\TelegramBot\Telegram;
use PDO;
use PDOException;

//use Longman\TelegramBot\OzziClass;

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class CashbackCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'cashback';

    /**
     * @var string
     */
    protected $description = 'Кешбэк';

    /**
     * @var string
     */
    protected $usage = '/cashback';

    protected $version = '1.1.1';

    protected $need_mysql = true;

    protected $private_only = true;

    protected $conversation;

    /**
     * Command execute method
     *
     * @return ServerResponse
     * @throws TelegramException
     */

    public function execute(): ServerResponse
    {

        $message = $this->getMessage();
        $chat    = $message->getChat();
        $user    = $message->getFrom();
        $text_cashback = trim($message->getText(true));
        $chat_id = $chat->getId();
        $user_id = $user->getId();

        $callback_query    = $this->getUpdate()->getMessage();

        $a = 1;
        $is_bot = $user->getIsBot();
        $username = $user->getUsername();
        $first_name = $user->getFirstName();
        $last_name = $user->getLastName();
        $language_code = $user->getLanguageCode();

        $result = Request::sendChatAction(['chat_id' => $chat_id, 'action' => 'typing']);

        $this->conversation = new Conversation($user_id, $chat_id, $this->getName());
        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];
        $state = 0;
        if (isset($notes['state_cashback'])) {
            $state = $notes['state_cashback'];
        }

        $result = Request::emptyResponse();

        $buttons_back = BotButtonsController::getButtons($user_id,'System', ['back']);

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

        $message_cart_id = BotUsersNavController::getCartMessageId($user_id);
        $inline_keyboard = new InlineKeyboard([]);
        $data_edit = ['chat_id' => $user_id];
        $data_edit['reply_markup'] = $inline_keyboard;
        $data_edit['message_id'] = $message_cart_id;
        Request::editMessageReplyMarkup($data_edit);

        switch ($state) {

            case 0:
                conv0:
                if (in_array($text_cashback, $buttons_back)) {

                    $notes['state_cashback'] = 0;
                    $this->conversation->stop();
                    $this->telegram->executeCommand('order');
                    break;

                }
                elseif (($total >= $min_sum_order || $delivery_discount == 1) && $text_cashback !== '' && (is_numeric($text_cashback) || is_float($text_cashback)) && $text_cashback >= 0 && $text_cashback <= $pay_cashback ) {

                    CashbackCommandController::show_message_ok($user_id, $text_cashback);
                    $text_cashback = '';
                    $notes['state_cashback'] = 0;
                    $this->conversation->stop();

                    $update = json_decode($this->update->toJson(), true);
                    $update['message']['text'] = '';

                    return (new OrderCommand($this->telegram, new Update($update)))->preExecute();
                    break;

                }
                else {

                    if (BotCashbackController::getUserCashback($user_id) > 0 || BotCashbackController::getUserCashbackAction($user_id) > 0) {

                        if ($total < 350 && BotCashbackController::getUserCashbackAction($user_id) > 0) {

                            CashbackCommandController::show_message_action_no($user_id);

                        }
                        CashbackCommandController::show_message($user_id);

                    }
                    else {

                        CashbackCommandController::show_message_no_cashback($user_id);
                        $text_cashback = '';
                        $notes['state_cashback'] = 0;
                        $this->conversation->stop();

                        $update = json_decode($this->update->toJson(), true);
                        $update['message']['text'] = '';
                        return (new OrderCommand($this->telegram, new Update($update)))->preExecute();
                        break;

                    }

                }

        }

        return $result;

    }

}
