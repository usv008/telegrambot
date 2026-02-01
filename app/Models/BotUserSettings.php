<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotUserSettings extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_user_settings';
    public $timestamps = false;
    protected $fillable = ['user_id', 'lang', 'date_z'];
}
