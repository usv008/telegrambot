<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSettingsDate extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_settings_date';
    public $timestamps = false;
}
