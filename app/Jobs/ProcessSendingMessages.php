<?php

namespace App\Jobs;

use App\Models\BotSendingMessages;
use App\Models\BotSendingMessagesHistory;
use App\Models\BotUser;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\Telegram\BotStickerController;
use App\Models\LongmanBotUser;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;
use PhpTelegramBot\Laravel\PhpTelegramBotContract;

class ProcessSendingMessages implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $post_id;
    private $user_id;
    private $sticker;
    private $text;
    private $image;
    private $inline_keyboard;

    /**
     * Create a new job instance.
     * @param $data
     */
    public function __construct($data)
    {

        $this->post_id = $data['post_id'];
        $this->user_id = $data['user_id'];
        $this->sticker = $data['sticker'];
        $this->text = $data['text'];
        $this->image = $data['image'];
        $this->inline_keyboard = $data['inline_keyboard'];

    }

    /**
     * Execute the job.
     *
     * @param PhpTelegramBotContract $telegram_bot
     * @return void
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function handle(PhpTelegramBotContract $telegram_bot)
    {

//        Log::info("!!!JOB!!! user_id: $this->user_id; text: ".$this->text."; image: $this->image; ");

        if ($this->sticker !== '') {
            $data_sticker = ['chat_id' => $this->user_id];
            $data_sticker['sticker'] = $this->sticker;
            $data_sticker['reply_markup'] = Keyboard::remove(['selective' => true]);
            $send_sticker = Request::sendSticker($data_sticker);
        }

        $data_send = ['chat_id' => $this->user_id];
        $data_send['parse_mode'] = 'html';
        if ($this->inline_keyboard !== null) $data_send['reply_markup'] = $this->inline_keyboard;

        if ($this->image !== null) {

            $data_send['photo'] = $this->image;
            $data_send['caption'] = $this->text;
            $result = Request::sendPhoto($data_send);

        }
        else {

            $data_send['text'] = $this->text;
            $result = Request::sendMessage($data_send);

        }

//        Log::info("Result: ".$result);

        $sent = $result->getOk() == true ? 1 : 0;

        $history = new BotSendingMessagesHistory;
        $history->post_id = $this->post_id;
        $history->user_id = $this->user_id;
        $history->sent = $sent;
        $history->result = $sent === 0 ? (string)$result : '{}';
        $history->date_z = date("Y-m-d H:i:s");
        $history->save();

        $post = BotSendingMessages::where('id', $this->post_id)->first();
        $send_yes = $post['send_yes'] + $sent;
        $send_no = $sent == 0 ? $post['send_no'] + 1 : $post['send_no'];
        BotSendingMessages::where('id', $this->post_id)->update(['send_yes' => $send_yes, 'send_no' => $send_no]);

        if ($sent == 0) {
            $result_arr = json_decode($result, true);
            if (isset($result_arr['error_code']) && $result_arr['error_code'] == 403 && BotUser::where('user_id', $this->user_id)->count() == 1) {
                $updateBotUser = BotUser::where('user_id', $this->user_id)->update(['active' => 0]);
            }
        }

    }

}
