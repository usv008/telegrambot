<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotGameSeaBattleRates extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_game_sea_battle_rates';
    public $timestamps = false;

    public static function getRate($id)
    {
        return self::find($id);
    }

    public static function getRates()
    {
        return self::orderBy('id')->get();
    }

    public static function sendComment($rate_id, $user_id, $comment)
    {
        return self::where('id', $rate_id)->where('user_id', $user_id)->update(['comment' => $comment]);
    }

}
