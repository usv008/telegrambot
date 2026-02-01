<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSendingMessagesReactions extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_sending_messages_reactions';
    public $timestamps = false;

    public static function addReaction($post_id, $text_ru, $text_uk, $text_en)
    {
        $new_reaction = new self;
        $new_reaction->post_id = $post_id;
        $new_reaction->text_ru = $text_ru;
        $new_reaction->text_uk = $text_uk;
        $new_reaction->text_en = $text_en;
        $new_reaction->save();
        return $new_reaction;
    }

    public static function getReactionsByPost($post_id)
    {
        return self::where('post_id', $post_id)->orderBy('id')->get();
    }

    public static function incrementClicks($user_id, $post_id, $reaction_id)
    {
        if (BotSendingMessagesReactionsHistory::where('post_id', $post_id)->where('user_id', $user_id)->count() == 0)
            return self::find($reaction_id)->increment('clicks');
        else return null;
    }

}
