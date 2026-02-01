<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotCashbackUsers extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_cashback_users';
    public $timestamps = false;
}
