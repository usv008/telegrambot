<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSettingsLang extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_settings_language';
    public $timestamps = false;
}
