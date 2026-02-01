<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request as LRequest;
use Longman\TelegramBot\Request;

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Entities\Update;

class CmdObjController extends Controller
{
    public static function execute($command, $telegram, $callback_query) {

        if ($cmdobj = $telegram->getCommandObject($command)) {
            $new_message_array = json_decode($callback_query->getMessage()->toJson(), true);
            $new_message_array['from'] = $new_message_array['chat'];
            $new_message_array['text'] = '';
            $new_update = new Update(
                ['update_id' => '-1', 'message' => $new_message_array],
//                $callback_query->getBotName()
                ''
            );
            $cmdobj->setUpdate($new_update)->preExecute();
        }

    }

}
