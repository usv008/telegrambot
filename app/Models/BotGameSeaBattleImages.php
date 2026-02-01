<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotGameSeaBattleImages extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_game_sea_battle_images';
    public $timestamps = false;

    public static function getImageByN($n)
    {
        return self::where('n', $n)->first()['image'];
    }

    public static function getFileIdByN($n)
    {
        return self::where('n', $n)->first()['file_id'];
    }

    public static function getImages()
    {
        return self::all();
    }

}
