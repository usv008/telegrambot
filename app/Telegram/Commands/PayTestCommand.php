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

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;


/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class PayTestCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'paytest';

    /**
     * @var string
     */
    protected $description = 'Тест оплаты';

    /**
     * @var string
     */
    protected $usage = '/paytest';

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
        $text    = trim($message->getText(true));
        $chat_id = $chat->getId();
        $user_id = $user->getId();

//        $a = 1;
//        $is_bot = $user->getIsBot();
//        $username = $user->getUsername();
//        $first_name = $user->getFirstName();
//        $last_name = $user->getLastName();
//        $language_code = $user->getLanguageCode();

        Request::sendChatAction(['chat_id' => $chat_id, 'action' => 'typing']);

        $result = Request::emptyResponse();

        $keyboard_bottom = new Keyboard(
            ['🏠 Начало', '🍴 Меню', '🛒 Корзина']
        );
        $keyboard_b = $keyboard_bottom
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(false)
            ->setSelective(false);

        $data_b = [
            'chat_id'      => $chat_id,
            'text'         => 'Ожидаю оплаты...',
            'reply_markup' => $keyboard_b,
        ];
        $result = Request::sendMessage($data_b);

//        //////////////////////////// LiqPay Invoice ////////////////////////////
//        $id_order = '123456';
//        $n_order = '78926';
//        $phone = '380955675764';
//        $liqpay = new LiqPay($ozzi->getBotSettings('liqpay_public_key')['settings_value'], $ozzi->getBotSettings('liqpay_private_key')['settings_value']);
//        $res = $liqpay->api("request", array(
//        'action'    => 'invoice_send',
//        'version'   => '3',
//        'amount'    => $price,
//        'currency'  => 'UAH',
//        'description'   => 'Заказ Eco&Pizza №'.$n_order.' через бота',
//        'order_id'  => 'id_'.$id_order.'___chat_'.$chat_id.'___user_'.$user_id.'___order_'.$n_order.'_'.rand(100000, 999999),
//        'action_payment' => 'pay',
//        'phone'  => $phone,
//        'email' => $phone,
//        'server_url' => 'https://bot.ecopizza.com.ua/Ozzi/liqpay_callback.php'
//        ));
//        $res = json_encode($res);
//        $res = json_decode($res);
//        if ($res->result == 'ok') {
//
//            $inline_keyboard = new InlineKeyboard(
//                [
//                    ['text' => '💳 Оплатить ' .$price. ' грн через платежный виджет', 'url' => $res->href]
//                ]
//            );
//            $data = [
//                'chat_id'      => $chat_id,
//                'text'         => 'Или ты можешь оплатить через платежный виджет',
//                'reply_markup' => $inline_keyboard,
//            ];
//            $result = Request::sendMessage($data);
//        }
//        //////////////////////// LiqPay Invoice End ////////////////////////////
//
//        $this->telegram->executeCommand('precheckoutquery');
//        //////////////////////// LiqPay End ////////////////////////////////////

        return $result;

    }

}
