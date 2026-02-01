<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSettingsButtons extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_settings_buttons';
    public $timestamps = false;
}
