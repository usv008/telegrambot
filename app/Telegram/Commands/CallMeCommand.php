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
use Longman\TelegramBot\Commands\UserCommand;

use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

use App\Http\Controllers\Telegram\CallMeCommandController;

class CallMeCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'callme';

    /**
     * @var string
     */
    protected $description = 'Перезвонить мне';

    /**
     * @var string
     */
    protected $usage = '/callme - Перезвонить мне';

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

        $message = $this->getMessage();
        $chat    = $message->getChat();
        $user    = $message->getFrom();
        $text = trim($message->getText(true));
        $chat_id = $chat->getId();
        $user_id = $user->getId();

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
        if (isset($notes['state_contact'])) {
            $state = $notes['state_contact'];
        }

        $result = Request::emptyResponse();

        $buttons_home = BotButtonsController::getButtons($user_id,'System', ['home', 'cancel']);
        $buttons_back = BotButtonsController::getButtons($user_id,'System', ['back']);
        $buttons_skip = BotButtonsController::getButtons($user_id,'System', ['skip']);
        $buttons_success = BotButtonsController::getButtons($user_id,'System', ['success']);

        $pattern_tel1 = "/0\d{2}\d{3}\d{2}\d{2}/";
        $pattern_tel2 = "/80\d{2}\d{3}\d{2}\d{2}/";
        $pattern_tel3 = "/380\d{2}\d{3}\d{2}\d{2}/";

        switch ($state) {

            case 0:
                conv0:
                if (in_array($text, $buttons_home)) {

                    $this->conversation->stop();
                    $this->telegram->executeCommand('start');
                    break;

                }
                elseif (!in_array($text, $buttons_back) && $text !== '') {

                    $notes['user_name'] = $text;
                    $this->conversation->update();
                    $text = '';

                }
                else {

                    $notes['state_contact'] = 0;
                    $this->conversation->update();

                    CallMeCommandController::enter_name($user_id, $text);
                    break;

                }

            case 1:
                conv1:

                if ($message->getContact() === null) {

                    if (in_array($text, $buttons_home)) {

                        $this->conversation->stop();
                        $this->telegram->executeCommand('start');
                        break;

                    }
                    elseif ( $text !== ''
                        && is_numeric($text)
                        && (
                            (preg_match($pattern_tel1, $text) && strlen($text) == 10)
                            || (preg_match($pattern_tel2, $text) && strlen($text) == 11)
                            || (preg_match($pattern_tel3, $text) && strlen($text) == 12)
                        )
                    ) {

                        $tel_ins = $text;
                        while (stripos($tel_ins, '+') !== false) {
                            $tel_ins = str_replace("+", "", $tel_ins);
                        }

                        $notes['user_phone'] = $text;
                        $this->conversation->update();
                        $text = '';

                        CallMeCommandController::send_ok($user_id, $notes['user_name'], $notes['user_phone']);
                        $this->conversation->stop();

                    }
                    else {

                        $notes['state_contact'] = 1;
                        $this->conversation->update();
                        CallMeCommandController::enter_phone($user_id, $text);
                        break;

                    }

                }
                else {

                    $tel_ins = $message->getContact()->getPhoneNumber();
                    while (stripos($tel_ins, '+') !== false) {
                        $tel_ins = str_replace("+", "", $tel_ins);
                    }
                    $notes['user_phone'] = $tel_ins;
                    CallMeCommandController::send_ok($user_id, $notes['user_name'], $notes['user_phone']);
                    $this->conversation->stop();

                }

//             $data_t = ['chat_id' => $user_id];
//             $data_t['text'] = 'debug: next-'.$select_val.'; nav-'.$nav.'; text-'.$text_reserve;
//             $send_t = Request::sendMessage($data_t);

        }
        return $result;

    }

}
