<?php

namespace App\Http\Controllers;

use App\Models\BotUser;
use App\Models\BotChatMessages;
use Illuminate\Support\Facades\Auth;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

use Illuminate\Http\Request as LRequest;

class BotChatController extends Controller
{

    public static function execute()
    {
        if (view()->exists('admin.bot')) {
            $messages_data = BotChatMessages::getUsersMessages();
            $messages_unreaded = BotChatMessages::getMessagesUnreaded();
            $data = [
                'title' => 'Чат',
                'page' => 'chat',
                'messages' => $messages_data['messages'],
                'users_messages' => $messages_data['users_messages'],
                'messages_unreaded' => $messages_unreaded,
            ];
            return view('admin.bot', $data);
        }
    }

    public static function loadChats()
    {
        $messages_data = BotChatMessages::getUsersMessages();
        $data = [
            'messages' => $messages_data['messages'],
            'users_messages' => $messages_data['users_messages'],
        ];
        return view('admin.chats_load_content', $data);
    }

    public static function chatUser($user_id)
    {
        $user = BotUser::getUser($user_id);
        $photo_small_arr = self::getUserPhotos($user_id);
        $users_messages_data = BotChatMessages::getUsersMessages();
        $users_messages = $users_messages_data['users_messages'];
        $messsages = BotChatMessages::getMessagesByUser($user_id);

        $firstname = isset($user->first_name) ? $user->first_name : '';
        $lastname = isset($user->last_name) ? $user->last_name : '';
        $user_ins = $firstname . ' ' . $lastname;
        $user_ins .= isset($user->username) && $user->username !== '' ? ' (' . $user->username . ')' : '';
        $messages_unreaded = BotChatMessages::getMessagesUnreaded();
        $data = [
            'title' => 'Чат с ' . '<a href="'.route('user', ['user' => $user_id]).'">'.$user_ins.'</a>',
            'page' => 'chat_user',
            'user_id' => $user_id,
            'user' => $user,
            'users_messages' => $users_messages,
            'messages' => $messsages,
            'photo_small_arr' => $photo_small_arr,
            'messages_unreaded' => $messages_unreaded,
        ];
        return view('admin.bot', $data);
    }

    public static function sendMessageToChat(LRequest $request)
    {
        $input = $request->except('_token');
        $user_id = $input['user_id'];
        $text = $input['text'];
        $telegram = new Telegram(env('PHP_TELEGRAM_BOT_API_KEY'), env('PHP_TELEGRAM_BOT_NAME'));
        $data = ['chat_id' => $user_id];
        $data['parse_mode'] = 'html';
        $data['text'] = $text;
        $result = Request::sendMessage($data);

        if ($result->getOk()) {
            $new_message = BotChatMessages::addNewMessage($user_id, $text);
            $photo_small_arr = self::getUserPhotos($user_id);
            $messsages = BotChatMessages::getMessagesByUser($user_id);

            $data = [
                'messages' => $messsages,
                'photo_small_arr' => $photo_small_arr,
            ];
            return view('admin.chat_messages_content', $data);
        } else return null;
    }

    public static function getUserPhotos($user_id)
    {
        $telegram = new Telegram(env('PHP_TELEGRAM_BOT_API_KEY'), env('PHP_TELEGRAM_BOT_NAME'));
        $data_photo = [
            'user_id' => $user_id,
            'limit' => 100,
            'offset' => 0,
        ];
        $result = Request::getUserProfilePhotos($data_photo);
        $photos = $result->getOk() ? $result->getResult()->photos : [];
        $photo_arr = [];
        $photo_small_arr = [];
        foreach ($photos as $photo) {
            $data_path = [
                'file_id' => $photo[0]['file_id'],
            ];
            $file_path = Request::getFile($data_path);
            if ($file_path->getOk()) $photo_small_arr[] = 'https://api.telegram.org/file/bot' . env('PHP_TELEGRAM_BOT_API_KEY') . '/' . $file_path->getResult()->file_path;
            $data_path = [
                'file_id' => $photo[2]['file_id'],
            ];
            $file_path = Request::getFile($data_path);
            if ($file_path->getOk()) $photo_arr[] = 'https://api.telegram.org/file/bot' . env('PHP_TELEGRAM_BOT_API_KEY') . '/' . $file_path->getResult()->file_path;
        }
        return $photo_small_arr;
    }

    public static function loadChatUsers(LRequest $request)
    {
        $input = $request->except('_token');
        $user_id = $input['user_id'];
        $messages_data = BotChatMessages::getUsersMessages();

        $data = [
            'user_id' => $user_id,
            'users_messages' => $messages_data['users_messages'],
        ];
        return view('admin.chat_users_content', $data);
    }

    public static function loadChatMessages(LRequest $request)
    {
        $input = $request->except('_token');
        $user_id = $input['user_id'];
        $photo_small_arr = self::getUserPhotos($user_id);
        $messsages = BotChatMessages::getMessagesByUser($user_id);
        $data = [
            'user_id' => $user_id,
            'photo_small_arr' => $photo_small_arr,
            'messages' => $messsages,
        ];
        return view('admin.chat_messages_content', $data);
    }

    public static function readMessages(LRequest $request)
    {
        $input = $request->except('_token');
        $user_id = $input['user_id'];
        $messages_read = BotChatMessages::setMessagesReadedByUserId($user_id);
        return $messages_read;
    }

}
