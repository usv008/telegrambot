<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotCashbackHistory extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_cashback_history';
    public $timestamps = false;
}
