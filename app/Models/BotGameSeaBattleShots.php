<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Longman\TelegramBot\Request;

class BotGameSeaBattleShots extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_game_sea_battle_shots';
    public $timestamps = false;

    public static function getShot($id)
    {
        return self::find($id);
    }

    public static function getShotByFieldId($field_id)
    {
        return self::where('field_id', $field_id)->first();
    }

    public static function updateFired($shot_id, $f) {
        return self::where('id', $shot_id)->update(['f'.$f => 1]);
    }

    public static function createNewShot($game_id, $field_id, $user_id, $is_bot = 0)
    {
        $new_field_shot = new self;
        $new_field_shot->game_id = $game_id;
        $new_field_shot->field_id = $field_id;
        $new_field_shot->user_id = $user_id;
        $new_field_shot->is_bot = $is_bot;
        $new_field_shot->save();
        return $new_field_shot;
    }

    public static function getShotsByFieldIds($field_ids)
    {
        return self::whereIn('field_id', $field_ids)->get();
    }
}
