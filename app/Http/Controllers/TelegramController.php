<?php

namespace App\Http\Controllers;

use App\Services\WorkingHoursService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use PhpTelegramBot\Laravel\PhpTelegramBotContract;

class TelegramController extends Controller {

    // public function handle(PhpTelegramBotContract $telegram_bot) {
    //     // Call handle method
    //     $telegram_bot->handle();
    // }

    public function webhook(PhpTelegramBotContract $telegram_bot) {
        // Check working hours for full blocking mode
        if (WorkingHoursService::getBlockingMode() === 'full') {
            $status = WorkingHoursService::isCurrentlyOpen();
            if (!$status['is_open']) {
                $input = json_decode(file_get_contents('php://input'), true);
                $chatId = null;
                $messageText = null;

                if (isset($input['message'])) {
                    $chatId = $input['message']['chat']['id'] ?? null;
                    $messageText = $input['message']['text'] ?? null;
                } elseif (isset($input['callback_query'])) {
                    $chatId = $input['callback_query']['message']['chat']['id'] ?? null;
                }

                // Allow /start command to pass through (don't lose new users)
                if ($messageText !== null && strpos($messageText, '/start') === 0) {
                    $telegram_bot->handle();
                    return;
                }

                if ($chatId) {
                    $closedMessage = WorkingHoursService::getClosedMessage();
                    if ($closedMessage) {
                        // Send message directly via Telegram HTTP API (without processing the update)
                        $apiKey = config('phptelegrambot.bot.api_key');
                        Http::post("https://api.telegram.org/bot{$apiKey}/sendMessage", [
                            'chat_id' => $chatId,
                            'text' => $closedMessage,
                            'parse_mode' => 'HTML',
                        ]);
                        return;
                    }
                }
            }
        }

        // Call handle method
        $telegram_bot->handle();
    }

    public function setWebhook(PhpTelegramBotContract $telegram_bot) {
        // Set webhook
        $hook_url = env('PHP_TELEGRAM_BOT_URL').env('PHP_TELEGRAM_BOT_API_KEY');
        echo ($telegram_bot->setWebhook($hook_url));
    }

    public function unsetWebhook(PhpTelegramBotContract $telegram_bot) {
        // UnSet webhook
        echo $telegram_bot->deleteWebhook();
    }

}
