<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class BotChatMessages extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_chat_messages';
    public $timestamps = false;

    public static function getUsersMessages()
    {
        $messages = self::join('bot_user', 'bot_user.user_id', 'bot_chat_messages.user_id')
            ->leftJoin('bot_user_settings', 'bot_user_settings.user_id', 'bot_user.user_id')
            ->get([
                'bot_chat_messages.id',
                'bot_user.user_id',
                'bot_user.username',
                'bot_user.first_name',
                'bot_user.last_name',
                'bot_user.city_id',
                'bot_user.active',
                'bot_chat_messages.readed',
                'bot_chat_messages.created_at',
                'bot_chat_messages.updated_at',
                'bot_user_settings.lang',
                'bot_user_settings.lang',
                'bot_chat_messages.text',
                'bot_chat_messages.photo',
            ]);
        $users_messages = $messages->sortByDesc('id')->unique('user_id');
        return ['messages' => $users_messages, 'users_messages' => $users_messages];
    }

    public static function getMessagesByUser($user_id)
    {
        return self::where('user_id', $user_id)->orderBy('id', 'asc')->get();
    }

    public static function setMessagesReadedByUserId($user_id)
    {
        return self::where('user_id', $user_id)->update(['readed' => 1]);
    }

    public static function addNewMessage($user_id, $text)
    {
        $new_message = new self;
        $new_message->operator = 1;
        $new_message->name = Auth::user()->name;
        $new_message->email = Auth::user()->email;
        $new_message->user_id = $user_id;
        $new_message->text = $text;
        $new_message->readed = 1;
        $new_message->save();
        return $new_message;
    }

    public static function getMessagesUnreaded()
    {
        return self::where('readed', 0)->count();
    }

}
