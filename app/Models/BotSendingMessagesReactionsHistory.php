<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSendingMessagesReactionsHistory extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_sending_messages_reactions_history';
    public $timestamps = false;

    public static function addToHistory($user_id, $post_id, $reaction_id)
    {
        $new_history = new self;
        $new_history->post_id = $post_id;
        $new_history->user_id = $user_id;
        $new_history->reaction_id = $reaction_id;
        $new_history->save();
        return $new_history;
    }

}
