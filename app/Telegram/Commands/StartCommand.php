<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use App\Models\BotUser;
use App\Models\BotUsersNav;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Commands\UserCommands\OrderCommand;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

use Longman\TelegramBot\Conversation;

use App\Http\Controllers\Telegram\StartCommandController;

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class StartCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'start';

    /**
     * @var string
     */
    protected $description = 'Start command';

    /**
     * @var string
     */
    protected $usage = '/start - Старт';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    /**
     * @var bool
     */
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
        $text    = trim($message->getText(true));
        $chat_id = $chat->getId();
        $user_id = $user->getId();

//        $is_bot = $user->getIsBot();
//        $username = $user->getUsername();
        $first_name = $user->getFirstName();
//        $last_name = $user->getLastName();
//        $language_code = $user->getLanguageCode();

        $result = Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);

        $date_z = date("Y-m-d H:i:s");
        BotUsersNav::updateOrCreate(
            ['user_id' => $user_id],
            ['firstname' => $first_name, 'date_z' => $date_z]
        );

        $checkUserActive = StartCommandController::checkUserActive($user_id);

        if ($chat->isGroupChat() || $chat->isSuperGroup()) {
            $data['reply_markup'] = Keyboard::forceReply(['selective' => true]);
        }

        //Conversation start
        $this->conversation = new Conversation($user_id, $chat_id, $this->getName());

        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];

        //cache data from the tracking session if any
        $state = 0;
        if (isset($notes['state'])) {
            $state = $notes['state'];
        }

        if ($message->getPhoto() !== null) {

            if ($user_id == 522750680) {
                $photo = $message->getPhoto()[0];
                $data_t = ['chat_id' => $user_id];
                $data_t['text'] = $photo->getFileId();
                $send_t = Request::sendMessage($data_t);

                $data_admin = ['chat_id' => $user_id];
                $photo = $message->getPhoto()[0];
                $data_admin['photo'] = $photo->getFileId();
                if ($message->getCaption() !== null && $message->getCaption() !== '') {
                    $data_admin['caption'] = '<a href="https://telegrambot.ecopizza.com.ua/admin/bot/users/'.$user_id.'">'.$user_id.'</a> написал в бот: '.PHP_EOL.$message->getCaption();
                }
                $data_admin['parse_mode'] = 'html';
                $send_t = Request::sendPhoto($data_admin);
                $result = null;
                return $result;
            }
            StartCommandController::sendPhotoToOperatorsChat($message);
        }

        switch ($state) {
            case 0:

                if (strtolower($text) == 'start') {
                    $this->conversation->stop();
                    $update = json_decode($this->update->toJson(), true);
                    $update['message']['text'] = '';
                    return (new StartCommand($this->telegram, new Update($update)))->preExecute();
                }
                else {
                    StartCommandController::process_text($user_id, $text, 'start');
                    $this->conversation->update();
                }

        }
        return $result;

    }

}
