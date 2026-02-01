<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSettingsSticker extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_settings_sticker';
    public $timestamps = false;
}
