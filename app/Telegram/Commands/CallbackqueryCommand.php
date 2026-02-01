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

use App\Http\Controllers\Telegram\StartCommandController;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

use Exception;
use Longman\TelegramBot\Telegram;

use Longman\TelegramBot\Entities\Update;

use App\Http\Controllers\Telegram\CallbackqueryCommandController;

/**
 * Callback query command
 *
 * This command handles all callback queries sent via inline keyboard buttons.
 *
 * @see InlinekeyboardCommand.php
 */
class CallbackqueryCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'callbackquery';

    /**
     * @var string
     */
    protected $description = 'Reply to callback query';

    /**
     * @var string
     */
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

    //protected $need_mysql = false;

    public function execute(): ServerResponse
    {

        $callback_query    = $this->getUpdate()->getCallbackQuery();
        $callback_query_id = $callback_query->getId();
        $callback_data     = $callback_query->getData();
        $callback_game     = $callback_query->getGameShortName();

        $chat_id = $callback_query->getMessage()->getChat()->getId();
        $message_id = $callback_query->getMessage()->getMessageId();
        $user_id = $callback_query->getFrom()->getId();
        $username = $callback_query->getFrom()->getUsername();
        $firstname = $callback_query->getFrom()->getFirstName();

        $checkUserActive = StartCommandController::checkUserActive($user_id);

//        $data = ['chat_id' => 522750680];
//        $data['text'] = 'data: '.$callback_query->getGameShortName();
//        $send = Request::sendMessage($data);

        // Обрабатываем запрос
        if ($callback_game !== null && $callback_game !== '')
            list($text, $show_alert, $url) = CallbackqueryCommandController::gameExecute($callback_game, $user_id);
        else
            list($text, $show_alert, $url) = CallbackqueryCommandController::index($user_id, $callback_data, $this->getTelegram(), $callback_query, $firstname);

        $data_callback = [
            'callback_query_id' => $callback_query_id,
            'text'              => $text,
            'show_alert'        => $show_alert,
//            'url'               => $url.'?user_id='.$user_id,
            'cache_time'        => 0
        ];

        return Request::answerCallbackQuery($data_callback);

    }

    public static function addCallbackHandler($callback)
    {
        self::$callbacks[] = $callback;
    }

}
