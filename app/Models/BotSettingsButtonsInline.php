<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSettingsButtonsInline extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_settings_buttons_inline';
    public $timestamps = false;
}
