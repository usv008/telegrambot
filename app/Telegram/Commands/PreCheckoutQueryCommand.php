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
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

/**
 * Callback query command
 */
class PreCheckoutQueryCommand extends UserCommand
{
    /**
     * @var callable[]
     */
    protected static $callbacks = [];

    /**
     * @var string
     */
    protected $name = 'precheckoutquery';

    /**
     * @var string
     */
    protected $description = 'Reply to Pre Checkout query';

    /**
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Command execute method
     *
     * @return mixed
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute(): ServerResponse
    {
        // $callback_query = $this->getUpdate()->getCallbackQuery();
        // $user_id        = $callback_query->getFrom()->getId();
        // $query_id       = $callback_query->getId();
        // $query_data     = $callback_query->getData();

        // $text    = json_encode($this->getUpdate()->getPreCheckoutQuery());
        //
        // $data = [
        //     'chat_id' => 522750680,
        //     'text'    => $text,
        // ];

        // Request::sendMessage($data);

        //exit;
        // Call all registered callbacks.
        foreach (self::$callbacks as $callback) {
            $callback($this->getUpdate()->getPreCheckoutQuery());
        }

//         $text    = json_encode($this->getUpdate()->getPreCheckoutQuery());
//         $data = [
//             'chat_id' => 522750680,
//             'text'    => 'PreCheckoutQuery: '.$text,
//         ];
//         Request::sendMessage($data);

        return Request::answerPreCheckoutQuery([
            'pre_checkout_query_id' => $this->getUpdate()->getPreCheckoutQuery()->getId(),
            'ok'                    => true,
        ]);

        $this->telegram->executeCommand('successfulpayment');
    }

    /**
     * Add a new callback handler for callback queries.
     *
     * @param $callback
     */
    public static function addCallbackHandler($callback)
    {
        self::$callbacks[] = $callback;
    }
}
