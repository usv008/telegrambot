<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSettingsCities extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_settings_cities';
    public $timestamps = false;
}
