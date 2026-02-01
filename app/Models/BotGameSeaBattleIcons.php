<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotGameSeaBattleIcons extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_game_sea_battle_icons';
    public $timestamps = false;

    public static $ship_id = 1;
    public static $bomb_id = 2;

    public static function getIcon($id)
    {
        return self::find($id);
    }

    public static function getIcons()
    {
        return self::where('enabled', 1)->get();
    }

}
