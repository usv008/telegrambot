<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSettings extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_settings';
    public $timestamps = false;

    public static function getAllSettings() {
        return self::all();
    }

    public static function getSettingsByName($name)
    {
        return self::where('settings_name', $name)->first();
    }

    public static function getWebCameraLink()
    {
        return self::where('settings_name', 'youtube_link')->first()['settings_value'];
    }

}
