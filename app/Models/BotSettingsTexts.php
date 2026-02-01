<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSettingsTexts extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_settings_texts';
    public $timestamps = false;
}
