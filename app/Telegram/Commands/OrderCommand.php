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
use App\Http\Controllers\Telegram\BotUsersNavController;
use App\Http\Controllers\Telegram\CartCommandController;
use App\Http\Controllers\Telegram\StartCommandController;
use Longman\TelegramBot\Commands\SystemCommands\StartCommand;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

use App\Http\Controllers\Telegram\MenuCommandController;

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class OrderCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'order';

    /**
     * @var string
     */
    protected $description = 'order';

    /**
     * @var string
     */
    protected $usage = '/order';

    protected $version = '1.1.0';

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

        $message = $this->getMessage();
        $chat    = $message->getChat();
        $user    = $message->getFrom();
        $text    = trim($message->getText(true));
        $chat_id = $chat->getId();
        $user_id = $user->getId();

        $checkUserActive = StartCommandController::checkUserActive($user_id);

//        $a = 1;
//        $is_bot = $user->getIsBot();
//        $username = $user->getUsername();
//        $first_name = $user->getFirstName();
//        $last_name = $user->getLastName();
//        $language_code = $user->getLanguageCode();

        $result = Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        if ($chat->isGroupChat() || $chat->isSuperGroup()) {
            $data['reply_markup'] = Keyboard::forceReply(['selective' => true]);
        }

        //Conversation start
        $this->conversation = new Conversation($user_id, $chat_id, $this->getName());

        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];

        $buttons_back = BotButtonsController::getButtons($user_id,'System', ['back']);

        //cache data from the tracking session if any
        $state = 0;
        if (isset($notes['state'])) {
            $state = $notes['state'];
        }

        switch ($state) {
            case 0:

                if ($message->getContact() === null && $message->getLocation() == null) {

                    if ($text == 'start') {
                        $this->conversation->stop();
                        $update = json_decode($this->update->toJson(), true);
                        $update['message']['text'] = '';
                        return (new StartCommand($this->telegram, new Update($update)))->preExecute();
                    }
                    else {
                        if ($message->getPhoto() !== null) {
                            StartCommandController::sendPhotoToOperatorsChat($message);
                        }
                        else StartCommandController::process_text($user_id, $text, 'order');
                        $this->conversation->update();
                    }

                }
                elseif ($message->getContact() !== null) {

                    $phone = $message->getContact()->getPhoneNumber();
                    while (stripos($phone, '+') !== false) {
                        $phone = str_replace("+", "", $phone);
                    }
                    $text = $phone;
                    StartCommandController::process_text($user_id, $phone, 'cart');
                    $this->conversation->update();

                }
                elseif ($message->getLocation() !== null) {

                    $lat = $message->getLocation()->getLatitude();
                    $lng = $message->getLocation()->getLongitude();

                    $text = 'Location';
                    StartCommandController::process_text($user_id, ['lat' => $lat, 'lng' => $lng], 'cart');
                    $this->conversation->update();

                }

        }

        return $text == '' || in_array($text, $buttons_back) ? CartCommandController::execute($user_id, 'send', null) : $result;

    }

}
