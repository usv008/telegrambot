<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotRaffleUsers extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_raffle_users';
    public $timestamps = false;
}
