<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpTelegramBot\Laravel\PhpTelegramBotContract;

class TelegramController extends Controller {

    // public function handle(PhpTelegramBotContract $telegram_bot) {
    //     // Call handle method
    //     $telegram_bot->handle();
    // }

    public function webhook(PhpTelegramBotContract $telegram_bot) {
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
