<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSettingsCashback extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_settings_cashback';
//    protected $fillable = ['button1', 'button2'];
    public $timestamps = false;


    public static function get_all_settings()
    {
        return self::all();
    }

    public static function get_cashback_percent()
    {
        return self::where('settings_name', 'cashback_percent')->first()['settings_value'];
    }

    public static function get_cashback_add_user()
    {
        return self::where('settings_name', 'cashback_add_user')->first()['settings_value'];
    }

    public static function get_cashback_add_referal()
    {
        return self::where('settings_name', 'cashback_add_referal')->first()['settings_value'];
    }

    public static function get_min_order_sum()
    {
        return self::where('settings_name', 'min_order_sum')->first()['settings_value'];
    }

}
