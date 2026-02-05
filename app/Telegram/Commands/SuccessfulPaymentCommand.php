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

use App\Models\BotOrdersNew;
use App\Http\Controllers\Telegram\BotTextsController;
use App\Http\Controllers\Telegram\StartCommandController;
use App\Models\PrestaShop_Orders;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;

//use Longman\TelegramBot\OzziClass;

/**
 * Callback query command
 */
class SuccessfulPaymentCommand extends UserCommand
{
    /**
     * @var callable[]
     */
    protected static $callbacks = [];

    /**
     * @var string
     */
    protected $name = 'successfulpayment';

    /**
     * @var string
     */
    protected $description = 'Echo SuccessfulPayment';

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

        $message = $this->getMessage();
        $chat    = $message->getChat();
        $chat_id = $chat->getId();
        $user    = $message->getFrom();
        $user_id = $user->getId();

        $a = 1;
        $is_bot = $user->getIsBot();
        $username = $user->getUsername();
        $first_name = $user->getFirstName();
        $last_name = $user->getLastName();
        $language_code = $user->getLanguageCode();

        // Call all registered callbacks.
        foreach (self::$callbacks as $callback) {
            $callback($this->getMessage()->getSuccessfulPayment());
        }

        $message_text = $this->getMessage();
        $text = json_encode($this->getMessage()->getSuccessfulPayment());
        if ( ($text !== null) && ($text !== '') ) {

            Log::info('LIQPAY CALLBACK received', ['payment_data' => $text]);
            $inv = json_decode($text);
            $invoice_payload = $inv->{'invoice_payload'};

            if ($invoice_payload !== null && $invoice_payload !== '') {

                BotOrdersNew::where('external_id', $invoice_payload)->update(['pay_yes' => 1]);
                PrestaShop_Orders::where('id_order', $invoice_payload)->update(['current_state' => 14]);
//                SimplaOrders::where('id', $invoice_payload)->update(['paid' => 1, 'payment_date' => date("Y-m-d H:i:s")]);
//                SimplaOrdersDuble::where('id', $invoice_payload)->update(['paid' => 1, 'payment_date' => date("Y-m-d H:i:s")]);

                $text_ins = BotTextsController::getText($user_id, 'LiqPay', 'success');
                $text_ins = str_replace("___ID___", $invoice_payload, $text_ins);

                Log::info('LiqPay payment success', ['order_id' => $invoice_payload]);

                $data['parse_mode'] = 'html';
                $data['text']      = $text_ins;
                $data['chat_id']      = $user_id;
                Request::sendMessage($data);

                Request::sendChatAction(['chat_id' => $user_id, 'action' => 'typing']);
                sleep(3);
                return StartCommandController::send_hello($user_id);

            }
            else {

                $data = [
                    'chat_id' => $user_id,
                    'text'    => BotTextsController::getText($user_id, 'LiqPay', 'error'),
                ];
                return Request::sendMessage($data);

            }

        }
        else {

            $data = [
                'chat_id' => $user_id,
                'text'    => BotTextsController::getText($user_id, 'LiqPay', 'error'),
            ];
            return Request::sendMessage($data);

        }

        // return Request::answerPreCheckoutQuery([
        //     'pre_checkout_query_id' => $this->getUpdate()->getPreCheckoutQuery()->getId(),
        //     'ok'                    => true,
        // ]);
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
