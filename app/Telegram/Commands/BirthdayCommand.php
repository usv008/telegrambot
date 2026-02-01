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

use App\Http\Controllers\Telegram\BotButtonsController;
use App\Http\Controllers\Telegram\BotCartController;
use App\Http\Controllers\Telegram\BotCashbackController;
use App\Http\Controllers\Telegram\BotOrderController;
use App\Http\Controllers\Telegram\BotSettingsController;
use App\Http\Controllers\Telegram\BotTextsController;
use App\Http\Controllers\Telegram\BotUserHistoryController;
use App\Http\Controllers\Telegram\BotUsersNavController;
use App\Http\Controllers\Telegram\CashbackCommandController;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

use Longman\TelegramBot\Exception;

use Longman\TelegramBot\Telegram;


/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class BirthdayCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'birthday';

    /**
     * @var string
     */
    protected $description = 'День рождения';

    /**
     * @var string
     */
    protected $usage = '/birthday';

    protected $version = '1.1.1';

    protected $need_mysql = true;

    protected $private_only = true;

    protected $conversation;

    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */

    public function execute(): ServerResponse
    {

        $command = 'Birthday';
        $message = $this->getMessage();
        $chat    = $message->getChat();
        $user    = $message->getFrom();
        $text_birthday = trim($message->getText(true));
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
        if (isset($notes['state_birthday'])) {
            $state = $notes['state_birthday'];
        }

        $result = Request::emptyResponse();

        $buttons_yes = BotButtonsController::getButtons($user_id,'Birthday', ['yes']);
        $buttons_no = BotButtonsController::getButtons($user_id,'Birthday', ['no']);

        $message_cart_id = BotUsersNavController::getCartMessageId($user_id);
        $inline_keyboard = new InlineKeyboard([]);
        $data_edit = ['chat_id' => $user_id];
        $data_edit['reply_markup'] = $inline_keyboard;
        $data_edit['message_id'] = $message_cart_id;
        Request::editMessageReplyMarkup($data_edit);

        switch ($state) {

            case 0:
                conv0:
                if (in_array($text_birthday, $buttons_yes)) {

                    BotUsersNavController::updateValue($user_id, 'birthday', 1);

                    BotUserHistoryController::insertToHistory($user_id, 'send', 'Birthday - YES - '.$text_birthday);

                    $text_ins = BotTextsController::getText($user_id, $command, 'message_ok');
                    $data_text = ['chat_id' => $user_id];
                    $data_text['text'] = $text_ins;
                    $data_text['parse_mode'] = 'html';
                    $data_text['reply_markup'] = Keyboard::remove(['selective' => true]);;
                    $send_text = Request::sendMessage($data_text);

                    $notes['state_birthday'] = 0;
                    $this->conversation->stop();
                    $update = json_decode($this->update->toJson(), true);
                    $update['message']['text'] = '';

                    return (new OrderCommand($this->telegram, new Update($update)))->preExecute();
                    break;

                }
                elseif (in_array($text_birthday, $buttons_no)) {

                    BotUsersNavController::updateValue($user_id, 'birthday', null);

                    BotUserHistoryController::insertToHistory($user_id, 'send', 'Birthday - NO - '.$text_birthday);

                    $text_ins = BotTextsController::getText($user_id, $command, 'message_no');
                    $data_text = ['chat_id' => $user_id];
                    $data_text['text'] = $text_ins;
                    $data_text['parse_mode'] = 'html';
                    $data_text['reply_markup'] = Keyboard::remove(['selective' => true]);;
                    $send_text = Request::sendMessage($data_text);

                    $notes['state_birthday'] = 0;
                    $this->conversation->stop();
                    $update = json_decode($this->update->toJson(), true);
                    $update['message']['text'] = '';

                    return (new OrderCommand($this->telegram, new Update($update)))->preExecute();
                    break;

                }
                else {

                    BotUserHistoryController::insertToHistory($user_id, 'send', 'Birthday - UNKNOWN - '.$text_birthday);

                    $text_ins = BotTextsController::getText($user_id, $command, 'message');
                    $data_text = ['chat_id' => $user_id];
                    $data_text['text'] = $text_ins;
                    $data_text['parse_mode'] = 'html';

                    $keyboard_bottom = new Keyboard([]);
                    foreach ($buttons_yes as $button) {
                        $keyboard_bottom->addRow($button);
                    }
                    foreach ($buttons_no as $button) {
                        $keyboard_bottom->addRow($button);
                    }
                    $keyboard_b = $keyboard_bottom
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data_text['reply_markup'] = $keyboard_b;

                    $send_text = Request::sendMessage($data_text);

                }

        }

        return $result;

    }

}
