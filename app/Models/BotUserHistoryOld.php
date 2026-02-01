<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotUserHistoryOld extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_user_history';
    public $timestamps = false;
}
