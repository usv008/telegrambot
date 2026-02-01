<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotRaffleCherry extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_raffle_cherry';
    public $timestamps = false;
}
