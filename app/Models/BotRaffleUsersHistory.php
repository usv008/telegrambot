<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotRaffleUsersHistory extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_raffle_users_history';
    public $timestamps = false;
}
