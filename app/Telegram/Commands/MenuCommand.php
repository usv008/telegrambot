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

use App\Http\Controllers\Telegram\StartCommandController;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

use App\Http\Controllers\Telegram\MenuCommandController;

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class MenuCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'menu';

    /**
     * @var string
     */
    protected $description = 'Menu';

    /**
     * @var string
     */
    protected $usage = '/menu';

    protected $version = '1.1.0';

    protected $need_mysql = true;

    protected $private_only = true;

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
//        $text    = trim($message->getText(true));
        $chat_id = $chat->getId();
        $user_id = $user->getId();

        $checkUserActive = StartCommandController::checkUserActive($user_id);

//        $a = 1;
//        $is_bot = $user->getIsBot();
//        $username = $user->getUsername();
//        $first_name = $user->getFirstName();
//        $last_name = $user->getLastName();
//        $language_code = $user->getLanguageCode();

        Request::sendChatAction(['chat_id' => $chat_id, 'action' => 'typing']);

//        $result = Request::emptyResponse();

        $result = MenuCommandController::execute($user_id, 'send', null);

//        return $result;

    }

}
