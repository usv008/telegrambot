<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotUserHistory extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_user_history_new';
    public $timestamps = false;
}
