<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotGameSeaBattleUsers extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_game_sea_battle_users';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'pwbou',
        'updated_at',
    ];

    public static function newUser($user_id)
    {
        $user = new self;
        $user->user_id = $user_id;
        $user->pwbou = 0;
        $user->updated_at = date("Y-m-d H:i:s");
        $user->save();
        return $user;
    }

    public static function findUser($user_id)
    {
        if (self::where('user_id', $user_id)->count() > 0)
            return true;
        else {
            $new_user = self::newUser($user_id);
            return null;
        }
    }

    public static function getUser($id)
    {
        return self::where('user_id', $id)->first();
    }

    public static function getUsers()
    {
        return self::all();
    }

    public static function getWaitingUsers($user_id)
    {
        return self::where('user_id', '!=', $user_id)->where('pwbou', 1)->orderBy('updated_at', 'asc')->get();
    }

    public static function setUserPlayWaiting($user_id)
    {
        return self::where('user_id', $user_id)->update(['pwbou' => 1]);
    }

    public static function setUserPlayWithBot($user_id)
    {
        return self::where('user_id', $user_id)->update(['pwbou' => 2]);
    }

    public static function setUserPlayWithUser($user_id)
    {
        return self::where('user_id', $user_id)->update(['pwbou' => 3]);
    }

    public static function setUserNoPlay($user_id)
    {
        return self::where('user_id', $user_id)->update(['pwbou' => 0]);
    }

    public static function incrementUserWin($user_id)
    {
        $user = self::getUser($user_id);
        $user->increment('won');
        return $user->save();
    }

}
