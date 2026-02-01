<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSettingsDateNotWork extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_settings_date_not_work';
    public $timestamps = false;
}
