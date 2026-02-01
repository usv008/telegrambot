<?php

namespace App\Models;

use App\Http\Controllers\Telegram\BotUserSettingsController;
use Illuminate\Database\Eloquent\Model;

class BotSendingMessagesReactionsAnswers extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_sending_messages_reactions_answers';
    public $timestamps = false;

    public static function addAnswer($post_id, $text_ru, $text_uk, $text_en)
    {
        $new_answer = new self;
        $new_answer->post_id = $post_id;
        $new_answer->text_ru = $text_ru;
        $new_answer->text_uk = $text_uk;
        $new_answer->text_en = $text_en;
        $new_answer->save();
        return $new_answer;
    }

    public static function checkAnswer($user_id, $post_id)
    {
        if (self::where('post_id', $post_id)->count() > 0) {
            $lang = BotUserSettingsController::getLang($user_id);
            $text_lang = 'text_'.$lang;
            return self::where('post_id', $post_id)->first()[$text_lang];
        }
        else return null;
    }
}
