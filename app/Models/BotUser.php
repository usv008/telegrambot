<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotUser extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_user';
    public $timestamps = false;

    public static function getUser($user_id)
    {
        return BotUser::where('user_id', $user_id)->first();
    }

    public static function getUsers()
    {
        return BotUser::orderBy('id', 'asc')->get();
    }

    public static function getValue($user_id, $key)
    {
        if ($key == 'city_id') return 6;
        else return BotUser::where('user_id', $user_id)->first()[$key];
    }

    public static function setValue($user_id, $key, $value)
    {
        return BotUser::where('user_id', $user_id)->update([$key => $value]);
    }

    public static function setValues($user_id, $array)
    {
        return BotUser::where('user_id', $user_id)->update($array);
    }

}
